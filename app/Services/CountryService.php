<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Country;

class CountryService
{
    
    public function fetchAndSyncCountry(string $countryCode): bool
    {
        try {
            $code = strtoupper($countryCode);

            $response = Http::get("https://restcountries.com/v3.1/alpha/{$code}");

            if ($response->successful() && !empty($response->json())) {
                $data = $response->json()[0];

                
                $name = $data['name']['common'] ?? 'Unknown';
                
                
                $region = $data['region'] ?? 'Unknown';
                
                
                $currencies = $data['currencies'] ?? [];
                $currencyCode = !empty($currencies) ? array_key_first($currencies) : 'USD';
                
                
                $languages = $data['languages'] ?? [];
                $language = !empty($languages) ? array_values($languages)[0] : 'Unknown';

               
                Country::updateOrCreate(
                    ['code' => $code],
                    [
                        'name' => $name,
                        'region' => $region,
                        'currency_code' => $currencyCode,
                        'language' => $language
                    ]
                );

                return true;
            }

            return false;
        } catch (\Exception $e) {
            \Log::error("Gagal sinkronisasi data negara untuk kode {$countryCode}: " . $e->getMessage());
            return false;
        }
    }
}