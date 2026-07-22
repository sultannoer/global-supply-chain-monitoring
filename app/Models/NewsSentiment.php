<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsSentiment extends Model
{
    protected $fillable = [
        'article_hash', 'query', 'title', 'description', 'url', 'source',
        'published_at', 'positive_score', 'negative_score', 'sentiment', 'analyzed_at',
    ];

    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'analyzed_at' => 'datetime',
        ];
    }
}
