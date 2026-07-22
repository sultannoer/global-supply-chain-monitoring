<?php

namespace App\Services;

use App\Models\Country;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class EconomicService
{
    /** Update indicators from World Bank. Five independent requests run concurrently. */
    public function updateCountryEconomicIndicators(Country $country): bool
    {
        $code = strtolower((string) $country->code);
        if ($code === '') {
            return false;
        }

        $cacheKey = 'world-bank:'.$code;
        $indicators = Cache::get($cacheKey);
        $expectedFields = ['gdp', 'inflation_rate', 'population', 'export_volume', 'import_volume'];
        if (! is_array($indicators) || $indicators === [] || array_intersect(array_keys($indicators), $expectedFields) === []) {
            try {
                $baseUrl = "https://api.worldbank.org/v2/country/{$code}/indicator/";
                $responses = Http::pool(fn (Pool $pool) => [
                    'gdp' => $pool->as('gdp')->acceptJson()->timeout(8)->get($baseUrl.'NY.GDP.MKTP.CD?format=json&per_page=10'),
                    'inflation_rate' => $pool->as('inflation_rate')->acceptJson()->timeout(8)->get($baseUrl.'FP.CPI.TOTL.ZG?format=json&per_page=10'),
                    'population' => $pool->as('population')->acceptJson()->timeout(8)->get($baseUrl.'SP.POP.TOTL?format=json&per_page=10'),
                    'export_volume' => $pool->as('export_volume')->acceptJson()->timeout(8)->get($baseUrl.'NE.EXP.GNFS.CD?format=json&per_page=10'),
                    'import_volume' => $pool->as('import_volume')->acceptJson()->timeout(8)->get($baseUrl.'NE.IMP.GNFS.CD?format=json&per_page=10'),
                ]);

                $indicators = [];
                foreach ($responses as $field => $response) {
                    $value = $response->successful()
                        ? collect($response->json()[1] ?? [])->pluck('value')->first(fn ($item) => is_numeric($item))
                        : null;
                    if (is_numeric($value)) {
                        $indicators[$field] = $field === 'inflation_rate' ? round((float) $value, 2) : $value;
                    }
                }
                if ($indicators !== []) {
                    Cache::put($cacheKey, $indicators, now()->addDay());
                }
            } catch (\Throwable $exception) {
                Log::warning('World Bank request failed.', ['country' => $code, 'message' => $exception->getMessage()]);
                $indicators = [];
            }
        }

        if ($indicators === []) {
            return false;
        }

        $country->update($indicators);

        return true;
    }
}
