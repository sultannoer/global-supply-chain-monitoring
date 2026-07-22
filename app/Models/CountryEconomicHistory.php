<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CountryEconomicHistory extends Model
{
    protected $fillable = ['country_code', 'gdp', 'inflation_rate', 'recorded_at'];

    protected function casts(): array
    {
        return ['gdp' => 'float', 'inflation_rate' => 'float', 'recorded_at' => 'datetime'];
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_code', 'code');
    }
}
