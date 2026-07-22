<?php

namespace Database\Seeders;

use App\Models\NegativeWord;
use App\Models\PositiveWord;
use Illuminate\Database\Seeder;

class SentimentLexiconSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        $positive = [
            'agreement', 'benefit', 'boom', 'cooperation', 'efficient', 'expansion',
            'gain', 'growth', 'improve', 'improved', 'improvement', 'increase',
            'innovation', 'investment', 'profit', 'progress', 'rebound', 'recovery',
            'resilient', 'secure', 'stability', 'stable', 'strengthen', 'strong',
            'success', 'surplus', 'upgrade', 'opportunity',
        ];
        $negative = [
            'accident', 'attack', 'blockade', 'congestion', 'conflict', 'crisis',
            'decline', 'decrease', 'delay', 'disaster', 'disruption', 'drought',
            'flood', 'inflation', 'instability', 'loss', 'risk', 'sanction',
            'shortage', 'slowdown', 'storm', 'strike', 'tension', 'war', 'warning',
            'contraction', 'damage', 'collapse', 'outage',
        ];

        PositiveWord::query()->upsert(
            collect($positive)->map(fn (string $word) => ['word' => $word, 'created_at' => $now, 'updated_at' => $now])->all(),
            ['word'],
            ['updated_at']
        );
        NegativeWord::query()->upsert(
            collect($negative)->map(fn (string $word) => ['word' => $word, 'created_at' => $now, 'updated_at' => $now])->all(),
            ['word'],
            ['updated_at']
        );
    }
}
