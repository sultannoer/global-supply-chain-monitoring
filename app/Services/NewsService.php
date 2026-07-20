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
        $cacheKey = 'gnews_realtime_' . Str::slug($countryName ?: 'global');

        // Cache diturunkan jadi 30 menit (1800 detik) agar lebih sering update berita riil
        return Cache::remember($cacheKey, 1800, function () use ($countryName) {
            
            if (!$this->apiKey) {
                return $this->getFallbackNews($countryName);
            }

            try {
                $response = Http::timeout(5)->get('https://gnews.io/api/v4/search', [
                    'q' => '(port OR maritime OR shipping OR strike OR conflict) AND "' . $countryName . '"',
                    'lang' => 'en',
                    'sortby' => 'publishedAt', // 🚀 KUNCI UTAMA: Selalu ambil berita paling baru (Real-Time)
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
                'title' => "⚠️ [SIMULASI] Pastikan API Key GNews di .env sudah terisi!",
                'description' => "Saat ini sistem menampilkan berita simulasi karena GNEWS_API_KEY tidak ditemukan atau limit harian API gratis sudah habis. Silakan cek file .env kamu.",
                'source' => ['name' => 'System LogixChain'],
                'publishedAt' => now()->toIso8601String(),
                'url' => '#'
            ]
        ];
    }
}