<?php

namespace App\Services;

use App\Models\Shipment;
use App\Models\Port;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MarineTrafficService
{
    /**
     * Simulasi Pergerakan Kapal (Logika Asli Milikmu)
     * Sangat berguna sebagai fallback jika API satelit AIS utama sedang offline.
     */
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
            Log::error("Gagal memperbarui koordinat pelayaran untuk Shipment #{$shipment->tracking_number}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * [FUNGSI BARU] Mengambil koordinat (Lat/Lng) Pelabuhan secara otomatis
     * Menggunakan API publik agar terhindar dari input manual yang rawan salah.
     */
    public function syncPortCoordinates(Port $port): bool
    {
        try {
            // Merakit kata kunci pencarian, contoh: "Port of Tanjung Priok, Indonesia"
            $searchQuery = urlencode("Port of " . $port->name . ", " . ($port->country->name ?? ''));

            // Menggunakan API Nominatim (OpenStreetMap) untuk Geocoding gratis
            $response = Http::withHeaders([
                'User-Agent' => 'LogixChain-SCRM-App/1.0'
            ])->timeout(5)->get("https://nominatim.openstreetmap.org/search?q={$searchQuery}&format=json&limit=1");

            if ($response->successful() && !empty($response->json())) {
                $data = $response->json()[0];

                $port->update([
                    'latitude' => $data['lat'],
                    'longitude' => $data['lon']
                ]);

                return true;
            }
            
            return false;
        } catch (\Exception $e) {
            Log::error("Gagal menarik koordinat dari API untuk pelabuhan {$port->name}: " . $e->getMessage());
            return false;
        }
    }
}