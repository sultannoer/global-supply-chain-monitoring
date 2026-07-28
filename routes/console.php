<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('logixchain:update-vessels')->everyFiveMinutes()->withoutOverlapping();
Schedule::command('risk-alerts:purge')->hourly()->withoutOverlapping();
// REST Countries v5's free plan is limited to 500 calls/month. A weekly
// 251-country refresh needs just three paginated calls, while country details
// refresh themselves only when their local profile is incomplete.
// Profil negara sudah tersimpan lokal; jangan melakukan request REST Countries
// otomatis agar kuota API tidak terpakai lagi.
Schedule::command('supply-chain:sync --country-limit=25 --port-limit=100')->everyTenMinutes()->withoutOverlapping();
Schedule::command('currency:snapshot')->hourly()->withoutOverlapping();
Schedule::command('supply-chain:sync-ports')->daily();
Schedule::command('ports:weather-backfill')->dailyAt('01:30')->withoutOverlapping();
