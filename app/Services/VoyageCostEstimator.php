<?php

namespace App\Services;

/**
 * Deterministic voyage estimate. Values are explicit assumptions, not random
 * data or an external quote; the UI and backend use the same model.
 */
class VoyageCostEstimator
{
    public function config(): array
    {
        return [
            'sea_route_factor' => 1.15,
            'fuel_price_usd_per_ton' => 650,
            'insurance_base_rate' => 0.0035,
            'insurance_minimum_usd' => 500,
            'port_fee_rate' => 0.01,
            'risk_multipliers' => ['Low' => 1.0, 'Medium' => 1.2, 'High' => 1.5],
        ];
    }

    public function estimate(?array $origin, ?array $destination, float $cargoWeight, float $cargoValue, string $vesselName = ''): array
    {
        $config = $this->config();
        $profile = $this->vesselProfile($vesselName);

        if (!$origin || !$destination || !isset($origin['lat'], $origin['lng'], $destination['lat'], $destination['lng'])) {
            return ['ready' => false, 'profile' => $profile, 'config' => $config];
        }

        $straightLineKm = $this->distanceKm((float) $origin['lat'], (float) $origin['lng'], (float) $destination['lat'], (float) $destination['lng']);
        $routeKm = $straightLineKm * $config['sea_route_factor'];
        $nauticalMiles = $routeKm * 0.539957;
        $transitDays = max(0.5, $nauticalMiles / $profile['speed_knots'] / 24);
        $fuelTons = $transitDays * $profile['fuel_tons_per_day'];
        $fuelCost = $fuelTons * $config['fuel_price_usd_per_ton'];

        $riskMultiplier = max(
            $config['risk_multipliers'][$origin['storm_risk_status'] ?? 'Low'] ?? 1.0,
            $config['risk_multipliers'][$destination['storm_risk_status'] ?? 'Low'] ?? 1.0,
        );
        $insuranceRate = $config['insurance_base_rate'] * $riskMultiplier;
        $insuranceCost = $cargoValue > 0
            ? max($cargoValue * $insuranceRate, $config['insurance_minimum_usd'])
            : 0.0;
        $portFees = $cargoValue * $config['port_fee_rate'];
        $grossRevenue = $cargoWeight * $profile['freight_rate_per_ton'];
        $netMargin = $grossRevenue - $fuelCost - $insuranceCost - $portFees;

        return [
            'ready' => true,
            'distance_km' => $routeKm,
            'transit_days' => $transitDays,
            'fuel_tons' => $fuelTons,
            'fuel_cost' => $fuelCost,
            'insurance_rate' => $insuranceRate,
            'insurance_cost' => $insuranceCost,
            'port_fees' => $portFees,
            'gross_revenue' => $grossRevenue,
            'net_margin' => $netMargin,
            'risk_multiplier' => $riskMultiplier,
            'profile' => $profile,
            'config' => $config,
        ];
    }

    public function vesselProfile(string $vesselName): array
    {
        $name = strtoupper($vesselName);

        if (str_contains($name, 'EXPRESS') || str_contains($name, 'BULK')) {
            return ['class' => 'Bulk Carrier', 'capacity_tons' => 80000, 'speed_knots' => 14, 'fuel_tons_per_day' => 45, 'freight_rate_per_ton' => 35];
        }
        if (str_contains($name, 'MARU') || str_contains($name, 'GENERAL')) {
            return ['class' => 'General Cargo', 'capacity_tons' => 20000, 'speed_knots' => 16, 'fuel_tons_per_day' => 38, 'freight_rate_per_ton' => 38];
        }

        return ['class' => 'Container Vessel', 'capacity_tons' => 60000, 'speed_knots' => 18, 'fuel_tons_per_day' => 60, 'freight_rate_per_ton' => 45];
    }

    private function distanceKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371.0;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
