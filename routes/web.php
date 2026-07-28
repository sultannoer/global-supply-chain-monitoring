<?php

use App\Http\Controllers\PortController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\RiskScoreController;
use App\Http\Controllers\NewsSentimentController;
use App\Http\Controllers\TrendController;
use App\Http\Controllers\CountryDetailController;
use App\Http\Controllers\CountryComparisonController;
use App\Http\Controllers\WatchlistController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\PortController as AdminPortController;
use App\Http\Controllers\Admin\ArticleController as AdminArticleController;
use App\Http\Controllers\Admin\WatchlistController as AdminWatchlistController;
use Illuminate\Support\Facades\Route;

// Login juga dapat dibuka oleh user yang sedang aktif agar bisa berpindah akun
// (misalnya dari User ke Admin) melalui tombol Login Admin.
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');

Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::resource('users', AdminUserController::class)->except('show');
    Route::resource('ports', AdminPortController::class)->except('show');
    Route::resource('articles', AdminArticleController::class)->except('show');
    Route::get('watchlists', [AdminWatchlistController::class, 'index'])->name('watchlists.index');
    Route::delete('watchlists/{watchlist}', [AdminWatchlistController::class, 'destroy'])->name('watchlists.destroy');
});


// Seluruh halaman aplikasi melewati login. Setelah autentikasi, user diarahkan
// ke radar utama dan admin diarahkan ke dashboard admin.
Route::middleware('auth')->group(function () {
    Route::get('/', [PortController::class, 'index'])->name('ports.index');
    Route::redirect('/ports', '/');

    Route::get('/ports/{id}', [PortController::class, 'show'])->name('ports.show');

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
});

// Dipertahankan sebagai endpoint data untuk kebutuhan integrasi eksternal.
Route::get('/api/live-metrics', [DashboardController::class, 'getLiveMetrics']);
