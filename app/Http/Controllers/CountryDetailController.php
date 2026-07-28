<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Port;
use App\Services\EconomicService;
use App\Services\ExchangeRateService;
use App\Services\CountryService;
use App\Services\CountryFlagService;
use App\Models\Watchlist;
use App\Models\CountryWeatherHistory;
use App\Models\RiskScore;
use App\Services\RiskAssessmentService;
use App\Services\WeatherService;
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
        WeatherService $weatherService,
        RiskAssessmentService $riskAssessment,
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

        // Detail negara memakai snapshot Open-Meteo yang sama dengan marker
        // dan Historical Trends. Cache singkat mencegah refresh halaman
        // mengirim banyak request identik, namun status badai tetap aktual.
        $weatherWasSynced = false;
        $countryWeather = Cache::remember('country-detail-weather:'.$country->code, now()->addMinutes(15), function () use ($country, $weatherService, &$weatherWasSynced) {
            $weather = $weatherService->getCountryWeather($country);
            if ($weather) {
                CountryWeatherHistory::create([
                    'country_code' => $country->code,
                    'temp' => $weather['temp'],
                    'rain' => $weather['rain'],
                    'wind_speed' => $weather['wind_speed'],
                    'storm_risk_status' => $weather['storm_risk_status'],
                    'risk_score' => $weather['risk_score'],
                    'recorded_at' => now(),
                ]);
                $weatherWasSynced = true;
            }

            return $weather;
        });
        $latestRisk = $weatherWasSynced
            ? $riskAssessment->calculateCountryRisk($country)
            : RiskScore::query()->where('country_code', $country->code)->latest('calculated_at')->first();
        $stormZoneExposure = $riskAssessment->stormExposureForCoordinates(
            (float) $country->latitude,
            (float) $country->longitude,
        );

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
            'weather' => $countryWeather,
            'storm_zone' => $stormZoneExposure,
            'risk_score' => $latestRisk?->total_score,
            'weather_risk_score' => $latestRisk?->weather_score,
            'risk_level' => $latestRisk?->risk_level,
            'forex_data' => $forexTimeline['values'] ?? [],
            'forex_labels' => $forexTimeline['labels'] ?? [],
        ];
        $relatedPorts = Port::query()->where('country_code', $country->code)->orderBy('name')->get();
        $isWatched = Watchlist::query()
            ->where('user_id', auth()->id())
            ->where('country_code', $country->code)
            ->exists();

        return response()->view('ports.countries', compact('country', 'apiData', 'relatedPorts', 'isWatched'));
    }
}
