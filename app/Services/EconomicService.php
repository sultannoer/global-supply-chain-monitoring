<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Country;

class EconomicService
{
    
    public function updateCountryEconomicIndicators(Country $country): bool
    {
        try {
            $code = strtolower($country->code);

           
            $gdpResponse = Http::get("https://api.worldbank.org/v2/country/{$code}/indicator/NY.GDP.MKTP.CD?format=json&per_page=1");
            
            
            $inflationResponse = Http::get("https://api.worldbank.org/v2/country/{$code}/indicator/FP.CPI.TOTL.ZG?format=json&per_page=1");
            
            
            $popResponse = Http::get("https://api.worldbank.org/v2/country/{$code}/indicator/SP.POP.TOTL?format=json&per_page=1");

         
            $gdp = $country->gdp;
            $inflation = $country->inflation_rate;
            $population = $country->population;

            
            if ($gdpResponse->successful() && isset($gdpResponse->json()[1][0]['value'])) {
                $gdp = $gdpResponse->json()[1][0]['value'];
            }

            
            if ($inflationResponse->successful() && isset($inflationResponse->json()[1][0]['value'])) {
                $inflation = $inflationResponse->json()[1][0]['value'];
            }

            
            if ($popResponse->successful() && isset($popResponse->json()[1][0]['value'])) {
                $population = $popResponse->json()[1][0]['value'];
            }

            
            $country->update([
                'gdp' => $gdp,
                'inflation_rate' => $inflation,
                'population' => $population,
                
                'export_volume' => $gdp ? ($gdp * 0.25) : null, 
                'import_volume' => $gdp ? ($gdp * 0.22) : null, 
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error("Gagal memperbarui indikator ekonomi World Bank untuk negara {$country->code}: " . $e->getMessage());
            return false;
        }
    }
}