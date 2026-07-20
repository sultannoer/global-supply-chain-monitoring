<?php

namespace App\Http\Controllers;

use App\Models\Port;
use App\Models\Shipment;
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

        // Mengambil seluruh data port dari database beserta relasi negaranya
        $ports = Port::with(['country'])->get();

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

        $activeCountryCodes = $ports->pluck('country_code')->unique()->filter()->toArray();

        $enrichedCountries = \App\Models\Country::whereIn('code', $activeCountryCodes)->get()->map(function ($country) use ($allLiveRates, $ports) {
            $currencyCode = $country->currency_code ?? 'USD';
            $rate = $allLiveRates[$currencyCode] ?? 1.00;

            // Mencari pelabuhan terdekat hanya sebagai fallback darurat jika koordinat negara benar-benar NULL
            $relatedPort = $ports->firstWhere('country_code', $country->code);
            $fallbackLat = $relatedPort ? (float)$relatedPort->latitude : 0.0;
            $fallbackLng = $relatedPort ? (float)$relatedPort->longitude : 0.0;

            // Memprioritaskan koordinat daratan tengah asli milik negara dari tabel countries
            $countryLat = !is_null($country->latitude) ? (float)$country->latitude : $fallbackLat;
            $countryLng = !is_null($country->longitude) ? (float)$country->longitude : $fallbackLng;

            return [
                'id' => $country->code,
                'name' => $country->name ?? 'Sovereign Hub Region',
                'lat' => $countryLat, 
                'lng' => $countryLng,
                'code' => strtolower($country->code),
                'currency' => $currencyCode,
                'rate' => $rate,
                'region' => $country->region ?? 'Global Strategic Hub',
                'language' => $country->language ?? 'Official Language',
                'gdp' => $country->gdp && $country->gdp > 0 
                    ? '$' . number_format($country->gdp / 1e9, 1) . 'B' 
                    : '$900B',
                'inflation' => ($country->inflation_rate && $country->inflation_rate > 0 ? $country->inflation_rate : '2.3') . '%',
                'population' => $country->population ? number_format($country->population) : 'Integrated',
                'export' => $country->export_volume && $country->export_volume > 0 ? '$' . number_format($country->export_volume / 1e9, 1) . 'B' : '$250B',
                'import' => $country->import_volume && $country->import_volume > 0 ? '$' . number_format($country->import_volume / 1e9, 1) . 'B' : '$210B',
            ];
        })->values()->toArray();

        $enrichedStorms = [
            ['name' => 'Badai Tropis Selat Malaka', 'lat' => 4.00, 'lng' => 100.00, 'radius_km' => 450, 'wind_speed' => '25 m/s'],
            ['name' => 'Siklon Tropis Filipina Selatan', 'lat' => 8.50, 'lng' => 126.20, 'radius_km' => 350, 'wind_speed' => '30 m/s']
        ];

        $dbShipments = Shipment::with(['originPort', 'destinationPort'])->get();
        $allCustomVessels = $dbShipments->map(function($s) {
            return [
                'id' => $s->id,
                'name' => $s->vessel_name,
                'lat' => (float) ($s->originPort->latitude ?? 0),
                'lng' => (float) ($s->originPort->longitude ?? 0),
                'live_lat' => (float) ($s->current_lat ?? $s->originPort->latitude ?? 0),
                'live_lng' => (float) ($s->current_lng ?? $s->originPort->longitude ?? 0),
                'origin_name' => $s->originPort->name ?? 'Unknown',
                'dest_name' => $s->destinationPort->name ?? 'Unknown',
                'dest_lat' => (float) ($s->destinationPort->latitude ?? 0),
                'dest_lng' => (float) ($s->destinationPort->longitude ?? 0),
                'cargo_weight' => $s->cargo_weight,
                'currency_value' => $s->initial_cost_usd,
                'status' => $s->status,
                'step' => $s->step
            ];
        })->toArray();

        $enrichedVessels = array_map(function ($vessel) use ($enrichedPorts, $enrichedStorms) {
            $destPort = collect($enrichedPorts)->firstWhere('name', $vessel['dest_name']);
            
            $insideStormStatic = false;
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
                    $insideStormStatic = true;
                    break;
                }
            }

            $marineWeather = $this->weatherService->getMarineWeather($currentLat, $currentLng);
            
            $insideStorm = $insideStormStatic || $marineWeather['is_storm_risk'];
            $midOceanTemp = $marineWeather['temp'];
            $midOceanWind = $marineWeather['wind'];
            $rain = $marineWeather['rain'];

            $stormRisk = $insideStorm 
                ? '⚠️ ALERT: Critical Storm Impact Encountered' 
                : ($midOceanWind > 15 ? '⚠️ ALERT: High Wind Risk Encountered' : '🌤️ Calm Sea Condition');
            
            $rate = $destPort['rate'] ?? 1.00;
            $lossImpact = $insideStorm ? '⚠️ LOSS METRIC: Devisa Penalty Applied (-15%)' : ($midOceanWind > 15 ? 'Potential Currency Loss (-1.2%)' : 'Stable (+0.4%)');

            return array_merge($vessel, [
                'temp' => $midOceanTemp,
                'wind' => $midOceanWind,
                'rain' => $rain,
                'storm_alert' => $stormRisk,
                'exchange_rate' => $rate,
                'currency_code' => $destPort['currency'] ?? 'USD',
                'currency_loss' => $lossImpact,
                'dest_gdp' => $destPort['gdp'] ?? '$1.2T',
                'dest_inflation' => $destPort['inflation'] ?? '2.1%'
            ]);
        }, $allCustomVessels);

        $activeAlerts = \App\Models\RiskAlert::with(['shipment', 'port'])
                ->where('is_resolved', false)
                ->orderBy('created_at', 'desc')
                ->take(5)
                ->get();

        return view('ports.index', compact('enrichedPorts', 'enrichedCountries', 'enrichedVessels', 'enrichedStorms', 'search', 'activeAlerts'));
    }

    public function updateVesselCoordinates(Request $request, $id)
    {
        $shipment = Shipment::findOrFail($id);
        $shipment->current_lat = (float) $request->input('live_lat');
        $shipment->current_lng = (float) $request->input('live_lng');
        
        if ($request->has('step')) {
            $shipment->step = (int) $request->input('step');
        }
        
        if ($shipment->step >= 1500) {
            $shipment->status = 'ARRIVED';
        }
        
        $shipment->save();
        
        return response()->json(['status' => 'success']);
    }

    public function show($id)
    {
        $port = Port::with(['country', 'inboundShipments', 'outboundShipments'])->findOrFail($id);
        
        $allShipments = $port->inboundShipments->merge($port->outboundShipments ?? collect([]));
        foreach ($allShipments as $shipment) {
            if ($shipment->status === 'ON_VOYAGE' || $shipment->status === 'DEPARTING') {
                if (isset($this->marineService)) {
                    try {
                        $this->marineService->updateShipmentLocation($shipment);
                    } catch (\Exception $e) {
                        \Log::warning("MarineTraffic simulasi gagal untuk TRK-{$shipment->tracking_number}: " . $e->getMessage());
                    }
                }
            }
        }
        $port->refresh(); 

        $countryCode = $port->country->code ?? 'IDN'; 
        $apiStatus = 'OK'; 

        try {
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
        } catch (\Exception $e) {
            \Log::error("Koneksi World Bank / REST Countries terganggu: " . $e->getMessage());
            $apiStatus = 'FALLBACK_ACTIVE';
        }

        $currencyCode = $port->country->currency_code ?? 'USD';
        
        $liveForexRate = Cache::remember("live_rate_" . $currencyCode, 3600, function() use ($currencyCode) {
            try {
                $response = Http::timeout(4)->get("https://open.er-api.com/v6/latest/USD");
                if ($response->successful()) {
                    return $response->json("rates.{$currencyCode}") ?? 1.00;
                }
            } catch (\Exception $e) {
                \Log::warning("Forex API gagal merespons, menggunakan kurs database internal.");
            }
            return 1.00;
        });

        $weatherTimeline = Cache::remember("weather_hourly_forecast_" . $port->id, 1800, function() use ($port) {
            try {
                $lat = $port->latitude;
                $lng = $port->longitude;
                $response = Http::timeout(4)->get("https://api.open-meteo.com/v1/forecast?latitude={$lat}&longitude={$lng}&hourly=temperature_2m&forecast_days=1");
                
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
                $response = Http::timeout(4)->get("https://open.er-api.com/v6/latest/USD");
                if ($response->successful() && isset($response->json()['rates'])) {
                    $current = $response->json()['rates'][$currencyCode] ?? $liveForexRate;
                    return [ $current * 0.993, $current * 0.997, $current * 1.002, $current * 0.996, $current ];
                }
            } catch (\Exception $e) {}
            return [$liveForexRate * 0.99, $liveForexRate * 0.995, $liveForexRate * 1.001, $liveForexRate * 0.998, $liveForexRate];
        });

        $exchangeData = [
            'currency_code' => $currencyCode,
            'rate_against_usd' => $liveForexRate,
            'flag_url' => 'https://flagcdn.com/' . strtolower(substr($countryCode, 0, 2)) . '.svg',
            'weather_data' => $weatherTimeline,
            'forex_data' => $forexTimeline,
            'api_status' => $apiStatus
        ];

        $dbShipments = Shipment::with(['originPort', 'destinationPort'])->get();
        $allCustomVessels = $dbShipments->map(function($s) {
            return [
                'id' => $s->id,
                'name' => $s->vessel_name,
                'origin_name' => $s->originPort->name ?? 'Unknown',
                'dest_name' => $s->destinationPort->name ?? 'Unknown',
            ];
        })->toArray();

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
        
        Shipment::create([
            'tracking_number'     => 'TRK-' . strtoupper(uniqid()),
            'vessel_name'         => strtoupper($vesselLabel),
            'origin_port_id'      => $request->origin_port,
            'destination_port_id' => $request->destination_port,
            'current_lat'         => $originPort->latitude ?? -6.1014,
            'current_lng'         => $originPort->longitude ?? 106.8831,
            'initial_cost_usd'    => $request->currency_value,
            'cargo_weight'        => $request->cargo_weight,
            'status'              => 'DEPARTING',
            'step'                => 0
        ]);

        return redirect()->route('cargo.create')->with(
            'success', 
            '🔒🔒🔒 LOGIXCHAIN SECURE: Manifest rute berhasil dikunci! Kapal ditambahkan ke radar pelayaran.'
        );
    }

    public function destroyVessel($id)
    {
        Shipment::destroy($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Vessel successfully dismissed from radar.'
        ]);
    }

    public function history(Request $request)
    {
        $dbCompletedShipments = Shipment::with(['originPort', 'destinationPort'])
                                        ->where('step', '>=', 1500)
                                        ->get();

        $completedVessels = [];

        foreach ($dbCompletedShipments as $shipment) {
            $originName = $shipment->originPort->name ?? 'Unknown';
            $destName = $shipment->destinationPort->name ?? 'Unknown';

            $originISO = $shipment->originPort->country->code ?? 'IDN';
            $destISO = $shipment->destinationPort->country->code ?? 'NLD';

            $completedVessels[] = [
                'id'                 => $shipment->id,
                'name'               => $shipment->vessel_name,
                'origin_name'        => $originName,
                'dest_name'          => $destName,
                'cargo_weight'       => $shipment->cargo_weight,
                'currency_value'     => $shipment->initial_cost_usd,
                'origin_country_iso' => strtoupper($originISO),
                'dest_country_iso'   => strtoupper($destISO),
            ];
        }

        $totalCompleted = count($completedVessels);
        $totalCargoDelivered = array_sum(array_column($completedVessels, 'cargo_weight'));
        $totalOperationalCost = array_sum(array_column($completedVessels, 'currency_value'));

        return view('ports.history', compact('completedVessels', 'totalCompleted', 'totalCargoDelivered', 'totalOperationalCost'));
    }

    public function showCountry($code)
    {
        $codeUpper = strtoupper($code);
        
        // 1. Ambil atau buat data dasar negara di DB lokal
        $country = \App\Models\Country::firstOrCreate(
            ['code' => $codeUpper],
            ['name' => 'Sovereign Hub Region', 'currency_code' => 'USD']
        );

        // Paksa bersihkan cache halaman ini agar selalu mengambil data segar dari API
        Cache::forget("sovereign_country_api_v3_" . $codeUpper);

        // 2. KONSUMSI REST COUNTRIES & WORLD BANK SECARA FULL DINAMIS
        $apiData = Cache::remember("sovereign_country_api_v3_" . $codeUpper, 86400, function() use ($codeUpper) {
            
            // Siapkan struktur data default (Mekanisme pertahanan jika API luar gagal merespons)
            $data = [
                'name' => 'Sovereign State', 
                'region' => 'Global Logistics Hub', 
                'language' => 'Official Language',
                'currency' => 'USD', 
                'currency_name' => 'US Dollar', 
                'cca2' => strtolower(substr($codeUpper, 0, 2)), // Fallback awal bendera (2 digit)
                'gdp' => 0, 'inflation' => 0, 'population' => 0, 'export' => 0, 'import' => 0, 
                'rate_to_usd' => 1.00
            ];

            // 🅰️ 100% DINAMIS: Tembak REST Countries API dengan Bypass SSL Validator
            try {
                $restResponse = Http::withoutVerifying()
                                    ->timeout(10)
                                    ->get("https://restcountries.com/v3.1/alpha/{$codeUpper}");

                if ($restResponse->successful() && isset($restResponse->json()[0])) {
                    $cData = $restResponse->json()[0];
                    
                    // Ekstraksi Nama Negara Resmi & Region
                    $data['name'] = $cData['name']['common'] ?? $data['name'];
                    $data['region'] = $cData['region'] ?? $data['region'];
                    
                    // Ekstraksi Kode 2 Digit Resmi dari API untuk Gambar Bendera (Pasti Akurat, misal UKR -> ua)
                    if (isset($cData['cca2'])) {
                        $data['cca2'] = strtolower($cData['cca2']);
                    }
                    
                    // Ekstraksi Semua Bahasa yang Digunakan di Negara Terkait
                    if (isset($cData['languages']) && is_array($cData['languages'])) {
                        $data['language'] = implode(', ', array_values($cData['languages']));
                    }
                    
                    // Ekstraksi Simbol & Nama Mata Uang Resmi Negara
                    if (isset($cData['currencies']) && is_array($cData['currencies'])) {
                        $currCode = array_key_first($cData['currencies']);
                        $data['currency'] = strtoupper($currCode);
                        $data['currency_name'] = $cData['currencies'][$currCode]['name'] ?? 'Official Currency';
                    }
                }
            } catch (\Exception $e) {
                \Log::warning("REST Countries API bermasalah untuk {$codeUpper}: " . $e->getMessage());
            }

            // 🅱️ DINAMIS: Tembak World Bank API untuk Indikator Makroekonomi
            try {
                // Gross Domestic Product (GDP)
                $gdpRes = Http::withoutVerifying()->timeout(5)->get("https://api.worldbank.org/v2/country/{$codeUpper}/indicator/NY.GDP.MKTP.CD?format=json&per_page=1");
                if ($gdpRes->successful() && isset($gdpRes->json()[1][0]['value'])) {
                    $data['gdp'] = (float) $gdpRes->json()[1][0]['value'];
                }
                // Laju Inflasi
                $infRes = Http::withoutVerifying()->timeout(5)->get("https://api.worldbank.org/v2/country/{$codeUpper}/indicator/FP.CPI.TOTL.ZG?format=json&per_page=1");
                if ($infRes->successful() && isset($infRes->json()[1][0]['value'])) {
                    $data['inflation'] = (float) $infRes->json()[1][0]['value'];
                }
                // Jumlah Populasi
                $popRes = Http::withoutVerifying()->timeout(5)->get("https://api.worldbank.org/v2/country/{$codeUpper}/indicator/SP.POP.TOTL?format=json&per_page=1");
                if ($popRes->successful() && isset($popRes->json()[1][0]['value'])) {
                    $data['population'] = (int) $popRes->json()[1][0]['value'];
                }
                // Volume Ekspor
                $expRes = Http::withoutVerifying()->timeout(5)->get("https://api.worldbank.org/v2/country/{$codeUpper}/indicator/NE.EXP.GNFS.CD?format=json&per_page=1");
                if ($expRes->successful() && isset($expRes->json()[1][0]['value'])) {
                    $data['export'] = (float) $expRes->json()[1][0]['value'];
                }
                // Volume Impor
                $impRes = Http::withoutVerifying()->timeout(5)->get("https://api.worldbank.org/v2/country/{$codeUpper}/indicator/NE.IMP.GNFS.CD?format=json&per_page=1");
                if ($impRes->successful() && isset($impRes->json()[1][0]['value'])) {
                    $data['import'] = (float) $impRes->json()[1][0]['value'];
                }
            } catch (\Exception $e) {}

            // 🅲️ DINAMIS: Tembak Exchange Rate API Menggunakan Kode Mata Uang Hasil REST Countries
            try {
                $targetCurr = $data['currency']; 
                $forexRes = Http::withoutVerifying()->timeout(5)->get("https://open.er-api.com/v6/latest/USD");
                if ($forexRes->successful() && isset($forexRes->json()['rates'][$targetCurr])) {
                    $data['rate_to_usd'] = (float) $forexRes->json()['rates'][$targetCurr];
                }
            } catch (\Exception $e) {}

            return $data;
        });

        // 3. Sinkronisasikan secara otomatis data hasil API ke dalam database lokal kamu
        $country->update([
            'name' => $apiData['name'] !== 'Sovereign State' ? $apiData['name'] : $country->name,
            'region' => $apiData['region'],
            'language' => $apiData['language'],
            'currency_code' => $apiData['currency'],
            'gdp' => $apiData['gdp'] > 0 ? $apiData['gdp'] : $country->gdp,
            'inflation_rate' => $apiData['inflation'] > 0 ? $apiData['inflation'] : $country->inflation_rate,
            'population' => $apiData['population'] > 0 ? $apiData['population'] : $country->population,
            'export_volume' => $apiData['export'] > 0 ? $apiData['export'] : $country->export_volume,
            'import_volume' => $apiData['import'] > 0 ? $apiData['import'] : $country->import_volume,
        ]);

        // 4. Ambil infrastruktur pelabuhan terkait dari DB
        $relatedPorts = \App\Models\Port::where('country_code', $country->code)->get();

        return view('ports.countries', compact('country', 'apiData', 'relatedPorts'));
    }
    
}