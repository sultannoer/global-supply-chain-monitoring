<?php

namespace App\Services;

use App\Models\Shipment;
use Carbon\Carbon;

class MarineTrafficService
{
   
    public function updateShipmentLocation(Shipment $shipment): bool
    {
        try {
            $now = Carbon::now();
            $departure = Carbon::parse($shipment->departure_date);
            $eta = Carbon::parse($shipment->adaptive_eta);

            
            if ($now->lessThanOrEqualTo($departure)) {
                $shipment->update([
                    'current_lat' => $shipment->originPort->latitude,
                    'current_lng' => $shipment->originPort->longitude,
                    'status' => 'ON_VOYAGE'
                ]);
                return true;
            }

            
            if ($now->greaterThanOrEqualTo($eta)) {
                $shipment->update([
                    'current_lat' => $shipment->destinationPort->latitude,
                    'current_lng' => $shipment->destinationPort->longitude,
                    'status' => 'ARRIVED'
                ]);
                return true;
            }

            
            $totalDuration = $departure->diffInSeconds($eta);
            $elapsedTime = $departure->diffInSeconds($now);

            
            $fraction = $totalDuration > 0 ? ($elapsedTime / $totalDuration) : 1;

            $originLat = (float) $shipment->originPort->latitude;
            $originLng = (float) $shipment->originPort->longitude;
            $destLat = (float) $shipment->destinationPort->latitude;
            $destLng = (float) $shipment->destinationPort->longitude;

            
            $currentLat = $originLat + ($destLat - $originLat) * $fraction;
            $currentLng = $originLng + ($destLng - $originLng) * $fraction;

            
            $shipment->update([
                'current_lat' => round($currentLat, 8),
                'current_lng' => round($currentLng, 8),
                'status' => 'ON_VOYAGE'
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error("Gagal memperbarui koordinat pelayaran untuk Shipment #{$shipment->tracking_number}: " . $e->getMessage());
            return false;
        }
    }
}