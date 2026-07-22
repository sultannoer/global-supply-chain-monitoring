<?php

namespace App\Http\Controllers;

use App\Models\RiskAlert;
use App\Models\RiskScore;

class RiskScoreController extends Controller
{
    public function index()
    {
        $latestScores = RiskScore::with('country:code,name,region,currency_code')
            ->latest('calculated_at')
            ->get()
            ->unique('country_code')
            ->filter(fn (RiskScore $score) => $score->total_score !== null)
            ->sortByDesc('total_score')
            ->values();

        // Carry forward komponen terakhir yang valid untuk menghindari N/A
        // sementara ketika salah satu API gagal pada batch terbaru.
        $history = RiskScore::query()
            ->whereIn('country_code', $latestScores->pluck('country_code'))
            ->latest('calculated_at')
            ->get()
            ->groupBy('country_code');
        foreach ($latestScores as $score) {
            foreach (['weather_score', 'inflation_score', 'exchange_score', 'news_score'] as $field) {
                if ($score->{$field} !== null) continue;
                $fallback = ($history[$score->country_code] ?? collect())->first(fn (RiskScore $item) => $item->{$field} !== null);
                if ($fallback) $score->{$field} = $fallback->{$field};
            }
        }

        $summary = [
            'total' => $latestScores->count(),
            'average' => round((float) $latestScores->avg('total_score'), 1),
            'critical' => $latestScores->where('risk_level', 'CRITICAL')->count(),
            'high' => $latestScores->where('risk_level', 'HIGH')->count(),
            'coverage' => round((float) $latestScores->avg('data_coverage')),
        ];

        $alerts = RiskAlert::with(['port:id,name,country_code', 'shipment:id,tracking_number,vessel_name'])
            ->where('is_resolved', false)
            ->latest()
            ->take(10)
            ->get();

        return view('risk-scores.index', compact('latestScores', 'summary', 'alerts'));
    }
}
