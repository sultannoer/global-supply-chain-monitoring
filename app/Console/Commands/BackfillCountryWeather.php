<?php

namespace App\Console\Commands;

use App\Models\Country;
use App\Models\CountryWeatherHistory;
use App\Services\RiskAssessmentService;
use App\Services\WeatherService;
use Illuminate\Console\Command;

class BackfillCountryWeather extends Command
{
    protected $signature = 'weather:backfill-countries';

    protected $description = 'Fill missing country weather snapshots from country coordinates';

    public function handle(WeatherService $weatherService, RiskAssessmentService $riskAssessmentService): int
    {
        // This is a CLI batch job, not a browser request. Let it finish its
        // bounded API batch instead of being killed by PHP's 60-second limit.
        set_time_limit(0);
        ini_set('max_execution_time', '0');

        $countries = Country::query()->whereDoesntHave('weatherHistories')->orderBy('code')->get();
        $this->info("Backfilling weather for {$countries->count()} countries without a snapshot...");
        $bar = $this->output->createProgressBar($countries->count());

        foreach ($countries as $country) {
            $weather = $weatherService->getCountryWeather($country);
            if ($weather !== null) {
                CountryWeatherHistory::create(array_merge($weather, [
                    'country_code' => $country->code,
                    'recorded_at' => now(),
                ]));
                $riskAssessmentService->calculateCountryRisk($country->fresh());
            }
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info('Country weather backfill selesai. REST Countries dan World Bank tidak dipanggil.');

        return self::SUCCESS;
    }
}
