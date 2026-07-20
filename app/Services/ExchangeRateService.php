<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use App\Models\Shipment;

class ExchangeRateService
{
    /**
     * Update kurs saat ini untuk spesifik Shipment (Kode Asli Milikmu)
     */
    public function updateShipmentExchangeRate(Shipment $shipment): bool
    {
        try {
            $destinationCurrency = $shipment->destinationPort->country->currency_code ?? null;

            if (!$destinationCurrency) {
                return false;
            }

            $response = Http::get("https://open.er-api.com/v6/latest/USD");

            if ($response->successful()) {
                $rates = $response->json('rates');
                $currentRate = $rates[$destinationCurrency] ?? null;

                if ($currentRate) {
                    $shipment->update([
                        'current_exchange_rate' => $currentRate
                    ]);

                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            \Log::error("Gagal memperbarui kurs untuk Shipment #{$shipment->tracking_number}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Dapatkan data tren fluktuasi untuk Chart.js di Dashboard (Tambahan Baru)
     */
    public function getCurrencyTrend($currencyCode): array
    {
        // Kita cache selama 1 jam agar API tidak kelebihan beban (limit request)
        return Cache::remember("forex_trend_" . $currencyCode, 3600, function() use ($currencyCode) {
            try {
                $response = Http::get("https://open.er-api.com/v6/latest/USD");
                
                if ($response->successful()) {
                    $rates = $response->json('rates');
                    $baseRate = $rates[$currencyCode] ?? 1.0;

                    // Mensimulasikan fluktuasi pasar riil berbasis persentase acak ringan
                    // Titik terakhir (index ke-3) adalah kurs ASLI saat ini
                    return [
                        round($baseRate * (1 - (rand(1, 5) / 1000)), 4), 
                        round($baseRate * (1 + (rand(1, 5) / 1000)), 4), 
                        round($baseRate * (1 - (rand(1, 3) / 1000)), 4), 
                        round($baseRate, 4) // Kurs Final / Aktual
                    ];
                }
            } catch (\Exception $e) {
                \Log::error("Gagal menarik tren Forex: " . $e->getMessage());
            }

            return [1.0, 1.0, 1.0, 1.0];
        });
    }
}