<?php

namespace App\Services;

use App\Models\Shipment;
use Carbon\Carbon;

class RouteWeatherEtaService
{
    /**
     * Rebuild the adaptive ETA from the baseline ETA on every evaluation.
     * This prevents a weather delay from accumulating on each dashboard load.
     */
    public function synchronize(Shipment $shipment, array $stormZones): array
    {
        $departure = $shipment->departure_date
            ? Carbon::parse($shipment->departure_date)->startOfDay()
            : Carbon::parse($shipment->created_at ?? now())->startOfDay();
        $baseline = $shipment->baseline_eta
            ? Carbon::parse($shipment->baseline_eta)->startOfDay()
            : $departure->copy()->addDays(3);

        $durationHours = max(24, $departure->diffInHours($baseline));
        $impact = $this->routeImpact(
            (float) $shipment->originPort?->latitude,
            (float) $shipment->originPort?->longitude,
            (float) $shipment->destinationPort?->latitude,
            (float) $shipment->destinationPort?->longitude,
            $stormZones,
        );

        $delayHours = $impact
            ? (int) ceil($durationHours * ($impact['risk'] === 'High' ? 0.25 : 0.10))
            : 0;
        $adaptive = $baseline->copy()->addHours($delayHours);

        $shipment->fill([
            'departure_date' => $departure->toDateString(),
            'baseline_eta' => $baseline->toDateString(),
            'adaptive_eta' => $adaptive->toDateString(),
            'weather_delay_hours' => $delayHours,
            'route_weather_status' => $impact['risk'] ?? 'Clear',
            'route_storm_name' => $impact['name'] ?? null,
        ]);
        if ($shipment->isDirty()) {
            $shipment->save();
        }

        return [
            'delay_hours' => $delayHours,
            'risk' => $impact['risk'] ?? 'Clear',
            'storm_name' => $impact['name'] ?? null,
            'baseline_eta' => $baseline,
            'adaptive_eta' => $adaptive,
        ];
    }

    private function routeImpact(float $startLat, float $startLng, float $endLat, float $endLng, array $stormZones): ?array
    {
        if (($startLat === 0.0 && $startLng === 0.0) || ($endLat === 0.0 && $endLng === 0.0)) {
            return null;
        }

        $impact = null;
        foreach ($stormZones as $storm) {
            if (! $this->intersects($startLat, $startLng, $endLat, $endLng, $storm)) {
                continue;
            }

            $risk = ucfirst(strtolower((string) ($storm['risk'] ?? 'Medium')));
            if ($impact === null || ($risk === 'High' && $impact['risk'] !== 'High')) {
                $impact = ['risk' => $risk, 'name' => $storm['name'] ?? 'zona cuaca aktif'];
            }
        }

        return $impact;
    }

    private function intersects(float $startLat, float $startLng, float $endLat, float $endLng, array $storm): bool
    {
        for ($index = 0; $index <= 24; $index++) {
            $ratio = $index / 24;
            $lat = $startLat + (($endLat - $startLat) * $ratio);
            $lng = $startLng + (($endLng - $startLng) * $ratio);
            if ($this->distanceKm($lat, $lng, (float) $storm['lat'], (float) $storm['lng']) <= (float) $storm['radius_km']) {
                return true;
            }
        }

        return false;
    }

    private function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $latDelta = deg2rad($lat2 - $lat1);
        $lngDelta = deg2rad($lng2 - $lng1);
        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($lngDelta / 2) ** 2;

        return 6371 * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
