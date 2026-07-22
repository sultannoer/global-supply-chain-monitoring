<?php

namespace App\Console\Commands;

use App\Models\Country;
use App\Services\ExchangeRateService;
use Illuminate\Console\Command;

class SnapshotCurrencyRates extends Command
{
    protected $signature = 'currency:snapshot';

    protected $description = 'Store the latest real exchange-rate observation for every country currency';

    public function handle(ExchangeRateService $exchangeRates): int
    {
        $currencies = Country::query()
            ->pluck('currency_code')
            ->filter()
            ->map(fn (string $code) => strtoupper($code))
            ->unique()
            ->values()
            ->all();

        $stored = $exchangeRates->snapshotRatesForCurrencies($currencies);
        $this->info("Stored {$stored} currency snapshots.");

        return self::SUCCESS;
    }
}
