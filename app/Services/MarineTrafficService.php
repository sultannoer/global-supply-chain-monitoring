<?php

namespace App\Services;

use App\Models\Shipment;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class MarineTrafficService
{
    /**
     * Simulasi Pergerakan Kapal Berbasis Koordinat Pelabuhan World Port Index
     * Menghitung posisi terkini kapal di antara Pelabuhan Asal dan Tujuan.
     */
    public function updateShipmentLocation(Shipment $shipment): bool
    {
        try {
            $now = Carbon::now();
            $departure = Carbon::parse($shipment->departure_date);
            $eta = Carbon::parse($shipment->adaptive_eta);

            // Jika belum berangkat, posisikan kapal di Pelabuhan Asal
            if ($now->lessThanOrEqualTo($departure)) {
                $shipment->update([
                    'current_lat' => $shipment->originPort->latitude,
                    'current_lng' => $shipment->originPort->longitude,
                    'status' => 'ON_VOYAGE'
                ]);
                return true;
            }

            // Jika sudah sampai, posisikan kapal di Pelabuhan Tujuan
            if ($now->greaterThanOrEqualTo($eta)) {
                $shipment->update([
                    'current_lat' => $shipment->destinationPort->latitude,
                    'current_lng' => $shipment->destinationPort->longitude,
                    'status' => 'ARRIVED'
                ]);
                return true;
            }

            // Hitung persentase perjalanan (Interpolasi Linier)
            $totalDuration = $departure->diffInSeconds($eta);
            $elapsedTime = $departure->diffInSeconds($now);

            $fraction = $totalDuration > 0 ? ($elapsedTime / $totalDuration) : 1;

            // Ambil koordinat dari World Port Index Dataset di DB
            $originLat = (float) $shipment->originPort->latitude;
            $originLng = (float) $shipment->originPort->longitude;
            $destLat = (float) $shipment->destinationPort->latitude;
            $destLng = (float) $shipment->destinationPort->longitude;

            // Hitung koordinat posisi kapal di tengah laut
            $currentLat = $originLat + ($destLat - $originLat) * $fraction;
            $currentLng = $originLng + ($destLng - $originLng) * $fraction;

            $shipment->update([
                'current_lat' => round($currentLat, 8),
                'current_lng' => round($currentLng, 8),
                'status' => 'ON_VOYAGE'
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Gagal memperbarui posisi kapal untuk Shipment #{$shipment->tracking_number}: " . $e->getMessage());
            return false;
        }
    }
}