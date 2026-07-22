<?php

namespace App\Http\Controllers;

use App\Models\Port;
use App\Models\Shipment;
use App\Services\WeatherService;
use App\Services\EconomicService; 
use App\Services\ExchangeRateService;
use App\Services\MarineTrafficService;
use App\Services\NewsService;
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

    public function __construct(
        WeatherService $weatherService,
        EconomicService $economicService, 
        ExchangeRateService $exchangeRateService,
        MarineTrafficService $marineService,
        NewsService $newsService
    ) {
        $this->weatherService = $weatherService;
        $this->economicService = $economicService; 
        $this->exchangeRateService = $exchangeRateService;
        $this->marineService = $marineService;
        $this->newsService = $newsService; 
    }

    public function index(Request $request)
    {
        $search = $request->input('search');

        // Mengambil seluruh data port dari database beserta relasi negaranya
        $ports = Port::with(['country'])->get();

        $allLiveRates = $this->exchangeRateService->getLatestRates()['rates'] ?? [];

        // 1. ENRICHED PORTS (Filter pelabuhan yang punya koordinat valid)
        $enrichedPorts = $ports->filter(function($port) {
            return !is_null($port->latitude) && !is_null($port->longitude);
        })->map(function ($port) use ($allLiveRates) {
            $currencyCode = $port->country->currency_code ?? 'USD';
            $countryName = $port->country->name ?? 'Global Maritime Region';
            $rate = $allLiveRates[$currencyCode] ?? null;
            
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
                    : 'N/A',
                'inflation' => $port->country->inflation_rate && $port->country->inflation_rate > 0 
                    ? $port->country->inflation_rate . '%' 
                    : 'N/A',
                'temp' => $port->temp_celsius ?? $port->temp,
                'wind' => $port->wind_speed_kmh ?? $port->wind_speed,
                'rain' => $port->rain_mm ?? $port->rain ?? 0.0,
                'rate' => $rate,
                'news' => $latestNews
            ];
        })->values();

        if ($search) {
            $enrichedPorts = $enrichedPorts->filter(function($port) use ($search) {
                return str_contains(strtolower($port['name']), strtolower($search)) || 
                       str_contains(strtolower($port['country']), strtolower($search));
            })->values();
        }

        // =========================================================================
        // 2. ENRICHED COUNTRIES: AMBIL SEMUA 251 NEGARA TANPA MEMFILTER HANYA PORTS
        // =========================================================================
        $enrichedCountries = \App\Models\Country::withoutGlobalScopes()->get()->map(function ($country) use ($allLiveRates, $ports) {
            $currencyCode = $country->currency_code ?? 'USD';
            $rate = $allLiveRates[$currencyCode] ?? null;

            // Mencari pelabuhan terdekat hanya sebagai fallback darurat jika koordinat negara NULL
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
                'flag_code' => \App\Services\CountryFlagService::iso2($country->code),
                'currency' => $currencyCode,
                'rate' => $rate,
                'region' => $country->region ?? 'Global Strategic Hub',
                'language' => $country->language ?? 'Official Language',
                'gdp' => $country->gdp && $country->gdp > 0 
                    ? '$' . number_format($country->gdp / 1e9, 1) . 'B' 
                    : 'N/A',
                'inflation' => $country->inflation_rate !== null ? $country->inflation_rate . '%' : 'N/A',
                'population' => $country->population ? number_format($country->population) : 'N/A',
                'export' => $country->export_volume && $country->export_volume > 0 ? '$' . number_format($country->export_volume / 1e9, 1) . 'B' : 'N/A',
                'import' => $country->import_volume && $country->import_volume > 0 ? '$' . number_format($country->import_volume / 1e9, 1) . 'B' : 'N/A',
            ];
        })
        // Pastikan hanya mengirim negara dengan koordinat terisi agar tidak terjadi error render JS
        ->filter(function($c) {
            return $c['lat'] != 0.0 || $c['lng'] != 0.0;
        })
        ->values()->toArray();

        // No synthetic storm markers: weather risk is sourced from Open-Meteo per port/vessel.
        $enrichedStorms = [];

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

            // Jangan memanggil Open-Meteo untuk setiap kapal saat dashboard
            // dibuka. Gunakan snapshot port tujuan; data live dimuat on-demand
            // ketika marker/detail port dipilih.
            $marineWeather = [
                'temp' => $destPort['temp'] ?? null,
                'wind' => $destPort['wind'] ?? null,
                'rain' => $destPort['rain'] ?? null,
                'is_storm_risk' => false,
            ];
            
            $insideStorm = $insideStormStatic || $marineWeather['is_storm_risk'];
            $midOceanTemp = $marineWeather['temp'];
            $midOceanWind = $marineWeather['wind'];
            $rain = $marineWeather['rain'];

            $stormRisk = $insideStorm 
                ? '⚠️ ALERT: Critical Storm Impact Encountered' 
                : ($midOceanWind > 15 ? '⚠️ ALERT: High Wind Risk Encountered' : '🌤️ Calm Sea Condition');
            
            $rate = $destPort['rate'] ?? null;
            $lossImpact = $insideStorm ? '⚠️ LOSS METRIC: Devisa Penalty Applied (-15%)' : ($midOceanWind > 15 ? 'Potential Currency Loss (-1.2%)' : 'Stable (+0.4%)');

            return array_merge($vessel, [
                'temp' => $midOceanTemp,
                'wind' => $midOceanWind,
                'rain' => $rain,
                'storm_alert' => $stormRisk,
                'exchange_rate' => $rate,
                'currency_code' => $destPort['currency'] ?? 'USD',
                'currency_loss' => $lossImpact,
                'dest_gdp' => $destPort['gdp'] ?? 'N/A',
                'dest_inflation' => $destPort['inflation'] ?? 'N/A'
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
        // Country and World Bank data are refreshed by the scheduled sync.
        // A detail page reads that real, locally cached result and must not
        // trigger a complete external synchronisation on every click.
        $apiStatus = 'CACHED_DATA';

        // Priority on-demand refresh: only request World Bank when this
        // specific country's macro data has not been populated yet.
        if ($port->country && ($port->country->gdp === null || $port->country->inflation_rate === null)) {
            Cache::remember("on_demand_economic_{$countryCode}", now()->addHour(), function () use ($port) {
                return $this->economicService->updateCountryEconomicIndicators($port->country);
            });
            $port->load('country');
        }

        $currencyCode = $port->country->currency_code ?? 'USD';
        $liveForexRate = $this->exchangeRateService->getRate($currencyCode);
        if ($liveForexRate === null) {
            $apiStatus = 'FALLBACK_ACTIVE';
        }

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
            return [];
        });

        $exchangeData = [
            'currency_code' => $currencyCode,
            'rate_against_usd' => $liveForexRate,
            'flag_url' => ($flagCode = \App\Services\CountryFlagService::iso2($countryCode))
                ? 'https://flagcdn.com/' . $flagCode . '.svg'
                : null,
            'weather_data' => $weatherTimeline,
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

        if ($port->temp === null || $port->wind_speed === null) {
            // A port without weather data is refreshed immediately, even if it
            // has not reached its background batch yet.
            $this->weatherService->updatePortWeather($port);
        } else {
            Cache::remember("port_weather_refresh_{$port->id}", now()->addMinutes(15), function () use ($port) {
                return $this->weatherService->updatePortWeather($port);
            });
        }
        $port->refresh();

        $newsData = $this->newsService->getLatestNews($port->country->name);

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

            // 🅰️ 100% DINAMIS: Tembak REST Countries API dengan Bypass SSL Validator

            // 🅱️ DINAMIS: Tembak World Bank API untuk Indikator Makroekonomi

            // 🅲️ DINAMIS: Tembak Exchange Rate API Menggunakan Kode Mata Uang Hasil REST Countries


}
