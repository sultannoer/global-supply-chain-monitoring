<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Shipment;

class ExchangeRateService
{
    
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
}