<?php

use App\Http\Controllers\PortController;
use Illuminate\Support\Facades\Route;

// Halaman utama default Laravel kita arahkan langsung ke daftar pelabuhan
Route::get('/', [PortController::class, 'index'])->name('ports.index');

// Rute untuk melihat detail cuaca dan informasi per pelabuhan
Route::get('/ports/{id}', [PortController::class, 'show'])->name('ports.show');