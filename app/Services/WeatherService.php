<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Port;

class WeatherService
{
    /**
     * Mengambil data cuaca live dari Open-Meteo berdasarkan koordinat pelabuhan,
     * lalu memperbarui datanya langsung ke database lokal.
     */
    public function updatePortWeather(Port $port): bool
    {
        try {
            // Nembak API Open-Meteo menggunakan HTTP Client bawaan Laravel
            $response = Http::get('https://api.open-meteo.com/v1/forecast', [
                'latitude' => $port->latitude,
                'longitude' => $port->longitude,
                'current' => 'temperature_2m,rain,wind_speed_10m',
                'timezone' => 'auto'
            ]);

            if ($response->successful()) {
                $data = $response->json('current');

                // Ambil variabel penting untuk radar risiko
                $temp = $data['temperature_2m'] ?? 0;
                $rain = $data['rain'] ?? 0;
                $windSpeed = $data['wind_speed_10m'] ?? 0; // dalam km/jam

                // LOGIKA ANALISIS RISIKO BADAI (Sederhana tapi akurat untuk visualisasi)
                // Jika kecepatan angin > 40 km/jam atau curah hujan > 10mm, status otomatis naik
                $stormRisk = 'Low';
                if ($windSpeed > 50 || $rain > 15) {
                    $stormRisk = 'High';
                } elseif ($windSpeed > 30 || $rain > 5) {
                    $stormRisk = 'Medium';
                }

                // Update data pelabuhan di database lokal
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
            // Gagalkan secara diam-diam agar scheduler tidak crash jika API luar down
            \Log::error("Gagal menarik data cuaca untuk Port {$port->name}: " . $e->getMessage());
            return false;
        }
    }
}