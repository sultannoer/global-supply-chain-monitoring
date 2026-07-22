<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\CountryService;

class SyncAllCountries extends Command
{
    protected $signature = 'countries:sync-all';
    protected $description = 'Sinkronisasi massal seluruh negara di dunia dari REST Countries API';

    public function handle(CountryService $countryService)
    {
        $this->info("Sedang mengunduh SELURUH negara di dunia dari REST Countries API...");

        // Panggil bulk sync (tanpa memfilter tabel ports)
        $success = $countryService->syncAllCountriesBulk();

        if ($success) {
            $total = \App\Models\Country::withoutGlobalScopes()->count();
            $this->info("✅ BERHASIL! Sekarang ada total {$total} negara di database kamu.");
        } else {
            $this->error("❌ Gagal terhubung ke REST Countries API.");
        }
    }
}