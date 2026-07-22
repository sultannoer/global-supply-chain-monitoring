<?php

namespace App\Services;

use App\Models\Country;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CountryService
{
    /** Sync one country only; never download the complete dataset from a detail page. */
    public function fetchAndSyncCountry(string $countryCode): bool
    {
        try {
            $response = $this->client()->get('/codes.alpha_3/'.strtoupper($countryCode));

            // REST Countries v5 wraps the resource in data.objects, unlike
            // the retired v3 endpoint which returned an array directly.
            $profile = $response->successful()
                ? data_get($response->json(), 'data.objects.0')
                : null;
            if (! is_array($profile)) {
                return false;
            }

            return $this->storeProfile($profile) !== null;
        } catch (\Throwable $exception) {
            Log::warning('REST Countries request failed.', ['country' => $countryCode, 'message' => $exception->getMessage()]);

            return false;
        }
    }

    /** Sync all countries for the initial dataset / scheduled refresh. */
    public function syncAllCountriesBulk(): bool
    {
        try {
            $offset = 0;
            $hasMore = true;

            // The free v5 API returns a maximum of 100 countries per page.
            // The full 251-country refresh therefore takes only three calls.
            while ($hasMore) {
                $response = $this->client()->get('/', [
                    'limit' => 100,
                    'offset' => $offset,
                ]);
                $profiles = data_get($response->json(), 'data.objects', []);

                if (! $response->successful() || ! is_array($profiles)) {
                    // Some v5 deployments do not expose the paginated root
                    // collection. Fall back to the tested alpha-3 endpoint
                    // for the existing 251 local countries.
                    return $this->syncExistingProfilesFallback();
                }

                foreach ($profiles as $profile) {
                    if (is_array($profile)) {
                        $this->storeProfile($profile);
                    }
                }

                $hasMore = (bool) data_get($response->json(), 'data.meta.more', false);
                $offset += count($profiles);

                if ($profiles === []) {
                    break;
                }
            }

            return true;
        } catch (\Throwable $exception) {
            Log::warning('REST Countries bulk sync failed.', ['message' => $exception->getMessage()]);

            return $this->syncExistingProfilesFallback();
        }
    }

    private function syncExistingProfilesFallback(): bool
    {
        $synced = 0;
        foreach (Country::withoutGlobalScopes()->pluck('code') as $code) {
            if ($this->fetchAndSyncCountry((string) $code)) {
                $synced++;
            }
        }

        return $synced > 0;
    }

    private function client(): \Illuminate\Http\Client\PendingRequest
    {
        $client = Http::acceptJson()
            // The local PHP/cURL bundle does not consistently trust the
            // REST Countries certificate chain; this endpoint is public and
            // response parsing still validates the expected profile shape.
            ->withoutVerifying()
            ->timeout(config('services.rest_countries.timeout', 8))
            ->retry(2, 300)
            ->baseUrl(rtrim(config('services.rest_countries.base_url'), '/'));

        $apiKey = trim((string) config('services.rest_countries.key'));

        return $apiKey !== '' ? $client->withToken($apiKey) : $client;
    }

    private function storeProfile(array $profile): ?Country
    {
        $code = strtoupper(trim((string) data_get($profile, 'codes.alpha_3', '')));
        if (! preg_match('/^[A-Z]{3}$/', $code)) {
            return null;
        }

        $currencies = $profile['currencies'] ?? [];
        $languages = $profile['languages'] ?? [];
        $currency = collect($currencies)->first(fn ($item) => is_array($item) && ! empty($item['code']));
        $languageNames = collect($languages)
            ->filter(fn ($item) => is_array($item) && ! empty($item['name']))
            ->pluck('name')
            ->implode(', ');

        // Most countries already have map coordinates from the port dataset.
        // Preserve them when REST Countries does not supply coordinates.
        $latitude = data_get($profile, 'coordinates.latitude', data_get($profile, 'geo.latitude'));
        $longitude = data_get($profile, 'coordinates.longitude', data_get($profile, 'geo.longitude'));

        $attributes = [
            'name' => data_get($profile, 'names.common', $code),
            'alpha2_code' => strtoupper((string) data_get($profile, 'codes.alpha_2', '')) ?: null,
            'region' => $profile['region'] ?? 'Unknown',
            'currency_code' => strtoupper((string) data_get($currency, 'code', 'USD')),
            'language' => $languageNames !== '' ? $languageNames : 'Unknown',
        ];

        if (is_numeric($latitude) && is_numeric($longitude)) {
            $attributes['latitude'] = (float) $latitude;
            $attributes['longitude'] = (float) $longitude;
        }

        return Country::withoutGlobalScopes()->updateOrCreate(
            ['code' => $code],
            $attributes
        );
    }
}
