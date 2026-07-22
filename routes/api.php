<?php

use App\Models\Country;
use App\Models\Port;
use App\Models\RiskAlert;
use App\Models\RiskScore;
use App\Services\ExchangeRateService;
use App\Services\NewsService;
use App\Http\Controllers\LiveMarkerController;
use Illuminate\Support\Facades\Route;

Route::get('/countries', function () {
    return response()->json([
        'status' => 'success',
        'source' => 'REST Countries (synchronised locally)',
        'data' => Country::query()->orderBy('name')->get(),
    ]);
});

Route::get('/risk', function () {
    $scores = RiskScore::with('country:code,name')
        ->latest('calculated_at')
        ->get()
        ->unique('country_code')
        ->sortByDesc('total_score')
        ->take(30)
        ->values();

    return response()->json([
        'status' => 'success',
        'source' => 'Weighted RiskAssessmentService output',
        'weights' => ['weather' => 35, 'inflation' => 25, 'exchange' => 15, 'news' => 25],
        'data' => $scores,
        'alerts' => RiskAlert::with(['port:id,name,country_code', 'shipment:id,tracking_number,vessel_name,risk_score'])
            ->where('is_resolved', false)->latest()->take(20)->get(),
    ]);
});

Route::get('/ports', function () {
    return response()->json([
        'status' => 'success',
        'source' => 'World Port Index (synchronised locally)',
        'data' => Port::with('country:code,name')->orderBy('name')->get(),
    ]);
});

Route::get('/news', function (NewsService $newsService) {
    $articles = $newsService->getLogisticsNews('global logistics OR maritime shipping OR supply chain');

    return response()->json([
        'status' => $articles === [] ? 'unavailable' : 'success',
        'source' => 'GNews',
        'sentiment_summary' => $newsService->summarizeSentiment($articles),
        'articles' => $articles,
    ], $articles === [] ? 503 : 200);
});

Route::get('/currency', function (ExchangeRateService $exchangeService) {
    $data = $exchangeService->getLatestRates();

    if ($data === []) {
        return response()->json([
            'status' => 'unavailable',
            'source' => 'open.er-api.com',
            'message' => 'ExchangeRate API did not return a current rate.',
        ], 503);
    }

    return response()->json(['status' => 'success'] + $data);
});

Route::get('/live/markers/countries/{code}', [LiveMarkerController::class, 'country']);
Route::get('/live/markers/ports/{id}', [LiveMarkerController::class, 'port']);
