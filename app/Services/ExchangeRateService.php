<?php

namespace App\Services;

use App\Models\CurrencyRateHistory;
use App\Models\Shipment;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExchangeRateService
{
    public function getLatestRates(): array
    {
        return Cache::remember('exchange-rates:usd', now()->addHour(), function () {
            try {
                $response = Http::acceptJson()->timeout(8)->retry(2, 300)
                    ->get('https://open.er-api.com/v6/latest/USD');

                if (! $response->successful() || ! is_array($response->json('rates'))) {
                    return [];
                }

                return [
                    'base' => $response->json('base_code', 'USD'),
                    'rates' => $response->json('rates'),
                    'updated_at' => $response->json('time_last_update_utc'),
                    'source' => 'open.er-api.com',
                ];
            } catch (\Throwable $exception) {
                Log::warning('ExchangeRate request failed.', ['message' => $exception->getMessage()]);
                return [];
            }
        });
    }

    public function getRate(string $currencyCode): ?float
    {
        $rates = $this->getLatestRates()['rates'] ?? [];
        $rate = $rates[strtoupper($currencyCode)] ?? null;

        return is_numeric($rate) ? (float) $rate : null;
    }

    public function updateShipmentExchangeRate(Shipment $shipment): bool
    {
        $currency = $shipment->destinationPort?->country?->currency_code;
        $rate = $currency ? $this->getRate($currency) : null;

        if ($rate === null) {
            return false;
        }

        $shipment->update(['current_exchange_rate' => $rate]);

        return true;
    }

    /** Store one observed rate per country currency without inventing values for unsupported codes. */
    public function snapshotRatesForCurrencies(array $currencyCodes): int
    {
        $payload = $this->getLatestRates();
        $rates = $payload['rates'] ?? [];
        if (! is_array($rates) || $rates === []) {
            return 0;
        }

        $codes = collect($currencyCodes)
            ->map(fn ($code) => strtoupper(trim((string) $code)))
            ->filter(fn (string $code) => preg_match('/^[A-Z]{3}$/', $code) === 1)
            ->unique()
            ->values();

        foreach ($codes as $code) {
            $rate = $code === 'USD' ? 1.0 : ($rates[$code] ?? null);
            CurrencyRateHistory::create([
                'currency_code' => $code,
                'rate_to_usd' => is_numeric($rate) ? (float) $rate : null,
                'source' => $payload['source'] ?? 'open.er-api.com',
                'recorded_at' => now(),
            ]);
        }

        return $codes->count();
    }

    /** The free source has a current-rate endpoint only; do not fabricate a historical chart. */
    public function getCurrencyTrend(string $currencyCode): array
    {
        $rate = $this->getRate($currencyCode);
        if ($rate === null) {
            return ['labels' => [], 'values' => []];
        }

        return [
            'labels' => [now('UTC')->format('d M H:i').' UTC'],
            'values' => [$rate],
        ];
    }
}
