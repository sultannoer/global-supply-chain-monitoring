<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortWeatherHistory extends Model
{
    protected $fillable = ['port_id', 'temp', 'rain', 'wind_speed', 'storm_risk_status', 'risk_score', 'recorded_at'];

    protected function casts(): array
    {
        return ['temp' => 'float', 'rain' => 'float', 'wind_speed' => 'float', 'recorded_at' => 'datetime'];
    }

    public function port(): BelongsTo
    {
        return $this->belongsTo(Port::class);
    }
}
