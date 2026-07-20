<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Port;

class WeatherService
{
    /**
     * Update cuaca statis di Pelabuhan (Kode Asli Milikmu)
     */
    public function updatePortWeather(Port $port): bool
    {
        try {
            $response = Http::get('https://api.open-meteo.com/v1/forecast', [
                'latitude' => $port->latitude,
                'longitude' => $port->longitude,
                'current' => 'temperature_2m,rain,wind_speed_10m',
                'timezone' => 'auto'
            ]);

            if ($response->successful()) {
                $data = $response->json('current');

                $temp = $data['temperature_2m'] ?? 0;
                $rain = $data['rain'] ?? 0;
                $windSpeed = $data['wind_speed_10m'] ?? 0; // dalam km/jam

                $stormRisk = 'Low';
                if ($windSpeed > 50 || $rain > 15) {
                    $stormRisk = 'High';
                } elseif ($windSpeed > 30 || $rain > 5) {
                    $stormRisk = 'Medium';
                }

                $port->update([
                    'temp' => $temp,
                    'rain' => $rain,
                    'wind_speed' => $windSpeed,
                    'storm_risk_status' => $stormRisk,
                ]);

                return true;
            }
            return false;
        } catch (\Exception $e) {
            \Log::error("Gagal menarik data cuaca untuk Port {$port->name}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Cek cuaca dinamis di tengah laut untuk koordinat Kapal (Tambahan Baru)
     */
    public function getMarineWeather($lat, $lng): array
    {
        try {
            $response = Http::get('https://api.open-meteo.com/v1/forecast', [
                'latitude' => $lat,
                'longitude' => $lng,
                'current' => 'temperature_2m,rain,wind_speed_10m',
                'timezone' => 'auto'
            ]);

            if ($response->successful()) {
                $data = $response->json('current');
                
                $windSpeed = $data['wind_speed_10m'] ?? 0;
                $rain = $data['rain'] ?? 0;

                // Logika yang sama dengan milikmu agar konsisten
                $isStormRisk = false;
                if ($windSpeed > 30 || $rain > 5) {
                    $isStormRisk = true; // Anggap cuaca buruk jika Medium/High
                }

                return [
                    'temp' => $data['temperature_2m'] ?? 25,
                    'wind' => $windSpeed,
                    'rain' => $rain,
                    'is_storm_risk' => $isStormRisk
                ];
            }
        } catch (\Exception $e) {
            // Heningkan error agar peta tidak mati jika API down
        }

        // Kembalikan data default jika API gagal
        return ['temp' => 26, 'wind' => 10, 'rain' => 0, 'is_storm_risk' => false];
    }
}