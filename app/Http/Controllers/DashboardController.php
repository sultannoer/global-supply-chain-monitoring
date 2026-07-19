<?php

namespace App\Http\Controllers;

use App\Models\Port;
use App\Services\WeatherService;
use App\Services\ExchangeRateService;
use App\Services\NewsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

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

   
    public function getliveMetrics(Request $request)
    {
        $ports = Port::with(['country'])->get();

        
        $allLiveRates = Cache::remember("global_live_forex_rates_metrics", 3600, function() {
            try {
                $response = Http::timeout(3)->get("https://open.er-api.com/v6/latest/USD");
                if ($response->successful()) {
                    return $response->json("rates") ?? [];
                }
            } catch (\Exception $e) {
                \Log::error("Metrics Forex API Error: " . $e->getMessage());
            }
            return [];
        });

        $metricsData = $ports->map(function ($port) use ($allLiveRates) {
            $currencyCode = $port->country->currency_code ?? 'USD';
            $rate = $allLiveRates[$currencyCode] ?? 1.00;
            
            
            $newsUpdate = "Terminal status operational.";
            if ($this->newsService && method_exists($this->newsService, 'getLatestNews')) {
                try {
                    $countryName = $port->country->name ?? '';
                    $fetchedNews = $this->newsService->getLatestNews($countryName);
                    if (!empty($fetchedNews) && isset($fetchedNews[0]['title'])) {
                        $newsUpdate = $fetchedNews[0]['title'];
                    }
                } catch (\Exception $e) {
                    \Log::error("Metrics News Fetch Error: " . $e->getMessage());
                }
            }

            return [
                'port_id' => $port->id,
                'name' => $port->name,
                'code' => $port->code ?? 'N/A',
                'coordinates' => [
                    'latitude' => (float)$port->latitude,
                    'longitude' => (float)$port->longitude,
                ],
                'climate' => [
                    'temperature_celsius' => $port->temp_celsius ?? $port->temp ?? 27,
                    'wind_speed_kmh' => $port->wind_speed_kmh ?? $port->wind_speed ?? 10,
                    'rain_mm' => $port->rain_mm ?? $port->rain ?? 0,
                    'storm_risk' => $port->storm_risk_status ?? 'Low'
                ],
                'finance' => [
                    'currency' => $currencyCode,
                    'exchange_rate_vs_usd' => (float)$rate,
                    'gdp' => $port->country->gdp ?? null,
                    'inflation_rate' => $port->country->inflation_rate ?? null
                ],
                'security_intelligence' => $newsUpdate
            ];
        });


        if ($request->wantsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 'success',
                'system_time' => now()->toIso8601String(),
                'total_nodes' => $metricsData->count(),
                'data' => $metricsData
            ], 200, [], JSON_PRETTY_PRINT);
        }

        
        return view('dashboard.ports_master', compact('metricsData'));
    }
}