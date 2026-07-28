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
        $zoneExposure = $this->stormExposureForCoordinates(
            (float) $port->latitude,
            (float) $port->longitude,
            $port->id,
        );
        $effectiveStormStatus = $this->highestStormStatus(
            $port->storm_risk_status,
            $zoneExposure['risk'] ?? null,
        );

        // 1. FAKTOR STATUS BADAI (Bobot Maksimal: 40 Poin)
        if ($effectiveStormStatus === 'High') {
            $score += 40;
        } elseif ($effectiveStormStatus === 'Medium') {
            $score += 20;
        } else {
            $score += 5;
        }

        // Sebuah port dapat ber-cuaca lokal tenang namun berada di dalam
        // radius dampak badai dari terminal lain. Tambahkan penalti zona agar
        // marker, alert, dan Risk Score menggambarkan kondisi operasionalnya.
        if ($zoneExposure && $this->stormRank($zoneExposure['risk']) > $this->stormRank($port->storm_risk_status)) {
            $score += 10;
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
        // Medium and High weather conditions must both enter the global
        // warning feed; a route is not required for a port weather alert.
        if ($finalScore >= 40) {
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

        // Risiko cuaca negara harus mencerminkan titik terburuk yang sedang
        // dipantau: rata-rata risiko terminal aktif dan/atau cuaca pada
        // koordinat referensi negara. Dengan demikian badai pada negara yang
        // punya port tetap menaikkan komponen weather pada Risk Score.
        $portWeather = $country->ports()
            ->whereNotNull('temp')
            ->avg('risk_score');
        $portWeather = is_numeric($portWeather) ? (float) $portWeather : null;
        $countryWeather = CountryWeatherHistory::query()
            ->where('country_code', $country->code)
            ->latest('recorded_at')
            ->value('risk_score');
        $countryWeather = is_numeric($countryWeather) ? (float) $countryWeather : null;
        $countryZoneExposure = $this->stormExposureForCoordinates(
            (float) $country->latitude,
            (float) $country->longitude,
        );
        $zoneWeather = $countryZoneExposure ? $this->zoneExposureScore($countryZoneExposure['risk']) : null;
        $weather = collect([$portWeather, $countryWeather, $zoneWeather])->filter(static fn ($value) => $value !== null)->max();
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

    /**
     * Finds the strongest active Open-Meteo storm zone covering coordinates.
     * The source port remains the source of the weather measurement; this only
     * models its operational impact radius on nearby ports/country markers.
     */
    public function stormExposureForCoordinates(float $latitude, float $longitude, ?int $excludePortId = null): ?array
    {
        if (! is_finite($latitude) || ! is_finite($longitude) || ($latitude == 0.0 && $longitude == 0.0)) {
            return null;
        }

        $zones = Port::query()
            ->whereIn('storm_risk_status', ['High', 'Medium'])
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->when($excludePortId, fn ($query) => $query->where('id', '!=', $excludePortId))
            ->get(['id', 'name', 'latitude', 'longitude', 'storm_risk_status']);

        $best = null;
        foreach ($zones as $zone) {
            $risk = $zone->storm_risk_status;
            $radius = $risk === 'High' ? 300.0 : 180.0;
            $distance = $this->haversineKilometers($latitude, $longitude, (float) $zone->latitude, (float) $zone->longitude);
            if ($distance > $radius) {
                continue;
            }

            if ($best === null
                || $this->stormRank($risk) > $this->stormRank($best['risk'])
                || ($this->stormRank($risk) === $this->stormRank($best['risk']) && $distance < $best['distance_km'])) {
                $best = [
                    'risk' => $risk,
                    'source_port_id' => $zone->id,
                    'source_name' => $zone->name,
                    'radius_km' => $radius,
                    'distance_km' => round($distance, 1),
                ];
            }
        }

        return $best;
    }

    private function zoneExposureScore(string $risk): float
    {
        return $risk === 'High' ? 60.0 : 40.0;
    }

    private function highestStormStatus(?string ...$statuses): string
    {
        $highest = 'Low';
        foreach ($statuses as $status) {
            if ($this->stormRank($status) > $this->stormRank($highest)) {
                $highest = ucfirst(strtolower((string) $status));
            }
        }

        return $highest;
    }

    private function stormRank(?string $status): int
    {
        return match (strtolower((string) $status)) {
            'high' => 3,
            'medium' => 2,
            default => 1,
        };
    }

    private function haversineKilometers(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);
        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lngDelta / 2) ** 2;

        return 6371.0 * 2 * atan2(sqrt($a), sqrt(1 - $a));
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
