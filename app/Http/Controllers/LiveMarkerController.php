<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\CountryWeatherHistory;
use App\Models\Port;
use App\Models\PortWeatherHistory;
use App\Services\EconomicService;
use App\Services\ExchangeRateService;
use App\Services\RiskAssessmentService;
use App\Services\WeatherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class LiveMarkerController extends Controller
{
    public function country(string $code, EconomicService $economic, WeatherService $weather, ExchangeRateService $exchange, RiskAssessmentService $riskAssessment): JsonResponse
    {
        $country = Country::query()->findOrFail(strtoupper($code));
        $key = 'live-marker-country:v4:'.$country->code;
        $data = Cache::remember($key, now()->addMinutes(15), function () use ($country, $economic, $weather, $exchange, $riskAssessment) {
            $economic->updateCountryEconomicIndicators($country);
            $country->refresh();
            $weatherData = $weather->getCountryWeather($country);

            if ($weatherData) {
                CountryWeatherHistory::create([
                    'country_code' => $country->code,
                    'temp' => $weatherData['temp'],
                    'rain' => $weatherData['rain'],
                    'wind_speed' => $weatherData['wind_speed'],
                    'storm_risk_status' => $weatherData['storm_risk_status'],
                    'risk_score' => $weatherData['risk_score'],
                    'recorded_at' => now(),
                ]);
            }

            $countryRisk = $riskAssessment->calculateCountryRisk($country);
            $zoneExposure = $riskAssessment->stormExposureForCoordinates(
                (float) $country->latitude,
                (float) $country->longitude,
            );

            return [
                'code' => $country->code,
                'gdp' => $country->gdp,
                'inflation' => $country->inflation_rate,
                'population' => $country->population,
                'export' => $country->export_volume,
                'import' => $country->import_volume,
                'currency' => $country->currency_code,
                'rate' => $exchange->getRate($country->currency_code),
                'weather' => $weatherData,
                'storm_zone' => $zoneExposure,
                'risk' => [
                    'weather_score' => $countryRisk->weather_score,
                    'total_score' => $countryRisk->total_score,
                    'risk_level' => $countryRisk->risk_level,
                ],
            ];
        });

        return response()->json(['status' => 'success', 'cached_minutes' => 15, 'data' => $data]);
    }

    public function port(int $id, EconomicService $economic, WeatherService $weather, ExchangeRateService $exchange, RiskAssessmentService $riskAssessment): JsonResponse
    {
        $port = Port::with('country')->findOrFail($id);
        // Version the payload so clients do not receive an older cached
        // response that predates storm_risk_status.
        $key = 'live-marker-port:v5:'.$port->id;
        $data = Cache::remember($key, now()->addMinutes(15), function () use ($port, $economic, $weather, $exchange, $riskAssessment) {
            if ($port->country && ($port->country->gdp === null || $port->country->inflation_rate === null)) {
                $economic->updateCountryEconomicIndicators($port->country);
                $port->load('country');
            }
            $weather->updatePortWeather($port);
            $port->refresh();
            $riskAssessment->calculatePortRisk($port);
            $port->refresh();
            if ($port->temp !== null && $port->wind_speed !== null && $port->rain !== null) {
                PortWeatherHistory::create([
                    'port_id' => $port->id,
                    'temp' => $port->temp,
                    'rain' => $port->rain,
                    'wind_speed' => $port->wind_speed,
                    'storm_risk_status' => $port->storm_risk_status,
                    'risk_score' => $port->risk_score,
                    'recorded_at' => now(),
                ]);
            }
            $zoneExposure = $riskAssessment->stormExposureForCoordinates(
                (float) $port->latitude,
                (float) $port->longitude,
                $port->id,
            );
            $countryRisk = $port->country
                ? $riskAssessment->calculateCountryRisk($port->country)
                : null;

            return [
                'temp' => $port->temp,
                'rain' => $port->rain,
                'wind' => $port->wind_speed,
                'storm_risk_status' => $port->storm_risk_status ?? 'N/A',
                'storm_zone' => $zoneExposure,
                'port_risk_score' => $port->risk_score,
                'country_weather_score' => $countryRisk?->weather_score,
                'country_risk_score' => $countryRisk?->total_score,
                'currency' => $port->country?->currency_code ?? 'USD',
                'rate' => $exchange->getRate($port->country?->currency_code ?? 'USD'),
                'gdp' => $port->country?->gdp,
                'inflation' => $port->country?->inflation_rate,
            ];
        });

        return response()->json(['status' => 'success', 'cached_minutes' => 15, 'data' => $data]);
    }
}
