<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Port;
use App\Models\Country;
use App\Models\Shipment;
use App\Models\CountryEconomicHistory;
use App\Models\PortWeatherHistory;
use App\Models\CountryWeatherHistory;
use App\Services\CountryService;
use App\Services\EconomicService;
use App\Services\WeatherService;
use App\Services\ExchangeRateService;
use App\Services\MarineTrafficService;
use App\Services\RiskAssessmentService;
use Illuminate\Support\Facades\DB;

class SyncGlobalSupplyChain extends Command
{
    
    protected $signature = 'supply-chain:sync
                            {--country-limit=10 : Number of countries to refresh from World Bank per batch}
                            {--port-limit=25 : Number of ports to refresh from Open-Meteo per batch}
                            {--refresh-countries : Refresh the complete REST Countries dataset before processing the batch}';

   
    protected $description = 'Sinkronisasi massal data dari 7 API logistik global, cuaca, ekonomi, dan pergerakan kapal live';

    
    public function handle(
        CountryService $countryService,
        EconomicService $economicService,
        WeatherService $weatherService,
        ExchangeRateService $exchangeRateService,
        MarineTrafficService $marineTrafficService,
        RiskAssessmentService $riskAssessmentService
    ): int {
        set_time_limit(0);
        ini_set('max_execution_time', '0');

        $this->info('======= MEMULAI SINKRONISASI GLOBAL SUPPLY CHAIN =======');

        
        if ($this->option('refresh-countries')) {
            $this->info('-> Mengompilasi profil negara dari REST Countries...');
            $countryService->syncAllCountriesBulk();
        }

        $countryLimit = max(0, (int) $this->option('country-limit'));
        $portLimit = max(0, (int) $this->option('port-limit'));

        $this->info("-> Memperbarui maksimal {$countryLimit} indikator World Bank...");
        $latestRisk = DB::table('risk_scores')
            ->selectRaw('country_code, MAX(calculated_at) AS latest_risk_at')
            ->groupBy('country_code');
        $countryQuery = Country::query()
            ->leftJoinSub($latestRisk, 'latest_risk', fn ($join) => $join->on('countries.code', '=', 'latest_risk.country_code'))
            ->orderByRaw('(countries.gdp IS NULL AND countries.inflation_rate IS NULL) DESC')
            ->orderByRaw('latest_risk.latest_risk_at IS NULL DESC')
            ->orderBy('latest_risk.latest_risk_at')
            ->select('countries.*');
        if ($countryLimit > 0) {
            $countryQuery->limit($countryLimit);
        }
        $countries = $countryQuery->get();
        foreach ($countries as $country) {
            $economicService->updateCountryEconomicIndicators($country);
            $country->refresh();
            // A null is retained as a genuine “not published by World Bank”
            // observation, rather than being hidden as an unchecked country.
            CountryEconomicHistory::create([
                'country_code' => $country->code,
                'gdp' => $country->gdp,
                'inflation_rate' => $country->inflation_rate,
                'recorded_at' => now(),
            ]);
            $hasPortWeather = $country->ports()->whereNotNull('temp')->exists();
            if (! $hasPortWeather) {
                $countryWeather = $weatherService->getCountryWeather($country);
                if ($countryWeather !== null) {
                    CountryWeatherHistory::create(array_merge($countryWeather, [
                        'country_code' => $country->code,
                        'recorded_at' => now(),
                    ]));
                }
            }
            $riskAssessmentService->calculateCountryRisk($country);
        }

        $this->info("-> Memantau maksimal {$portLimit} port melalui Open-Meteo dan kalkulasi risiko...");
        $missingWeatherCountryCodes = Country::query()
            ->whereHas('ports')
            ->whereDoesntHave('ports.weatherHistories')
            ->pluck('code');
        $portQuery = Port::query()
            ->when($missingWeatherCountryCodes->isNotEmpty(), fn ($query) => $query->whereIn('country_code', $missingWeatherCountryCodes))
            ->orderByRaw('temp IS NULL DESC')
            ->orderBy('updated_at');
        if ($portLimit > 0) {
            $portQuery->limit($portLimit);
        }
        $ports = $portQuery->get();
        foreach ($ports as $port) {
            
            $weatherService->updatePortWeather($port);
            
            $riskAssessmentService->calculatePortRisk($port);
            $port->refresh();
            if ($port->temp !== null || $port->wind_speed !== null || $port->rain !== null) {
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
        }

       
        $this->info('-> Mengalkulasi posisi koordinat kapal dan fluktuasi kurs logistik...');
        $shipments = Shipment::where('status', 'ON_VOYAGE')->get();
        foreach ($shipments as $shipment) {
           
            $marineTrafficService->updateShipmentLocation($shipment);
            
            $exchangeRateService->updateShipmentExchangeRate($shipment);
            
            $riskAssessmentService->calculateShipmentRisk($shipment);
        }

        $this->info('======= SINKRONISASI SELESAI: DATABASE LOKAL SUDAH TER-UPDATE =======');
        return Command::SUCCESS;
    }
}
