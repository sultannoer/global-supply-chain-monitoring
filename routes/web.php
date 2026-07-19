<?php

use App\Http\Controllers\PortController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;


Route::get('/', [PortController::class, 'index'])->name('ports.index');

Route::get('/ports/{id}', [PortController::class, 'show'])->name('ports.show');

Route::get('/api/live-metrics', [DashboardController::class, 'getLiveMetrics']);

Route::get('/cargo/create', [PortController::class, 'createCargo'])->name('cargo.create');

Route::post('/cargo/store', [PortController::class, 'storeCargo'])->name('cargo.store');

Route::delete('/cargo/vessel/{id}', [PortController::class, 'destroyVessel'])->name('cargo.vessel.destroy');

Route::post('/cargo/vessel/{id}/update-coordinates', [App\Http\Controllers\PortController::class, 'updateVesselCoordinates'])->name('vessel.update-coordinates');

Route::get('/cargo/history', [PortController::class, 'history'])->name('cargo.history');