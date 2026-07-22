<?php

namespace App\Services;

use App\Models\Country;
use App\Models\Port;
use App\Models\RiskScore;
use App\Models\Shipment;
use App\Models\RiskAlert;
use App\Models\CountryWeatherHistory;

class RiskAssessmentService
{
    public function __construct(
        private readonly ExchangeRateService $exchangeRates,
        private readonly NewsService $newsService,
    ) {
    }

    /**
     * Menghitung skor risiko akumulatif untuk Pelabuhan (Skala 0 - 100)
     */
    public function calculatePortRisk(Port $port): int
    {
        $score = 0;

        // 1. FAKTOR STATUS BADAI (Bobot Maksimal: 40 Poin)
        if ($port->storm_risk_status === 'High') {
            $score += 40;
        } elseif ($port->storm_risk_status === 'Medium') {
            $score += 20;
        } else {
            $score += 5;
        }

        // 2. FAKTOR KECEPATAN ANGIN (Bobot Maksimal: 30 Poin)
        $wind = (float) $port->wind_speed;
        if ($wind > 50) {
            $score += 30;
        } elseif ($wind > 30) {
            $score += 15;
        } else {
            $score += 5;
        }

        // 3. FAKTOR CURAH HUJAN (Bobot Maksimal: 30 Poin)
        $rain = (float) $port->rain;
        if ($rain > 15) {
            $score += 30;
        } elseif ($rain > 5) {
            $score += 15;
        } else {
            $score += 5;
        }

        $finalScore = min(max($score, 0), 100);
        $port->update(['risk_score' => $finalScore]);

        // 🚨 TRIGGER OTOMATIS: Catat log jika pelabuhan masuk zona bahaya
        if ($finalScore >= 50) {
            RiskAlert::updateOrCreate(
                ['port_id' => $port->id, 'is_resolved' => false, 'risk_type' => 'WEATHER'],
                [
                    'alert_level' => $finalScore >= 75 ? 'CRITICAL' : 'WARNING',
                    'message' => "⚠️ PELABUHAN RAWAN: Terminal [{$port->name}] terdeteksi memiliki Skor Risiko tinggi ({$finalScore}/100) akibat cuaca buruk. Operasional crane kontainer berpotensi tertunda."
                ]
            );
        } else {
            // Jika cuaca membaik, tandai alert sebelumnya sebagai resolved
            RiskAlert::where('port_id', $port->id)->update(['is_resolved' => true]);
        }

        return $finalScore;
    }

    /**
     * Menghitung skor risiko akumulatif untuk Perjalanan Kapal / Shipment (Skala 0 - 100)
     */
    public function calculateShipmentRisk(Shipment $shipment): int
    {
        $score = 0;
        $inflation = 0.0;
        $destinationPort = $shipment->destinationPort;

        // 1. FAKTOR KONDISI CUACA DI PELABUHAN TUJUAN (Bobot: 60%)
        if ($destinationPort) {
            $portRisk = $destinationPort->risk_score;
            $score += ($portRisk * 0.6);
        }

        // 2. FAKTOR KERAWANAN EKONOMI / INFLASI NEGARA TUJUAN (Bobot: 40%)
        if ($destinationPort && $destinationPort->country) {
            $inflation = (float) $destinationPort->country->inflation_rate;
            
            if ($inflation > 12) {
                $score += 40;
            } elseif ($inflation > 6) {
                $score += 20;
            } else {
                $score += 10;
            }
        } else {
            $score += 15; 
        }

        $finalScore = min(max((int) $score, 0), 100);
        $shipment->update(['risk_score' => $finalScore]);

        // 🚨 TRIGGER OTOMATIS: Catat log jika kapal kargo menghadapi risiko tinggi
        if ($finalScore >= 45) {
            RiskAlert::updateOrCreate(
                ['shipment_id' => $shipment->id, 'is_resolved' => false],
                [
                    'alert_level' => $finalScore >= 70 ? 'CRITICAL' : 'WARNING',
                    'risk_type' => $inflation > 12 ? 'ECONOMIC' : 'WEATHER',
                    'message' => "🚢 ANCAMAN RUTE: Kapal [{$shipment->vessel_name}] dengan manifes #{$shipment->tracking_number} menghadapi akumulasi risiko kargo ({$finalScore}/100) menuju pelabuhan target."
                ]
            );
        } else {
            RiskAlert::where('shipment_id', $shipment->id)->update(['is_resolved' => true]);
        }

        return $finalScore;
    }

