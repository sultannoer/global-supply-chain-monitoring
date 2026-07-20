<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Country;

class EconomicService
{
    /**
     * Memperbarui indikator ekonomi Makro suatu negara via World Bank API
     */
    public function updateCountryEconomicIndicators(Country $country): bool
    {
        try {
            // Pastikan menggunakan ISO code 2 atau 3 huruf (sesuaikan dengan tabelmu, misal $country->iso_code atau code)
            $code = strtolower($country->code ?? $country->iso_code);

            if (!$code) {
                return false;
            }

            // 1. Tarik Data GDP (Gross Domestic Product)
            $gdpResponse = Http::get("https://api.worldbank.org/v2/country/{$code}/indicator/NY.GDP.MKTP.CD?format=json&per_page=1");
            
            // 2. Tarik Data Inflasi (%)
            $inflationResponse = Http::get("https://api.worldbank.org/v2/country/{$code}/indicator/FP.CPI.TOTL.ZG?format=json&per_page=1");
            
            // 3. Tarik Data Populasi
            $popResponse = Http::get("https://api.worldbank.org/v2/country/{$code}/indicator/SP.POP.TOTL?format=json&per_page=1");

            // 4. Tarik Data Ekspor Asli (Export of Goods and Services)
            $exportResponse = Http::get("https://api.worldbank.org/v2/country/{$code}/indicator/NE.EXP.GNFS.CD?format=json&per_page=1");

            // 5. Tarik Data Impor Asli (Import of Goods and Services)
            $importResponse = Http::get("https://api.worldbank.org/v2/country/{$code}/indicator/NE.IMP.GNFS.CD?format=json&per_page=1");

            // Fallback ke data lama jika API gagal
            $gdp = $country->gdp;
            $inflation = $country->inflation_rate;
            $population = $country->population;
            $exportVolume = $country->export_volume;
            $importVolume = $country->import_volume;

            if ($gdpResponse->successful() && isset($gdpResponse->json()[1][0]['value'])) {
                $gdp = $gdpResponse->json()[1][0]['value'];
            }

            if ($inflationResponse->successful() && isset($inflationResponse->json()[1][0]['value'])) {
                $inflation = round($inflationResponse->json()[1][0]['value'], 2); // Bulatkan 2 desimal
            }

            if ($popResponse->successful() && isset($popResponse->json()[1][0]['value'])) {
                $population = $popResponse->json()[1][0]['value'];
            }

            if ($exportResponse->successful() && isset($exportResponse->json()[1][0]['value'])) {
                $exportVolume = $exportResponse->json()[1][0]['value'];
            }

            if ($importResponse->successful() && isset($importResponse->json()[1][0]['value'])) {
                $importVolume = $importResponse->json()[1][0]['value'];
            }

            // Simpan semua data asli ke database
            $country->update([
                'gdp' => $gdp,
                'inflation_rate' => $inflation,
                'population' => $population,
                'export_volume' => $exportVolume, 
                'import_volume' => $importVolume, 
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error("Gagal memperbarui indikator ekonomi World Bank untuk negara {$country->code}: " . $e->getMessage());
            return false;
        }
    }
}