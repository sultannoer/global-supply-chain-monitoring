<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Port;
use App\Models\Country;
use App\Models\Shipment;
use App\Services\CountryService;
use App\Services\EconomicService;
use App\Services\WeatherService;
use App\Services\ExchangeRateService;
use App\Services\MarineTrafficService;
use App\Services\RiskAssessmentService;

class SyncGlobalSupplyChain extends Command
{
    
    protected $signature = 'supply-chain:sync';

   
    protected $description = 'Sinkronisasi massal data dari 7 API logistik global, cuaca, ekonomi, dan pergerakan kapal live';

    
    public function handle(
        CountryService $countryService,
        EconomicService $economicService,
        WeatherService $weatherService,
        ExchangeRateService $exchangeRateService,
        MarineTrafficService $marineTrafficService,
        RiskAssessmentService $riskAssessmentService
    ): int {
        $this->info('======= MEMULAI SINKRONISASI GLOBAL SUPPLY CHAIN =======');

        
        $this->info('-> Mengompilasi profil negara dan indikator World Bank...');
        $countries = Country::all();
        foreach ($countries as $country) {
           
            $countryService->fetchAndSyncCountry($country->code);
            
            $economicService->updateCountryEconomicIndicators($country);
        }

        
        $this->info('-> Memantau radar cuaca live dan kalkulasi risiko pelabuhan...');
        $ports = Port::all();
        foreach ($ports as $port) {
            
            $weatherService->updatePortWeather($port);
            
            $riskAssessmentService->calculatePortRisk($port);
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