<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
Schedule::command('logixchain:update-vessels')->everyFiveMinutes()->withoutOverlapping();
// REST Countries v5's free plan is limited to 500 calls/month. A weekly
// 251-country refresh needs just three paginated calls, while country details
// refresh themselves only when their local profile is incomplete.
// Profil negara sudah tersimpan lokal; jangan melakukan request REST Countries
// otomatis agar kuota API tidak terpakai lagi.
Schedule::command('supply-chain:sync --country-limit=25 --port-limit=100')->everyTenMinutes()->withoutOverlapping();
Schedule::command('currency:snapshot')->hourly()->withoutOverlapping();
Schedule::command('supply-chain:sync-ports')->daily();
