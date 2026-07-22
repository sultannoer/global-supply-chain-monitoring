<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Port;
use App\Services\EconomicService;
use App\Services\ExchangeRateService;
use App\Services\CountryService;
use App\Services\CountryFlagService;
use App\Models\Watchlist;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Response;

class CountryDetailController extends Controller
{
    /** Keep country detail on the same shared data path as map, risk, and trends. */
    public function show(
        string $code,
        EconomicService $economicService,
        ExchangeRateService $exchangeRates,
        CountryService $countryService,
    ): Response {
        $code = strtoupper($code);
        $country = Country::find($code);

        abort_unless($country, 404);

        // REST Countries is queried at most once per country per 30 days and
        // only when the local profile is missing its alpha-2/region metadata.
        if (empty($country->alpha2_code) || empty($country->region) || empty($country->language)) {
            $attemptKey = 'rest-country-detail-attempted-'.$country->code;
            if (! Cache::has($attemptKey)) {
                $countryService->fetchAndSyncCountry($country->code);
                Cache::put($attemptKey, true, now()->addDays(30));
                $country->refresh();
            }
        }

        if (
            $country->gdp === null
            || $country->inflation_rate === null
            || $country->population === null
            || $country->export_volume === null
            || $country->import_volume === null
        ) {
            $economicService->updateCountryEconomicIndicators($country);
            $country->refresh();
        }

        $forexTimeline = $exchangeRates->getCurrencyTrend($country->currency_code);
        $apiData = [
            'region' => $country->region,
            'flag_code' => $country->alpha2_code ?: CountryFlagService::iso2($country->code),
            'language' => $country->language,
            'currency' => $country->currency_code,
            'rate_to_usd' => $exchangeRates->getRate($country->currency_code),
            'gdp' => $country->gdp,
            'inflation' => $country->inflation_rate,
            'population' => $country->population,
            'export' => $country->export_volume,
            'import' => $country->import_volume,
            'forex_data' => $forexTimeline['values'] ?? [],
            'forex_labels' => $forexTimeline['labels'] ?? [],
        ];
        $relatedPorts = Port::query()->where('country_code', $country->code)->orderBy('name')->get();
        $isWatched = Watchlist::query()->where('country_code', $country->code)->exists();

        return response()->view('ports.countries', compact('country', 'apiData', 'relatedPorts', 'isWatched'));
    }
}
