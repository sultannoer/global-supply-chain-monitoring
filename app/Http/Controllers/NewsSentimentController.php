<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\NegativeWord;
use App\Models\PositiveWord;
use App\Services\NewsService;
use Illuminate\Http\Request;

class NewsSentimentController extends Controller
{
    public function index(Request $request, NewsService $newsService)
    {
        $countryQuery = trim((string) $request->query('country'));
        $country = $countryQuery === '' ? null : (Country::query()->where('code', strtoupper($countryQuery))->first()
            ?? Country::query()->whereRaw('LOWER(name) = ?', [strtolower($countryQuery)])->first());
        $articles = $country
            ? $newsService->getLatestNews($country->name, 10)
            : $newsService->getLogisticsNews('global logistics OR maritime shipping OR supply chain', 10);
        $summary = $newsService->summarizeSentiment($articles);

        return view('news-sentiment.index', [
            'articles' => $articles,
            'summary' => $summary,
            'country' => $country,
            'countries' => Country::query()->orderBy('name')->get(['code', 'name']),
            'positiveWords' => PositiveWord::query()->orderBy('word')->pluck('word'),
            'negativeWords' => NegativeWord::query()->orderBy('word')->pluck('word'),
        ]);
    }
}
