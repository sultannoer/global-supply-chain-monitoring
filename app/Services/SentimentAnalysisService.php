<?php

namespace App\Services;

use App\Models\NegativeWord;
use App\Models\NewsSentiment;
use App\Models\PositiveWord;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;

class SentimentAnalysisService
{
    /** Analyze and retain the result for one article returned by GNews. */
    public function analyzeArticle(array $article, ?string $query = null): array
    {
        $text = trim(($article['title'] ?? '').' '.($article['description'] ?? ''));
        preg_match_all('/[\p{L}\p{N}]+/u', mb_strtolower($text), $matches);
        $tokens = $matches[0] ?? [];
        $positive = $this->countMatches($tokens, $this->positiveWords());
        $negative = $this->countMatches($tokens, $this->negativeWords());
        $sentiment = $positive > $negative ? 'Positive' : ($negative > $positive ? 'Negative' : 'Neutral');

        $hashSource = $article['url'] ?: implode('|', [
            $article['title'] ?? '', $article['publishedAt'] ?? '', data_get($article, 'source.name', ''),
        ]);

        NewsSentiment::query()->updateOrCreate(
            ['article_hash' => hash('sha256', $hashSource)],
            [
                'query' => $query,
                'title' => $article['title'] ?? 'Untitled article',
                'description' => $article['description'] ?? null,
                'url' => $article['url'] ?? null,
                'source' => data_get($article, 'source.name', 'GNews'),
                'published_at' => $this->parseDate($article['publishedAt'] ?? null),
                'positive_score' => $positive,
                'negative_score' => $negative,
                'sentiment' => $sentiment,
                'analyzed_at' => now(),
            ]
        );

        return $article + [
            'sentiment' => $sentiment,
            'positive_score' => $positive,
            'negative_score' => $negative,
        ];
    }

    public function summarize(array $articles): array
    {
        $counts = ['Positive' => 0, 'Neutral' => 0, 'Negative' => 0];
        foreach ($articles as $article) {
            $sentiment = $article['sentiment'] ?? 'Neutral';
            $counts[array_key_exists($sentiment, $counts) ? $sentiment : 'Neutral']++;
        }

        $total = count($articles);
        $percentage = fn (string $type): float => $total === 0 ? 0.0 : round(($counts[$type] / $total) * 100, 1);

        return [
            'total_articles' => $total,
            'positive' => $counts['Positive'],
            'neutral' => $counts['Neutral'],
            'negative' => $counts['Negative'],
            'positive_percentage' => $percentage('Positive'),
            'neutral_percentage' => $percentage('Neutral'),
            'negative_percentage' => $percentage('Negative'),
        ];
    }

    private function positiveWords(): array
    {
        return Cache::remember('sentiment:positive-words', now()->addDay(), fn () => PositiveWord::query()->pluck('word')->all());
    }

    private function negativeWords(): array
    {
        return Cache::remember('sentiment:negative-words', now()->addDay(), fn () => NegativeWord::query()->pluck('word')->all());
    }

    private function countMatches(array $tokens, array $lexicon): int
    {
        $wordSet = array_flip($lexicon);

        return count(array_filter($tokens, fn (string $token) => isset($wordSet[$token])));
    }

    private function parseDate(?string $date): ?Carbon
    {
        if (! $date) {
            return null;
        }

        try {
            return Carbon::parse($date);
        } catch (\Throwable) {
            return null;
        }
    }
}
