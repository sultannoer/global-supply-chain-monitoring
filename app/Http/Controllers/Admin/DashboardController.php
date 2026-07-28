<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Article;
use App\Models\Country;
use App\Models\Port;
use App\Models\RiskScore;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $latestRisks = RiskScore::with('country:code,name')
            ->latest('calculated_at')
            ->get()
            ->unique('country_code')
            ->sortByDesc('total_score')
            ->take(5)
            ->values();

        return view('admin.dashboard', [
            'stats' => [
                'users' => User::count(),
                'countries' => Country::count(),
                'ports' => Port::count(),
                'articles' => Article::count(),
            ],
            'latestArticles' => Article::with('author:id,name')->latest()->take(5)->get(),
            'latestRisks' => $latestRisks,
        ]);
    }
}
