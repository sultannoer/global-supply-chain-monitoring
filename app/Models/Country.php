<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    
    protected $primaryKey = 'code';
    
    
    public $incrementing = false;
    
    
    protected $keyType = 'string';

    
    protected $fillable = [
        'code',
        'alpha2_code',
        'name', 
        'region', 
        'currency_code', 
        'language',
        'latitude',
        'longitude',
        'gdp', 
        'inflation_rate', 
        'population', 
        'export_volume', 
        'import_volume'
    ];

    
    public function ports(): HasMany
    {
        return $this->hasMany(Port::class, 'country_code', 'code');
    }

    public function riskScores(): HasMany
    {
        return $this->hasMany(RiskScore::class, 'country_code', 'code');
    }

    public function economicHistories(): HasMany
    {
        return $this->hasMany(CountryEconomicHistory::class, 'country_code', 'code');
    }

    public function weatherHistories(): HasMany
    {
        return $this->hasMany(CountryWeatherHistory::class, 'country_code', 'code');
    }
}
