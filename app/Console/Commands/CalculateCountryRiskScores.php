<?php

namespace App\Console\Commands;

use App\Models\Country;
use App\Services\RiskAssessmentService;
use Illuminate\Console\Command;

class CalculateCountryRiskScores extends Command
{
    protected $signature = 'risk-scores:calculate {--limit=0 : Maximum countries to calculate; 0 calculates all}';

    protected $description = 'Calculate and store current country risk scores from available real API data';

    public function handle(RiskAssessmentService $riskAssessmentService): int
    {
        $limit = max(0, (int) $this->option('limit'));
        $query = Country::query()->orderBy('code');
        if ($limit > 0) {
            $query->limit($limit);
        }

        $countries = $query->get();
        $this->info("Calculating risk scores for {$countries->count()} countries...");

        $bar = $this->output->createProgressBar($countries->count());
        foreach ($countries as $country) {
            $riskAssessmentService->calculateCountryRisk($country);
            $bar->advance();
        }
        $bar->finish();
        $this->newLine();

        return self::SUCCESS;
    }
}
