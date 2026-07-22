<?php

use App\Http\Controllers\PortController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RiskScoreController;
use App\Http\Controllers\NewsSentimentController;
use App\Http\Controllers\TrendController;
use App\Http\Controllers\CountryDetailController;
use App\Http\Controllers\CountryComparisonController;
use App\Http\Controllers\WatchlistController;
use Illuminate\Support\Facades\Route;


Route::get('/', [PortController::class, 'index'])->name('ports.index');
Route::redirect('/ports', '/');

Route::get('/ports/{id}', [PortController::class, 'show'])->name('ports.show');

Route::get('/api/live-metrics', [DashboardController::class, 'getLiveMetrics']);

Route::get('/cargo/create', [PortController::class, 'createCargo'])->name('cargo.create');

Route::post('/cargo/store', [PortController::class, 'storeCargo'])->name('cargo.store');

Route::delete('/cargo/vessel/{id}', [PortController::class, 'destroyVessel'])->name('cargo.vessel.destroy');

Route::post('/cargo/vessel/{id}/update-coordinates', [App\Http\Controllers\PortController::class, 'updateVesselCoordinates'])->name('vessel.update-coordinates');

Route::get('/cargo/history', [PortController::class, 'history'])->name('cargo.history');

Route::get('/countries/{code}', [CountryDetailController::class, 'show'])->name('countries.show');

Route::get('/risk-scores', [RiskScoreController::class, 'index'])->name('risk-scores.index');
Route::get('/news-sentiment', [NewsSentimentController::class, 'index'])->name('news-sentiment.index');
Route::get('/country-comparison', [CountryComparisonController::class, 'index'])->name('country-comparison.index');
Route::get('/trends', [TrendController::class, 'index'])->name('trends.index');
Route::get('/watchlists', [WatchlistController::class, 'index'])->name('watchlists.index');
Route::post('/watchlists/{code}/toggle', [WatchlistController::class, 'toggle'])->name('watchlists.toggle');
Route::delete('/watchlists/{code}', [WatchlistController::class, 'destroy'])->name('watchlists.destroy');
