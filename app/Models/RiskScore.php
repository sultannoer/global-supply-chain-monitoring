<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiskScore extends Model
{
    protected $fillable = [
        'country_code', 'weather_score', 'inflation_score', 'exchange_score',
        'news_score', 'exchange_rate', 'total_score', 'data_coverage',
        'risk_level', 'calculated_at',
    ];

    protected function casts(): array
    {
        return [
            'weather_score' => 'float',
            'inflation_score' => 'float',
            'exchange_score' => 'float',
            'news_score' => 'float',
            'exchange_rate' => 'float',
            'total_score' => 'float',
            'calculated_at' => 'datetime',
        ];
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_code', 'code');
    }
}
