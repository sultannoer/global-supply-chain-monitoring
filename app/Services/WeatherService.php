<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Models\Port;
use App\Models\Country;

class WeatherService
{
    /**
     * Update cuaca statis di Pelabuhan (Kode Asli Milikmu)
     */
    public function updatePortWeather(Port $port): bool
    {
        try {
            $response = Http::acceptJson()->timeout(8)->retry(2, 300)->get('https://api.open-meteo.com/v1/forecast', [
                'latitude' => $port->latitude,
                'longitude' => $port->longitude,
                'current' => 'temperature_2m,rain,wind_speed_10m',
                'timezone' => 'auto'
            ]);

            if ($response->successful()) {
                $data = $response->json('current');

                $temp = $data['temperature_2m'] ?? null;
                $rain = $data['rain'] ?? null;
                $windSpeed = $data['wind_speed_10m'] ?? null; // dalam km/jam

                if (! is_numeric($temp) || ! is_numeric($rain) || ! is_numeric($windSpeed)) {
                    return false;
                }

                $stormRisk = 'Low';
                if ((float) $windSpeed > 50 || (float) $rain > 15) {
                    $stormRisk = 'High';
                } elseif ((float) $windSpeed > 30 || (float) $rain > 5) {
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

    /** Use a country's stored coordinates when no real port is available. */
    public function getCountryWeather(Country $country): ?array
    {
        if (! is_numeric($country->latitude) || ! is_numeric($country->longitude)) {
            return null;
        }

        try {
            $response = Http::acceptJson()->timeout(8)->retry(2, 300)->get('https://api.open-meteo.com/v1/forecast', [
                'latitude' => $country->latitude,
                'longitude' => $country->longitude,
                'current' => 'temperature_2m,rain,wind_speed_10m',
                'timezone' => 'auto',
            ]);
            $data = $response->successful() ? $response->json('current', []) : [];
            $temp = $data['temperature_2m'] ?? null;
            $rain = $data['rain'] ?? null;
            $wind = $data['wind_speed_10m'] ?? null;
            if (! is_numeric($temp) || ! is_numeric($rain) || ! is_numeric($wind)) {
                return null;
            }

            $storm = ((float) $wind > 50 || (float) $rain > 15) ? 'High' : (((float) $wind > 30 || (float) $rain > 5) ? 'Medium' : 'Low');
            $risk = (($storm === 'High') ? 40 : (($storm === 'Medium') ? 20 : 5))
                + (((float) $wind > 50) ? 30 : (((float) $wind > 30) ? 15 : 5))
                + (((float) $rain > 15) ? 30 : (((float) $rain > 5) ? 15 : 5));

            return ['temp' => (float) $temp, 'rain' => (float) $rain, 'wind_speed' => (float) $wind, 'storm_risk_status' => $storm, 'risk_score' => min(100, $risk)];
        } catch (\Throwable $exception) {
            \Log::warning('Country weather request failed.', ['country' => $country->code, 'message' => $exception->getMessage()]);
            return null;
        }
    }

    /**
     * Cek cuaca dinamis di tengah laut untuk koordinat Kapal (Tambahan Baru)
     */
    public function getMarineWeather($lat, $lng): array
    {
        try {
            $response = Http::acceptJson()->timeout(8)->retry(2, 300)->get('https://api.open-meteo.com/v1/forecast', [
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
        return ['temp' => null, 'wind' => null, 'rain' => null, 'is_storm_risk' => false];
    }
}
