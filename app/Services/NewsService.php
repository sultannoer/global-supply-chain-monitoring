<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NewsService
{
    protected $baseUrl = 'https://gnews.io/api/v4/search';

    /**
     * Mengambil 3 berita logistik/ekonomi terbaru berdasarkan nama negara
     */
    public function getLatestNews($countryName)
    {
        $apiKey = env('GNEWS_API_KEY');

        // Validasi jika API Key terlupa belum diisi di .env
        if (!$apiKey) {
            return [
                'success' => false, 
                'message' => 'API Key GNews belum dikonfigurasi di file .env'
            ];
        }

        try {
            // Kita cari berita dengan query: "Nama Negara" AND (logistics OR economy OR trade)
            // Dibatasi hanya 3 berita teratas (max=3) dalam bahasa Inggris (lang=en)
            $response = Http::timeout(10)->get($this->baseUrl, [
                'q'        => '"' . trim($countryName) . '" AND (logistics OR economy OR trade)',
                'lang'     => 'en',
                'max'      => 3,
                'apikey'   => $apiKey
            ]);

            if ($response->successful()) {
                $articles = $response->json()['articles'] ?? [];
                $formattedNews = [];

                foreach ($articles as $article) {
                    $formattedNews[] = [
                        'judul'      => $article['title'] ?? 'N/A',
                        'deskripsi'  => $article['description'] ?? '',
                        'sumber'     => $article['source']['name'] ?? 'Global News',
                        'url'        => $article['url'] ?? '#',
                        'gambar'     => $article['image'] ?? '',
                        'tanggal'    => isset($article['publishedAt']) ? date('d M Y', strtotime($article['publishedAt'])) : 'Baru saja'
                    ];
                }

                return [
                    'success' => true,
                    'berita'  => $formattedNews
                ];
            }

            return [
                'success' => false, 
                'message' => 'Gagal menarik data berita dari server GNews.'
            ];

        } catch (\Exception $e) {
            Log::error('GNews Service Error: ' . $e->getMessage());
            return [
                'success' => false, 
                'message' => 'Terjadi kendala pada sistem pembaca berita.'
            ];
        }
    }
}