    /**
     * Weighted country risk model.
     * Weather 35%, inflation 25%, exchange movement 15%, news risk 25%.
     * Missing components are excluded from the weighted denominator and are
     * reported through data_coverage instead of silently converted to zero.
     */
    public function calculateCountryRisk(Country $country): RiskScore
    {
        $previousScore = RiskScore::query()
            ->where('country_code', $country->code)
            ->latest('calculated_at')
            ->first();
        $lastValid = static function (string $field) use ($country) {
            return RiskScore::query()->where('country_code', $country->code)
                ->whereNotNull($field)->latest('calculated_at')->value($field);
        };

        $weather = $country->ports()
            ->whereNotNull('temp')
            ->avg('risk_score');
        $weather = is_numeric($weather) ? (float) $weather : null;
        if ($weather === null) {
            $weather = CountryWeatherHistory::query()
                ->where('country_code', $country->code)
                ->latest('recorded_at')
                ->value('risk_score');
            $weather = is_numeric($weather) ? (float) $weather : null;
        }
        if ($weather === null) {
            $lastWeather = $lastValid('weather_score');
            $weather = is_numeric($lastWeather) ? (float) $lastWeather : ($previousScore?->weather_score !== null ? (float) $previousScore->weather_score : null);
        }

        $inflation = $country->inflation_rate;
        $inflationScore = is_numeric($inflation)
            ? min(100, max(0, (float) $inflation * 5))
            : null;
        if ($inflationScore === null) {
            $lastInflation = $lastValid('inflation_score');
            $inflationScore = is_numeric($lastInflation) ? (float) $lastInflation : ($previousScore?->inflation_score !== null ? (float) $previousScore->inflation_score : null);
        }

        $rate = $this->exchangeRates->getRate($country->currency_code);
        $previous = RiskScore::query()
            ->where('country_code', $country->code)
            ->whereNotNull('exchange_rate')
            ->latest('calculated_at')
            ->value('exchange_rate');
        $exchangeScore = $this->calculateExchangeScore($rate, $previous);
        if ($exchangeScore === null) {
            $lastExchange = $lastValid('exchange_score');
            $exchangeScore = is_numeric($lastExchange) ? (float) $lastExchange : ($previousScore?->exchange_score !== null ? (float) $previousScore->exchange_score : null);
        }

        // Country risk and News Sentiment now use the same country-scoped articles.
        $newsSummary = $this->newsService->summarizeSentiment($this->newsService->getLatestNews($country->name, 10));
        $newsScore = $newsSummary['total_articles'] > 0 ? $newsSummary['negative_percentage'] : null;
        if ($newsScore === null) {
            $lastNews = $lastValid('news_score');
            $newsScore = is_numeric($lastNews) ? (float) $lastNews : ($previousScore?->news_score !== null ? (float) $previousScore->news_score : null);
        }
        $components = [
            ['value' => $weather, 'weight' => 0.35],
            ['value' => $inflationScore, 'weight' => 0.25],
            ['value' => $exchangeScore, 'weight' => 0.15],
            ['value' => $newsScore, 'weight' => 0.25],
        ];

        $availableWeight = 0.0;
        $weightedTotal = 0.0;
        foreach ($components as $component) {
            if ($component['value'] === null) {
                continue;
            }

            $availableWeight += $component['weight'];
            $weightedTotal += $component['value'] * $component['weight'];
        }

        $total = $availableWeight > 0 ? round($weightedTotal / $availableWeight, 2) : null;

        return RiskScore::create([
            'country_code' => $country->code,
            'weather_score' => $weather,
            'inflation_score' => $inflationScore,
            'exchange_score' => $exchangeScore,
            'news_score' => $newsScore,
            'exchange_rate' => $rate ?? $lastValid('exchange_rate') ?? $previousScore?->exchange_rate,
            'total_score' => $total,
            'data_coverage' => (int) round($availableWeight * 100),
            'risk_level' => $this->riskLevel($total),
            'calculated_at' => now(),
        ]);
    }

    private function calculateExchangeScore(?float $rate, mixed $previousRate): ?float
    {
        if ($rate === null) {
            return null;
        }

        if (! is_numeric($previousRate) || (float) $previousRate <= 0) {
            // First real observation establishes the baseline; no volatility is inferred.
            return 0.0;
        }

        $changePercent = abs(($rate - (float) $previousRate) / (float) $previousRate) * 100;

        return min(100, round($changePercent * 20, 2));
    }

    private function riskLevel(?float $score): string
    {
        return match (true) {
            $score === null => 'UNKNOWN',
            $score < 30 => 'LOW',
            $score < 60 => 'MEDIUM',
            $score < 80 => 'HIGH',
            default => 'CRITICAL',
        };
    }
}
