<?php

namespace App\Services;

use App\Models\Port;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NewsService
{
    public function __construct(private readonly SentimentAnalysisService $sentimentAnalysis)
    {
    }

    public function getLatestNews(string $country = '', int $limit = 3): array
    {
        $query = trim($country) !== ''
            ? sprintf('(%s) (logistics OR shipping OR trade OR economy)', $country)
            : 'supply chain OR maritime logistics OR shipping';

        return $this->analyze($this->search($query, $limit), $query);
    }

    /**
     * Search news for one port first, then fall back to country news when the
     * wire has no article that mentions the specific terminal.
     *
     * The result includes a scope so the UI can distinguish a true port hit
     * from a country-level fallback. Identical queries are cached for six
     * hours by search(), so opening the same port does not spend another call.
     */
    public function getPortNews(Port $port, int $limit = 5): array
    {
        $portName = trim($port->name);
        $countryName = trim((string) ($port->country?->name ?? ''));
        $quotedPort = '"'.str_replace('"', '', $portName).'"';
        $quotedCountry = $countryName !== '' ? ' "'.str_replace('"', '', $countryName).'"' : '';
        $query = trim($quotedPort.$quotedCountry.' (port OR terminal OR harbor OR shipping OR congestion OR cargo)');

        $articles = $this->analyze($this->search($query, $limit), $query);
        if ($articles !== []) {
            return ['articles' => $articles, 'scope' => 'port', 'query' => $query];
        }

        $fallback = $this->getLatestNews($countryName, $limit);

        return [
            'articles' => $fallback,
            'scope' => $fallback !== [] ? 'country_fallback' : 'none',
            'query' => $query,
        ];
    }

    public function getLogisticsNews(string $query = 'global logistics', int $limit = 5): array
    {
        return $this->analyze($this->search($query, $limit), $query);
    }

    public function summarizeSentiment(array $articles): array
    {
        return $this->sentimentAnalysis->summarize($articles);
    }

    private function analyze(array $articles, string $query): array
    {
        return array_map(fn (array $article) => $this->sentimentAnalysis->analyzeArticle($article, $query), $articles);
    }

    private function search(string $query, int $limit): array
    {
        $key = config('services.gnews.key');
        if (! is_string($key) || trim($key) === '') {
            Log::warning('GNews key is not configured.');
            return [];
        }

        $limit = max(1, min($limit, 10));
        $cacheKey = 'gnews:'.md5(strtolower($query).':'.$limit);

        // Keep each identical query cached for six hours so country-scoped
        // risk calculations and the News Sentiment page share one GNews set.
        return Cache::remember($cacheKey, now()->addHours(6), function () use ($query, $limit, $key) {
            try {
                $response = Http::acceptJson()
                    ->timeout(config('services.gnews.timeout', 8))
                    ->retry(2, 300)
                    ->get(rtrim(config('services.gnews.base_url'), '/').'/search', [
                        'q' => $query,
                        'lang' => 'en',
                        'max' => $limit,
                        'apikey' => $key,
                    ]);

                if (! $response->successful()) {
                    Log::warning('GNews request failed.', ['status' => $response->status(), 'body' => $response->body()]);
                    return [];
                }

                return collect($response->json('articles', []))
                    ->map(fn (array $article) => [
                        'title' => $article['title'] ?? 'Untitled article',
                        'description' => $article['description'] ?? '',
                        'url' => $article['url'] ?? null,
                        'publishedAt' => $article['publishedAt'] ?? null,
                        'source' => ['name' => $article['source']['name'] ?? 'GNews'],
                    ])
                    ->values()
                    ->all();
            } catch (\Throwable $exception) {
                Log::warning('GNews request failed.', ['message' => $exception->getMessage()]);
                return [];
            }
        });
    }
}
