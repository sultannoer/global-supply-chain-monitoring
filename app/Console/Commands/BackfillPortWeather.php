<?php

namespace App\Console\Commands;

use App\Models\Port;
use App\Models\PortWeatherHistory;
use App\Services\RiskAssessmentService;
use App\Services\WeatherService;
use Illuminate\Console\Command;

class BackfillPortWeather extends Command
{
    protected $signature = 'ports:weather-backfill {--limit=0 : Maximum ports; 0 processes all missing snapshots}';

    protected $description = 'Fill missing weather history snapshots for individual ports';

    public function handle(WeatherService $weatherService, RiskAssessmentService $riskAssessmentService): int
    {
        set_time_limit(0);
        ini_set('max_execution_time', '0');

        $limit = max(0, (int) $this->option('limit'));
        $query = Port::query()->whereDoesntHave('weatherHistories')->orderBy('id');
        if ($limit > 0) {
            $query->limit($limit);
        }

        $ports = $query->get();
        $this->info("Backfilling weather for {$ports->count()} ports without a snapshot...");
        $stored = 0;
        $bar = $this->output->createProgressBar($ports->count());

        foreach ($ports as $port) {
            if ($weatherService->updatePortWeather($port)) {
                $port->refresh();
                $riskAssessmentService->calculatePortRisk($port);
                PortWeatherHistory::create([
                    'port_id' => $port->id,
                    'temp' => $port->temp,
                    'rain' => $port->rain,
                    'wind_speed' => $port->wind_speed,
                    'storm_risk_status' => $port->storm_risk_status,
                    'risk_score' => $port->risk_score,
                    'recorded_at' => now(),
                ]);
                $stored++;
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Stored {$stored} port weather snapshots.");

        return self::SUCCESS;
    }
}
