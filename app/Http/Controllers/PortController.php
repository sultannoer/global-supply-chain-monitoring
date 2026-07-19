<?php

namespace App\Http\Controllers;

use App\Models\Port;
use App\Services\WeatherService;
use App\Services\EconomicService; 
use App\Services\ExchangeRateService;
use App\Services\MarineTrafficService;
use App\Services\NewsService;
use App\Services\CountryService; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class PortController extends Controller
{
    protected $weatherService;
    protected $economicService; 
    protected $exchangeRateService;
    protected $marineService;
    protected $newsService;
    protected $countryService; 

    public function __construct(
        WeatherService $weatherService,
        EconomicService $economicService, 
        ExchangeRateService $exchangeRateService,
        MarineTrafficService $marineService,
        NewsService $newsService,
        CountryService $countryService 
    ) {
        $this->weatherService = $weatherService;
        $this->economicService = $economicService; 
        $this->exchangeRateService = $exchangeRateService;
        $this->marineService = $marineService;
        $this->newsService = $newsService;
        $this->countryService = $countryService; 
    }

    public function index(Request $request)
    {
        $search = $request->input('search');

        $ports = Port::with(['country', 'inboundShipments'])->get();

        $allLiveRates = Cache::remember("global_live_forex_rates", 3600, function() {
            try {
                $response = Http::get("https://open.er-api.com/v6/latest/USD");
                if ($response->successful()) {
                    return $response->json("rates") ?? [];
                }
            } catch (\Exception $e) {
                \Log::error("Global Forex API Error: " . $e->getMessage());
            }
            return [];
        });

        $enrichedPorts = $ports->map(function ($port) use ($allLiveRates) {
            $currencyCode = $port->country->currency_code ?? 'USD';
            $countryName = $port->country->name ?? 'Global Maritime Region';
            $rate = $allLiveRates[$currencyCode] ?? 1.00;
            
            $latestNews = "Terminal " . $port->name . " status: Clear. Automated customs tracking active.";

            return [
                'id' => $port->id,
                'name' => $port->name,
                'lat' => (float)$port->latitude,
                'lng' => (float)$port->longitude,
                'country' => $countryName,
                'currency' => $currencyCode,
                'language' => $port->country->language ?? 'Official Language',
                'gdp' => $port->country->gdp && $port->country->gdp > 0 
                    ? '$' . number_format($port->country->gdp / 1e9, 1) . 'B' 
                    : '$1.2T', 
                'inflation' => $port->country->inflation_rate && $port->country->inflation_rate > 0 
                    ? $port->country->inflation_rate . '%' 
                    : '2.1%',
                'temp' => $port->temp_celsius ?? $port->temp ?? rand(26, 30),
                'wind' => $port->wind_speed_kmh ?? $port->wind_speed ?? rand(8, 14),
                'rain' => $port->rain_mm ?? $port->rain ?? 0.0,
                'rate' => $rate,
                'news' => $latestNews
            ];
        });

        if ($search) {
            $enrichedPorts = $enrichedPorts->filter(function($port) use ($search) {
                return str_contains(strtolower($port['name']), strtolower($search)) || 
                       str_contains(strtolower($port['country']), strtolower($search));
            })->values();
        }

        $enrichedStorms = [
            ['name' => 'Badai Tropis Selat Malaka', 'lat' => 4.00, 'lng' => 100.00, 'radius_km' => 450, 'wind_speed' => '25 m/s'],
            ['name' => 'Siklon Tropis Filipina Selatan', 'lat' => 8.50, 'lng' => 126.20, 'radius_km' => 350, 'wind_speed' => '30 m/s']
        ];

        $allCustomVessels = session('active_custom_vessels', []);
        $enrichedVessels = array_map(function ($vessel) use ($enrichedPorts, $enrichedStorms) {
            $destPort = collect($enrichedPorts)->firstWhere('name', $vessel['dest_name']);
            
            $insideStorm = false;
            $currentLat = $vessel['live_lat'] ?? $vessel['lat'];
            $currentLng = $vessel['live_lng'] ?? $vessel['lng'];

            foreach ($enrichedStorms as $storm) {
                $theta = $currentLng - $storm['lng'];
                $dist = sin(deg2rad($currentLat)) * sin(deg2rad($storm['lat'])) +  cos(deg2rad($currentLat)) * cos(deg2rad($storm['lat'])) * cos(deg2rad($theta));
                $dist = acos($dist);
                $dist = rad2deg($dist);
                $miles = $dist * 60 * 1.1515;
                $km = $miles * 1.609344;
                
                if ($km <= $storm['radius_km']) {
                    $insideStorm = true;
                    break;
                }
            }

            $midOceanTemp = $insideStorm ? 24 : rand(26, 30);
            $midOceanWind = $insideStorm ? 29 : (($vessel['cargo_weight'] ?? 100) > 150 ? 22 : 8); 
            $stormRisk = $insideStorm ? '⚠️ ALERT: Critical Storm Impact Encountered' : ($midOceanWind > 15 ? '⚠️ ALERT: High Storm Risk Encountered' : '🌤️ Calm Sea Condition');
            
            $rate = $destPort['rate'] ?? 1.00;
            $lossImpact = $insideStorm ? '⚠️ LOSS METRIC: Devisa Penalty Applied (-15%)' : ($midOceanWind > 15 ? 'Potential Currency Loss (-1.2%)' : 'Stable (+0.4%)');

            return array_merge($vessel, [
                'temp' => $midOceanTemp,
                'wind' => $midOceanWind,
                'rain' => $midOceanWind > 15 ? 12 : 1,
                'storm_alert' => $stormRisk,
                'exchange_rate' => $rate,
                'currency_code' => $destPort['currency'] ?? 'USD',
                'currency_loss' => $lossImpact,
                'dest_gdp' => $destPort['gdp'] ?? '$1.2T',
                'dest_inflation' => $destPort['inflation'] ?? '2.1%'
            ]);
        }, $allCustomVessels);

        return view('ports.index', compact('enrichedPorts', 'enrichedVessels', 'enrichedStorms', 'search'));
    }

    public function updateVesselCoordinates(Request $request, $id)
    {
        $vessels = session('active_custom_vessels', []);
        
        foreach ($vessels as &$vessel) {
            if ($vessel['id'] === $id) {
                $vessel['live_lat'] = (float) $request->input('live_lat');
                $vessel['live_lng'] = (float) $request->input('live_lng');
                if ($request->has('step')) {
                    $vessel['step'] = (int) $request->input('step');
                }
                break;
            }
        }
        
        session(['active_custom_vessels' => $vessels]);
        
        return response()->json(['status' => 'success']);
    }

    public function show($id)
    {
        $port = Port::with(['country', 'inboundShipments', 'outboundShipments'])->findOrFail($id);
        
        $countryCode = $port->country->code ?? 'IDN'; 

        Cache::remember("sync_country_data_" . $port->id, 86400, function() use ($port, $countryCode) {
            if ($this->countryService) {
                $this->countryService->fetchAndSyncCountry($countryCode);
                $port->refresh(); 
            }

            if ($this->economicService && $port->country) {
                $this->economicService->updateCountryEconomicIndicators($port->country);
                $port->refresh();
            }
            return true;
        });

        $currencyCode = $port->country->currency_code ?? 'USD';
        
        
        $liveForexRate = Cache::remember("live_rate_" . $currencyCode, 3600, function() use ($currencyCode) {
            try {
                $response = Http::get("https://open.er-api.com/v6/latest/USD");
                if ($response->successful()) {
                    return $response->json("rates.{$currencyCode}") ?? 1.00;
                }
            } catch (\Exception $e) {}
            return 1.00;
        });

        
        $weatherTimeline = Cache::remember("weather_hourly_forecast_" . $port->id, 1800, function() use ($port) {
            try {
                $lat = $port->latitude;
                $lng = $port->longitude;
                $response = Http::get("https://api.open-meteo.com/v1/forecast?latitude={$lat}&longitude={$lng}&hourly=temperature_2m&forecast_days=1");
                
                if ($response->successful() && isset($response->json()['hourly']['temperature_2m'])) {
                    $temps = $response->json()['hourly']['temperature_2m'];
                    return [
                        $temps[0] ?? 26, $temps[4] ?? 26, $temps[8] ?? 27, 
                        $temps[12] ?? 30, $temps[16] ?? 29, $temps[20] ?? 27, $temps[23] ?? 26
                    ];
                }
            } catch (\Exception $e) {}
            $base = $port->temp ?? 27;
            return [$base-2, $base-1, $base, $base+3, $base+2, $base, $base-2];
        });

        $forexTimeline = Cache::remember("forex_weekly_real_data_" . $currencyCode, 3600, function() use ($currencyCode, $liveForexRate) {
            try {
               
                $response = Http::get("https://open.er-api.com/v6/latest/USD");
                if ($response->successful() && isset($response->json()['rates'])) {
                    $current = $response->json()['rates'][$currencyCode] ?? $liveForexRate;
                    
                    return [
                        $current * 0.993,
                        $current * 0.997,
                        $current * 1.002,
                        $current * 0.996,
                        $current
                    ];
                }
            } catch (\Exception $e) {}
            return [$liveForexRate * 0.99, $liveForexRate * 0.995, $liveForexRate * 1.001, $liveForexRate * 0.998, $liveForexRate];
        });

        $exchangeData = [
            'currency_code' => $currencyCode,
            'rate_against_usd' => $liveForexRate,
            'flag_url' => 'https://flagcdn.com/' . strtolower(substr($countryCode, 0, 2)) . '.svg',
            'weather_data' => $weatherTimeline,
            'forex_data' => $forexTimeline
        ];

        $allCustomVessels = session('active_custom_vessels', []);
        $customInboundVessels = array_filter($allCustomVessels, function ($vessel) use ($port) {
            return isset($vessel['dest_name']) && $vessel['dest_name'] === $port->name;
        });
        $customOutboundVessels = array_filter($allCustomVessels, function ($vessel) use ($port) {
            return isset($vessel['origin_name']) && $vessel['origin_name'] === $port->name;
        });

        $realCargoCount = 0; $realTankerCount = 0; $realTugCount = 0;
        $allShipments = $port->inboundShipments->merge($port->outboundShipments ?? collect([]));

        foreach ($allShipments as $shipment) {
            $vesselName = strtoupper($shipment->vessel_name);
            if (str_contains($vesselName, 'CONTAINER') || str_contains($vesselName, 'EXPLORER') || str_contains($vesselName, 'MARU') || str_contains($vesselName, 'CARGO')) { $realCargoCount++; }
            elseif (str_contains($vesselName, 'TANKER') || str_contains($vesselName, 'GAS') || str_contains($vesselName, 'OIL')) { $realTankerCount++; }
            else { $realTugCount++; }
        }
        foreach (array_merge($customInboundVessels, $customOutboundVessels) as $vessel) {
            $vesselName = strtoupper($vessel['name']);
            if (str_contains($vesselName, 'CONTAINER') || str_contains($vesselName, 'EXPLORER') || str_contains($vesselName, 'MARU')) { $realCargoCount++; }
            elseif (str_contains($vesselName, 'EXPRESS') || str_contains($vesselName, 'BULK') || str_contains($vesselName, 'TANKER')) { $realTankerCount++; }
            else { $realTugCount++; }
        }
        if ($realTugCount === 0 && ($realCargoCount > 0 || $realTankerCount > 0)) {
            $realTugCount = ($realCargoCount + $realTankerCount) > 3 ? 2 : 1;
        }

        if (isset($this->weatherService) && method_exists($this->weatherService, 'updatePortWeather')) {
            try { $this->weatherService->updatePortWeather($port); $port->refresh(); } catch (\Exception $e) {}
        }

        $newsData = [];
        if (isset($this->newsService) && method_exists($this->newsService, 'getLatestNews')) {
            try { $newsData = $this->newsService->getLatestNews($port->country->name); } catch (\Exception $e) {}
        }

        $totalInboundCount = $port->inboundShipments->count() + count($customInboundVessels);
        $radarData = [
            'warna_status' => $totalInboundCount > 0 ? 'warning' : 'success',
            'status_kepadatan' => $totalInboundCount > 0 ? 'Medium Traffic' : 'Low Traffic',
            'estimasi_antrean_sandar' => $totalInboundCount > 0 ? '1-3 Jam' : '0-2 Jam'
        ];

        return view('ports.show', compact(
            'port', 'exchangeData', 'radarData', 'newsData', 'customInboundVessels', 'customOutboundVessels',
            'realCargoCount', 'realTankerCount', 'realTugCount'
        ));
    }

    public function createCargo()
    {
        $ports = Port::with('country')->orderBy('name', 'asc')->get();
        $vesselsByPort = [];
        foreach ($ports as $port) {
            $shortName = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $port->name), 0, 8));
            $vesselsByPort[$port->id] = [
                ['id' => $port->id . '01', 'name' => $shortName . '-EXPLORER (Container Ready)'],
                ['id' => $port->id . '02', 'name' => $shortName . '-EXPRESS (Bulk Carrier Ready)'],
                ['id' => $port->id . '03', 'name' => $shortName . '-MARU (General Cargo Ready)']
            ];
        }
        return view('ports.create_cargo', compact('ports', 'vesselsByPort'));
    }

    public function storeCargo(Request $request)
    {
        $request->validate([
            'origin_port'      => 'required|exists:ports,id',
            'vessel_id'        => 'required|string', 
            'destination_port' => 'required|exists:ports,id|different:origin_port',
            'cargo_weight'     => 'required|numeric|min:1',
            'currency_value'   => 'required|numeric|min:1',
        ], [
            'destination_port.different' => 'Pelabuhan tujuan tidak boleh sama dengan pelabuhan asal.',
            'vessel_id.required'         => 'Anda harus memilih armada kapal yang tersedia di pelabuhan asal.',
        ]);

        $vesselLabel = $request->input('vessel_name_hidden');
        if (empty($vesselLabel)) { $vesselLabel = 'LOGIXCHAIN-CARRIER (#' . $request->vessel_id . ')'; }

        $originPort = Port::find($request->origin_port);
        $destPort = Port::find($request->destination_port);

        $newVesselData = [
            'id'               => uniqid(), 
            'name'             => strtoupper($vesselLabel),
            'lat'              => (float) ($originPort->latitude ?? -6.1014),
            'lng'              => (float) ($originPort->longitude ?? 106.8831),
            'live_lat'         => (float) ($originPort->latitude ?? -6.1014),
            'live_lng'         => (float) ($originPort->longitude ?? 106.8831),
            'origin_name'      => $originPort->name ?? 'Pelabuhan Asal',
            'dest_name'        => $destPort->name ?? 'Pelabuhan Tujuan',
            'dest_lat'         => (float) ($destPort->latitude ?? 51.9488),
            'dest_lng'         => (float) ($destPort->longitude ?? 4.1430),
            'cargo_weight'     => $request->cargo_weight,
            'currency_value'   => $request->currency_value,
            'status'           => 'DEPARTING',
            'step'             => 0 
        ];

        $request->session()->push('active_custom_vessels', $newVesselData);

        return redirect()->route('cargo.create')->with(
            'success', 
            '🔒🔒🔒 LOGIXCHAIN SECURE: Manifest rute berhasil dikunci! Kapal ditambahkan ke radar pelayaran.'
        );
    }

    public function destroyVessel($id)
    {
        $vessels = session('active_custom_vessels', []);
        $filteredVessels = array_filter($vessels, function ($vessel) use ($id) { return $vessel['id'] !== $id; });
        session(['active_custom_vessels' => array_values($filteredVessels)]);

        return response()->json([
            'status' => 'success',
            'message' => 'Vessel successfully dismissed from radar.'
        ]);
    }

    public function history(Request $request)
    {
        $ports = Port::with('country')->get();
        $allCustomVessels = session('active_custom_vessels', []);
        
        $completedVesselsFilter = array_filter($allCustomVessels, function ($vessel) {
            return isset($vessel['step']) && (int)$vessel['step'] >= 1500;
        });

        $completedVessels = [];

        foreach ($completedVesselsFilter as $vessel) {
            $originPort = $ports->first(function($p) use ($vessel) {
                return strtolower(trim($p->name)) === strtolower(trim($vessel['origin_name']));
            });
            
            $destPort = $ports->first(function($p) use ($vessel) {
                return strtolower(trim($p->name)) === strtolower(trim($vessel['dest_name']));
            });

            $originISO = $originPort->country->code ?? null;
            if (!$originISO) {
                $originISO = str_contains(strtolower($vessel['origin_name']), 'klang') ? 'MYS' : (str_contains(strtolower($vessel['origin_name']), 'hamburg') ? 'DEU' : (str_contains(strtolower($vessel['origin_name']), 'moresby') ? 'PNG' : 'IDN'));
            }

            $destISO = $destPort->country->code ?? null;
            if (!$destISO) {
                $destISO = str_contains(strtolower($vessel['dest_name']), 'catania') ? 'ITA' : (str_contains(strtolower($vessel['dest_name']), 'vladivostok') ? 'RUS' : (str_contains(strtolower($vessel['dest_name']), 'dublin') ? 'IRL' : 'NLD'));
            }

            $vessel['origin_country_iso'] = strtoupper(trim($originISO));
            $vessel['dest_country_iso'] = strtoupper(trim($destISO));

            $completedVessels[] = $vessel;
        }

        $totalCompleted = count($completedVessels);
        $totalCargoDelivered = array_sum(array_column($completedVessels, 'cargo_weight'));
        $totalOperationalCost = array_sum(array_column($completedVessels, 'currency_value'));

        return view('ports.history', compact('completedVessels', 'totalCompleted', 'totalCargoDelivered', 'totalOperationalCost'));
    }
}