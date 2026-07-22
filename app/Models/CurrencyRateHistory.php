<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CurrencyRateHistory extends Model
{
    protected $fillable = ['currency_code', 'rate_to_usd', 'source', 'recorded_at'];

    protected function casts(): array
    {
        return [
            'rate_to_usd' => 'float',
            'recorded_at' => 'datetime',
        ];
    }
}
