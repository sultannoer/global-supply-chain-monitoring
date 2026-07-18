<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EconomicService
{
    
    protected $baseUrl = 'https://api.worldbank.org/v2/country';

   
    public function getEconomicData($countryCode)
    {
        try {
            
            return [
                'success'   => true,
                'gdp'       => $this->fetchIndicator($countryCode, 'NY.GDP.MKTP.CD'),
                'inflasi'   => $this->fetchIndicator($countryCode, 'FP.CPI.TOTL.ZG'),
                'populasi'  => $this->fetchIndicator($countryCode, 'SP.POP.TOTL'),
                'ekspor'    => $this->fetchIndicator($countryCode, 'NE.EXP.GNFS.ZS'),
                'impor'     => $this->fetchIndicator($countryCode, 'NE.IMP.GNFS.ZS'),
            ];
        } catch (\Exception $e) {
            Log::error('World Bank Service Error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Gagal memuat data ekonomi dari World Bank.'
            ];
        }
    }

    
    private function fetchIndicator($countryCode, $indicatorCode)
    {
        
        $response = Http::get("{$this->baseUrl}/{$countryCode}/indicator/{$indicatorCode}", [
            'format'   => 'json',
            'per_page' => 1
        ]);

        if ($response->successful()) {
            $data = $response->json();

          
            if (isset($data[1]) && count($data[1]) > 0) {
                $latestData = $data[1][0];
                $value = $latestData['value'];
                $year = $latestData['date'];

                if ($value === null) {
                    return 'Data Belum Tersedia';
                }

                
                return $this->formatOutput($indicatorCode, $value) . " ({$year})";
            }
        }

        return 'N/A';
    }

    
    private function formatOutput($indicatorCode, $value)
    {
        switch ($indicatorCode) {
            case 'NY.GDP.MKTP.CD':
                
                if ($value >= 1000000000000) {
                    return '$' . number_format($value / 1000000000000, 2) . ' Triliun';
                }
                return '$' . number_format($value / 1000000000, 2) . ' Miliar';
                
            case 'SP.POP.TOTL':
                
                return number_format($value, 0, ',', '.') . ' Jiwa';
                
            case 'FP.CPI.TOTL.ZG':
            case 'NE.EXP.GNFS.ZS':
            case 'NE.IMP.GNFS.ZS':
                
                return number_format($value, 2) . '%';
                
            default:
                return $value;
        }
    }
}