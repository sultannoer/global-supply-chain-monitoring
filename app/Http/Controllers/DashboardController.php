<?php

namespace App\Http\Controllers;

use App\Models\Port;
use App\Services\WeatherService;
use App\Services\ExchangeRateService;
use App\Services\NewsService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected $weatherService;
    protected $exchangeRateService;
    protected $newsService;

    public function __construct(
        WeatherService $weatherService,
        ExchangeRateService $exchangeRateService,
        NewsService $newsService
    ) {
        $this->weatherService = $weatherService;
        $this->exchangeRateService = $exchangeRateService;
        $this->newsService = $newsService;
    }

    // Menambahkan parameter Request $request agar sistem bisa mendeteksi jenis akses
    public function getliveMetrics(Request $request)
    {
        $ports = Port::with(['country'])->get();

        $allLiveRates = $this->exchangeRateService->getLatestRates()['rates'] ?? [];

        $metricsData = $ports->map(function ($port) use ($allLiveRates) {
            $currencyCode = $port->country->currency_code ?? 'USD';
            $rate = $allLiveRates[$currencyCode] ?? null;
            $hasWeather = $port->temp !== null && $port->wind_speed !== null && $port->rain !== null;

            return [
                'port_id' => $port->id,
                'name' => $port->name,
                'country_code' => $port->country_code,
                'country_name' => $port->country->name ?? null,
                'coordinates' => [
                    'latitude' => (float)$port->latitude,
                    'longitude' => (float)$port->longitude,
                ],
                'climate' => [
                    'available' => $hasWeather,
                    'temperature_celsius' => $port->temp !== null ? (float) $port->temp : null,
                    'wind_speed_kmh' => $port->wind_speed !== null ? (float) $port->wind_speed : null,
                    'rain_mm' => $port->rain !== null ? (float) $port->rain : null,
                    'storm_risk' => $hasWeather ? $port->storm_risk_status : null,
                ],
                'finance' => [
                    'currency' => $currencyCode,
                    'exchange_rate_available' => is_numeric($rate),
                    'exchange_rate_vs_usd' => is_numeric($rate) ? (float) $rate : null,
                    'gdp' => $port->country->gdp ?? null,
                    'inflation_rate' => $port->country->inflation_rate ?? null
                ],
                // A full port export can contain thousands of nodes; news is
                // deliberately fetched through /api/news or the port detail page
                // instead of issuing thousands of GNews requests here.
                'security_intelligence' => null
            ];
        });

        // =========================================================================
        // KUNCI PINTAR DETEKSI SISTEM:
        // Jika diakses sebagai API (oleh Postman/sistem B2B) atau via AJAX, mutahkan JSON
        // =========================================================================
        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 'success',
                'system_time' => now()->toIso8601String(),
                'total_nodes' => $metricsData->count(),
                'data' => $metricsData
            ], 200, [], JSON_PRETTY_PRINT);
        }

        // Jika dibuka operator lewat browser biasa, arahkan ke halaman dashboard visual mewah
        return view('dashboard.ports_master', compact('metricsData'));
    }
}
