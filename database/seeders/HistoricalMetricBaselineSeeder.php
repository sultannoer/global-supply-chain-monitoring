<?php

namespace Database\Seeders;

use App\Models\Country;
use App\Models\CountryEconomicHistory;
use App\Models\Port;
use App\Models\PortWeatherHistory;
use Illuminate\Database\Seeder;

class HistoricalMetricBaselineSeeder extends Seeder
{
    /**
     * Saves the currently synchronized API values as the first real baseline.
     * It intentionally skips missing values; no simulated point is inserted.
     */
    public function run(): void
    {
        $recordedAt = now()->startOfMinute();

        Country::query()
            ->where(fn ($query) => $query->whereNotNull('gdp')->orWhereNotNull('inflation_rate'))
            ->select(['code', 'gdp', 'inflation_rate'])
            ->each(function (Country $country) use ($recordedAt) {
                CountryEconomicHistory::query()->firstOrCreate(
                    ['country_code' => $country->code, 'recorded_at' => $recordedAt],
                    ['gdp' => $country->gdp, 'inflation_rate' => $country->inflation_rate]
                );
            });

        Port::query()
            ->where(fn ($query) => $query->whereNotNull('temp')->orWhereNotNull('wind_speed')->orWhereNotNull('rain'))
            ->select(['id', 'temp', 'rain', 'wind_speed', 'storm_risk_status', 'risk_score'])
            ->each(function (Port $port) use ($recordedAt) {
                PortWeatherHistory::query()->firstOrCreate(
                    ['port_id' => $port->id, 'recorded_at' => $recordedAt],
                    [
                        'temp' => $port->temp,
                        'rain' => $port->rain,
                        'wind_speed' => $port->wind_speed,
                        'storm_risk_status' => $port->storm_risk_status,
                        'risk_score' => $port->risk_score,
                    ]
                );
            });
    }
}
