<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Country;

class CountryService
{
    /**
     * Sinkronisasi data master negara menggunakan REST Countries API
     */
    public function fetchAndSyncCountry(string $countryCode): bool
    {
        try {
            $code = strtoupper($countryCode);

            // Menambahkan timeout 5 detik untuk keamanan performa aplikasi
            $response = Http::timeout(5)->get("https://restcountries.com/v3.1/alpha/{$code}");

            if ($response->successful() && !empty($response->json())) {
                $data = $response->json()[0];

                // 1. Ekstraksi Nama & Region
                $name = $data['name']['common'] ?? 'Unknown';
                $region = $data['region'] ?? 'Unknown';
                
                // 2. Ekstraksi Mata Uang (Currency Code)
                $currencies = $data['currencies'] ?? [];
                $currencyCode = !empty($currencies) ? array_key_first($currencies) : 'USD';
                
                // 3. Ekstraksi Bahasa Utama (Language)
                $languages = $data['languages'] ?? [];
                $language = !empty($languages) ? array_values($languages)[0] : 'Unknown';

                // 4. Update jika kode sudah ada, Create jika belum ada
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