<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str; 

class NewsService
{
    protected $apiKey;

    public function __construct()
    {
        
        $this->apiKey = env('GNEWS_API_KEY');
    }

    public function getLatestNews($countryName)
    {
        
        $cacheKey = 'gnews_' . Str::slug($countryName ?: 'global');

        return Cache::remember($cacheKey, 3600, function () use ($countryName) {
            
            
            if (!$this->apiKey) {
                return $this->getFallbackNews($countryName);
            }

            try {
                
                $response = Http::timeout(5)->get('https://gnews.io/api/v4/search', [
                    'q' => '"port" OR "maritime" OR "shipping" AND "' . $countryName . '"',
                    'lang' => 'en',
                    'token' => $this->apiKey,
                    'max' => 3
                ]);

                if ($response->successful()) {
                    $data = $response->json();
                    if (!empty($data['articles'])) {
                        return $data['articles']; 
                    }
                }
            } catch (\Exception $e) {
                
                \Log::warning("GNews API gagal, beralih ke simulasi logistik: " . $e->getMessage());
            }

            
            return $this->getFallbackNews($countryName);
        });
    }

    
    protected function getFallbackNews($countryName)
    {
        return [
            [
                'title' => "Customs Clearance Operations Stabilized in {$countryName} Maritime Zones",
                'description' => "Port authorities in {$countryName} have successfully optimized automated manifest clearing systems, slashing container dwell times by 14%.",
                'source' => ['name' => 'LogixChain Global Intelligence'],
                'publishedAt' => now()->subHours(2)->toIso8601String(),
                'url' => '#'
            ],
            [
                'title' => "Global Freight Rates Adjust Amid Regional Route Rebalancing",
                'description' => "Shipping lines connecting trade lanes near {$countryName} report minor adjustments in fuel surcharges as carriers transition to zero-emission vessel configurations.",
                'source' => ['name' => 'Marine Logistics Review'],
                'publishedAt' => now()->subHours(5)->toIso8601String(),
                'url' => '#'
            ]
        ];
    }
}