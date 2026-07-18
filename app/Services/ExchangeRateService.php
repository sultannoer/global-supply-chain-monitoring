<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExchangeRateService
{
    
    protected $baseUrl = 'https://open.er-api.com/v6/latest/USD';

    
    public function getRateAgainstUsd($targetCurrency)
    {
        try {
            $response = Http::timeout(10)->get($this->baseUrl);

            if ($response->successful()) {
                $data = $response->json();
                $rates = $data['rates'] ?? [];
                
                
                $currency = strtoupper(trim($targetCurrency));

                if (array_key_exists($currency, $rates)) {
                    $rateValue = $rates[$currency];

                    return [
                        'success'     => true,
                        'base'        => 'USD',
                        'target'      => $currency,
                        'nilai_kurs'  => '1 USD = ' . number_format($rateValue, 2, ',', '.') . ' ' . $currency,
                        'updated_at'  => $data['time_last_update_utc'] ?? 'Baru saja'
                    ];
                }

                return [
                    'success' => false,
                    'message' => 'Kode mata uang (' . $currency . ') tidak ditemukan di data kurs.'
                ];
            }

            return ['success' => false, 'message' => 'Gagal mengambil data dari Exchange Rate server.'];

        } catch (\Exception $e) {
            Log::error('Exchange Rate Service Error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan pada sistem nilai tukar uang.'];
        }
    }
}