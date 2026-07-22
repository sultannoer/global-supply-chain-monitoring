<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\CountryEconomicHistory;
use App\Models\CountryWeatherHistory;
use App\Models\CurrencyRateHistory;
use App\Models\RiskScore;
use App\Services\NewsService;
use Illuminate\Http\Request;

class CountryComparisonController extends Controller
{
    public function index(Request $request, NewsService $newsService)
    {
        $countries = Country::query()->orderBy('name')->get(['code', 'name', 'currency_code']);
        $left = $this->findCountry($countries, $request->query('country_a'));
        $right = $this->findCountry($countries, $request->query('country_b'));

        return view('country-comparison.index', [
            'countries' => $countries,
            'left' => $left ? $this->metrics($left, $newsService) : null,
            'right' => $right ? $this->metrics($right, $newsService) : null,
            'leftCode' => $left?->code,
            'rightCode' => $right?->code,
        ]);
    }

    private function findCountry($countries, ?string $value): ?Country
    {
        $value = trim((string) $value);
        if ($value === '') return null;
        return $countries->firstWhere('code', strtoupper($value))
            ?? $countries->first(fn (Country $country) => strtolower($country->name) === strtolower($value));
    }

    private function metrics(Country $country, NewsService $newsService): array
    {
        $risk = RiskScore::query()->where('country_code', $country->code)->latest('calculated_at')->first();
        $economic = CountryEconomicHistory::query()->where('country_code', $country->code)->latest('recorded_at')->first();
        $weather = CountryWeatherHistory::query()->where('country_code', $country->code)->latest('recorded_at')->first();
        $currency = CurrencyRateHistory::query()->where('currency_code', $country->currency_code)->latest('recorded_at')->first();
        $articles = $newsService->getLatestNews($country->name, 10);

        return [
            'country' => $country,
            'flag_code' => \App\Services\CountryFlagService::iso2($country->code),
            'gdp' => $country->gdp ?? $economic?->gdp,
            'inflation' => $country->inflation_rate ?? $economic?->inflation_rate,
            'risk' => $risk,
            'weather' => $weather,
            'currency' => $currency,
            'news' => $newsService->summarizeSentiment($articles),
        ];
    }
}
