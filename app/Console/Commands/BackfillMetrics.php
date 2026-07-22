<?php

namespace App\Console\Commands;

use App\Models\Country;
use App\Models\CountryEconomicHistory;
use App\Models\CountryWeatherHistory;
use App\Services\EconomicService;
use App\Services\ExchangeRateService;
use App\Services\RiskAssessmentService;
use App\Services\WeatherService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class BackfillMetrics extends Command
{
    protected $signature = 'metrics:backfill {--limit=0 : Maximum countries; 0 processes all}';

    protected $description = 'Backfill economy, country weather, currency snapshots, and risk scores without REST Countries requests';

    public function handle(
        EconomicService $economicService,
        WeatherService $weatherService,
        ExchangeRateService $exchangeRates,
        RiskAssessmentService $riskAssessmentService,
    ): int {
        // Backfill is intentionally executed from the CLI and may contain
        // hundreds of short external API calls.
        set_time_limit(0);
        ini_set('max_execution_time', '0');

        $limit = max(0, (int) $this->option('limit'));
        $query = Country::query()->orderBy('code');
        if ($limit > 0) {
            $query->limit($limit);
        }
        $countries = $query->get();

        $this->info("Backfilling {$countries->count()} countries (REST Countries is not called)...");
        $currencyCodes = $countries->pluck('currency_code')->filter()->unique()->values()->all();
        $this->info('Saving currency snapshots...');
        $exchangeRates->snapshotRatesForCurrencies($currencyCodes);

        $bar = $this->output->createProgressBar($countries->count());
        foreach ($countries as $country) {
            // Force a fresh World Bank attempt for missing/stale indicators.
            Cache::forget('world-bank:'.strtolower((string) $country->code));
            $economicService->updateCountryEconomicIndicators($country);
            $country->refresh();
            CountryEconomicHistory::create([
                'country_code' => $country->code,
                'gdp' => $country->gdp,
                'inflation_rate' => $country->inflation_rate,
                'recorded_at' => now(),
            ]);

            if (! CountryWeatherHistory::query()->where('country_code', $country->code)->exists()) {
                $weather = $weatherService->getCountryWeather($country);
                if ($weather !== null) {
                    CountryWeatherHistory::create(array_merge($weather, [
                        'country_code' => $country->code,
                        'recorded_at' => now(),
                    ]));
                }
            }

            $riskAssessmentService->calculateCountryRisk($country->fresh());
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();
        $this->info('Backfill selesai. Tidak ada request REST Countries yang dilakukan.');

        return self::SUCCESS;
    }
}
