<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Port;
use App\Services\EconomicService;
use App\Services\ExchangeRateService;
use App\Services\WeatherService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class LiveMarkerController extends Controller
{
    public function country(string $code, EconomicService $economic, WeatherService $weather, ExchangeRateService $exchange): JsonResponse
    {
        $country = Country::query()->findOrFail(strtoupper($code));
        $key = 'live-marker-country:'.$country->code;
        $data = Cache::remember($key, now()->addMinutes(15), function () use ($country, $economic, $weather, $exchange) {
            $economic->updateCountryEconomicIndicators($country);
            $country->refresh();
            $weatherData = $weather->getCountryWeather($country);

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
            ];
        });

        return response()->json(['status' => 'success', 'cached_minutes' => 15, 'data' => $data]);
    }

    public function port(int $id, EconomicService $economic, WeatherService $weather, ExchangeRateService $exchange): JsonResponse
    {
        $port = Port::with('country')->findOrFail($id);
        $key = 'live-marker-port:'.$port->id;
        $data = Cache::remember($key, now()->addMinutes(15), function () use ($port, $economic, $weather, $exchange) {
            if ($port->country && ($port->country->gdp === null || $port->country->inflation_rate === null)) {
                $economic->updateCountryEconomicIndicators($port->country);
                $port->load('country');
            }
            $weather->updatePortWeather($port);
            $port->refresh();

            return [
                'temp' => $port->temp,
                'rain' => $port->rain,
                'wind' => $port->wind_speed,
                'currency' => $port->country?->currency_code ?? 'USD',
                'rate' => $exchange->getRate($port->country?->currency_code ?? 'USD'),
                'gdp' => $port->country?->gdp,
                'inflation' => $port->country?->inflation_rate,
            ];
        });

        return response()->json(['status' => 'success', 'cached_minutes' => 15, 'data' => $data]);
    }
}
