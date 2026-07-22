<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CountryWeatherHistory extends Model
{
    protected $fillable = ['country_code', 'temp', 'rain', 'wind_speed', 'storm_risk_status', 'risk_score', 'recorded_at'];

    protected function casts(): array
    {
        return ['temp' => 'float', 'rain' => 'float', 'wind_speed' => 'float', 'risk_score' => 'integer', 'recorded_at' => 'datetime'];
    }
}
