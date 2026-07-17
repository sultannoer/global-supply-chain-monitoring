<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WeatherService
{
    // URL resmi Open-Meteo (Gratis & Terbuka publik)
    protected $baseUrl = 'https://api.open-meteo.com/v1/forecast';

    /**
     * Mengambil data cuaca berdasarkan koordinat Lintang (Lat) dan Bujur (Lon)
     */
    public function getWeatherByCoordinates($lat, $lon)
    {
        try {
            // Kita langsung tembak URL tanpa perlu mengirimkan API Key/AppID
            $response = Http::get($this->baseUrl, [
                'latitude'  => $lat,
                'longitude' => $lon,
                'current'   => 'temperature_2m,relative_humidity_2m,rain,wind_speed_10m',
                'timezone'  => 'auto'
            ]);

            // Jika server Open-Meteo sukses merespons
            if ($response->successful()) {
                $current = $response->json()['current'];
                $windSpeed = $current['wind_speed_10m'] ?? 0; // Kecepatan angin (km/h)
                $rain = $current['rain'] ?? 0; // Curah hujan (mm)

                return [
                    'success'          => true,
                    'suhu'             => $current['temperature_2m'] . '°C',
                    'kondisi'          => $rain > 0 ? 'Hujan' : 'Cerah / Berawan',
                    'kecepatan_angin'  => $windSpeed . ' km/h',
                    'curah_hujan'      => $rain . ' mm',
                    'risiko_badai'     => $this->calculateStormRisk($windSpeed, $rain),
                    'status_keamanan'  => $this->checkSafetyStatus($windSpeed)
                ];
            }

            return ['success' => false, 'message' => 'Gagal mengambil data dari Open-Meteo.'];

        } catch (\Exception $e) {
            Log::error('Open-Meteo Service Error: ' . $e->getMessage());
            return ['success' => false, 'message' => 'Terjadi kesalahan sistem cuaca.'];
        }
    }

    /**
     * Hitung Risiko Badai (Kebutuhan Poin 1 di daftar API kamu)
     */
    private function calculateStormRisk($windSpeed, $rain)
    {
        // Parameter maritim: Angin > 50 km/h ATAU curah hujan > 10 mm
        if ($windSpeed > 50 || $rain > 10) {
            return 'Tinggi (Risiko Badai/Siklon)';
        } elseif ($windSpeed > 30 || $rain > 5) {
            return 'Sedang';
        }
        return 'Rendah (Aman)';
    }

    /**
     * Tentukan status keamanan pelabuhan
     */
    private function checkSafetyStatus($windSpeed)
    {
        if ($windSpeed > 50) {
            return 'Bahaya (Aktivitas Bongkar Muat Dihentikan)';
        } elseif ($windSpeed > 30) {
            return 'Waspada';
        }
        return 'Aman';
    }
}