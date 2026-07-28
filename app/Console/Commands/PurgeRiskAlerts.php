<?php

namespace App\Console\Commands;

use App\Models\RiskAlert;
use Illuminate\Console\Command;

class PurgeRiskAlerts extends Command
{
    protected $signature = 'risk-alerts:purge';

    protected $description = 'Delete risk alerts older than 24 hours';

    public function handle(): int
    {
        $cutoff = now()->subHours(24);
        $deleted = RiskAlert::where('created_at', '<', $cutoff)->delete();

        $this->info("Deleted {$deleted} risk alerts older than 24 hours.");

        return self::SUCCESS;
    }
}
