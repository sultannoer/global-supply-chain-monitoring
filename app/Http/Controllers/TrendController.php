<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\CountryEconomicHistory;
use App\Models\CurrencyRateHistory;
use App\Models\Port;
use App\Models\PortWeatherHistory;
use App\Models\CountryWeatherHistory;
use App\Models\RiskScore;
use App\Services\RiskAssessmentService;
use App\Services\WeatherService;
use Illuminate\Http\Request;

class TrendController extends Controller
{
    public function index(Request $request, WeatherService $weatherService, RiskAssessmentService $riskAssessmentService)
    {
        $countries = Country::query()->orderBy('name')->get(['code', 'name', 'currency_code']);
        $countryCode = strtoupper((string) $request->query('country', ''));
        $country = $countries->firstWhere('code', $countryCode);

        if (! $country) {
            $fallbackCode = RiskScore::query()->latest('calculated_at')->value('country_code');
            $country = $countries->firstWhere('code', $fallbackCode) ?? $countries->first();
        }

        $ports = $country ? Port::query()->where('country_code', $country->code)->orderBy('name')->get(['id', 'name']) : collect();
        $portId = (int) $request->query('port', 0);
        $selectedPort = $ports->firstWhere('id', $portId)
            ?? Port::query()->whereIn('id', $ports->pluck('id'))->whereHas('weatherHistories')->latest('updated_at')->first(['id', 'name'])
            ?? $ports->first();

        if ($selectedPort) {
            // The selected port may not be part of the latest background batch.
            // Create its first genuine Open-Meteo observation on demand instead
            // of showing an empty chart or generating a synthetic value.
            $selectedPort = Port::find($selectedPort->id);
            $hasWeatherHistory = PortWeatherHistory::query()->where('port_id', $selectedPort->id)->exists();
            if (! $hasWeatherHistory && $weatherService->updatePortWeather($selectedPort)) {
                $selectedPort->refresh();
                $riskAssessmentService->calculatePortRisk($selectedPort);
                PortWeatherHistory::create([
                    'port_id' => $selectedPort->id,
                    'temp' => $selectedPort->temp,
                    'rain' => $selectedPort->rain,
                    'wind_speed' => $selectedPort->wind_speed,
                    'storm_risk_status' => $selectedPort->storm_risk_status,
                    'risk_score' => $selectedPort->risk_score,
                    'recorded_at' => now(),
                ]);
            }
        }

        $risk = $country ? RiskScore::query()->where('country_code', $country->code)->latest('calculated_at')->take(60)->get()->reverse()->values() : collect();
        $currency = $country ? CurrencyRateHistory::query()->where('currency_code', $country->currency_code)->latest('recorded_at')->take(60)->get()->reverse()->values() : collect();
        $economic = $country ? CountryEconomicHistory::query()->where('country_code', $country->code)->latest('recorded_at')->take(60)->get()->reverse()->values() : collect();
        $weather = $selectedPort
            ? PortWeatherHistory::query()->where('port_id', $selectedPort->id)->latest('recorded_at')->take(80)->get()->reverse()->values()
            : ($country ? CountryWeatherHistory::query()->where('country_code', $country->code)->latest('recorded_at')->take(80)->get()->reverse()->values() : collect());

        return view('trends.index', [
            'countries' => $countries,
            'country' => $country,
            'ports' => $ports,
            'selectedPort' => $selectedPort,
            'charts' => [
                'risk' => $this->series($risk, 'calculated_at', ['Risk score' => 'total_score']),
                'currency' => $this->series($currency, 'recorded_at', ['Exchange rate' => 'rate_to_usd']),
                'economic' => $this->series($economic, 'recorded_at', ['GDP (USD)' => 'gdp', 'Inflation (%)' => 'inflation_rate']),
                'weather' => $this->series($weather, 'recorded_at', ['Temperature (°C)' => 'temp', 'Wind (km/h)' => 'wind_speed', 'Rain (mm)' => 'rain']),
            ],
        ]);
    }

    private function series($records, string $dateColumn, array $fields): array
    {
        return [
            'labels' => $records->map(fn ($record) => $record->{$dateColumn}?->format('d M H:i'))->all(),
            'datasets' => collect($fields)->map(fn (string $field, string $label) => [
                'label' => $label,
                'data' => $records->map(fn ($record) => $record->{$field} === null ? null : (float) $record->{$field})->all(),
            ])->values()->all(),
        ];
    }
}
