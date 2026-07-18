<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CountryService
{
    
    protected $baseUrl = 'https://restcountries.com/v3.1/alpha/';

    
    public function getCountryData($countryCode)
    {
        $code = strtolower(trim($countryCode));

        if (empty($code)) {
            return ['success' => false, 'message' => 'Kode negara kosong.'];
        }

        try {

            $response = Http::withHeaders([
                'User-Agent' => 'LogisticsApp/1.0 (Mozilla/5.0)'
            ])->timeout(8)->get($this->baseUrl . $code);

            if ($response->successful()) {
                $jsonData = $response->json();
                $data = is_array($jsonData) && isset($jsonData[0]) ? $jsonData[0] : null;

                if ($data) {
                    
                    $currencyCode = 'N/A';
                    $currencyName = 'N/A';
                    if (!empty($data['currencies']) && is_array($data['currencies'])) {
                        $currencyCode = (string) array_key_first($data['currencies']);
                        $currencyName = $data['currencies'][$currencyCode]['name'] ?? 'N/A';
                    }

                    
                    $languages = ['N/A'];
                    if (!empty($data['languages']) && is_array($data['languages'])) {
                        $languages = array_values($data['languages']);
                    }

                    return [
                        'success'    => true,
                        'nama_resmi' => $data['name']['official'] ?? $data['name']['common'] ?? 'N/A',
                        'wilayah'    => ($data['region'] ?? 'N/A') . ' (' . ($data['subregion'] ?? 'N/A') . ')',
                        'mata_uang'  => $currencyCode . ' - ' . $currencyName,
                        'bahasa'     => implode(', ', $languages),
                        'bendera'    => $data['flags']['png'] ?? $data['flags']['svg'] ?? 'https://flagcdn.com/w320/' . $code . '.png',
                        'sumber'     => 'Live REST Countries API'
                    ];
                }
            }

            
            return $this->getFallbackData($code);

        } catch (\Exception $e) {
            Log::warning("REST Countries utama bermasalah: " . $e->getMessage() . ". Mengalihkan ke data cadangan.");
           
            return $this->getFallbackData($code);
        }
    }

    
    private function getFallbackData($code)
    {
        $upperCode = strtoupper($code);
        
       
        $database = [
            'ID' => ['name' => 'Republic of Indonesia', 'region' => 'Asia (South-Eastern Asia)', 'currency' => 'IDR - Indonesian rupiah', 'lang' => 'Indonesian'],
            'SG' => ['name' => 'Republic of Singapore', 'region' => 'Asia (South-Eastern Asia)', 'currency' => 'SGD - Singapore dollar', 'lang' => 'English, Malay, Mandarin, Tamil'],
            'US' => ['name' => 'United States of America', 'region' => 'Americas (North America)', 'currency' => 'USD - United States dollar', 'lang' => 'English'],
            'CN' => ['name' => 'People\'s Republic of China', 'region' => 'Asia (Eastern Asia)', 'currency' => 'CNY - Chinese yuan', 'lang' => 'Mandarin'],
        ];

        if (array_key_exists($upperCode, $database)) {
            return [
                'success'    => true,
                'nama_resmi' => $database[$upperCode]['name'],
                'wilayah'    => $database[$upperCode]['region'],
                'mata_uang'  => $database[$upperCode]['currency'],
                'bahasa'     => $database[$upperCode]['lang'],
                'bendera'    => "https://flagcdn.com/w320/" . strtolower($code) . ".png",
                'sumber'     => 'Internal Backup & Flagcdn'
            ];
        }

        
        return [
            'success'    => true,
            'nama_resmi' => 'Country Territory (' . $upperCode . ')',
            'wilayah'    => 'Global Maritime Region',
            'mata_uang'  => 'USD - United States Dollar',
            'bahasa'     => 'English / Local Language',
            'bendera'    => "https://flagcdn.com/w320/" . strtolower($code) . ".png",
            'sumber'     => 'Smart Safe Fallback Generator'
        ];
    }
}