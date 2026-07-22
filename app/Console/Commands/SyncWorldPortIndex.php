<?php

namespace App\Console\Commands;

use App\Services\WorldPortIndexService;
use Illuminate\Console\Command;

class SyncWorldPortIndex extends Command
{
    protected $signature = 'supply-chain:sync-ports
                            {--limit=0 : Maximum WPI records to process; 0 syncs every available WPI record}
                            {--export : Export the database master back to database/ports.json}';

    protected $description = 'Synchronise ports from NGA World Port Index with local JSON fallback';

    public function handle(WorldPortIndexService $worldPortIndex): int
    {
        $result = $worldPortIndex->sync((int) $this->option('limit'));
        $this->info("Port sync complete: {$result['ports']} ports across {$result['countries']} existing countries from {$result['source']}.");

        if ($this->option('export')) {
            if (! str_starts_with($result['source'], 'world-port-index')) {
                $this->warn('Fallback dataset was used, so database/ports.json was not overwritten.');

                return self::SUCCESS;
            }

            $count = $worldPortIndex->exportFallbackPorts();
            $this->info("Fallback dataset exported: {$count} ports written to database/ports.json.");
        }

        return self::SUCCESS;
    }
}
