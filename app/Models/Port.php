<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Port extends Model
{
    
    protected $fillable = [
        'name', 
        'country_code', 
        'latitude', 
        'longitude',
        'temp', 
        'rain', 
        'wind_speed', 
        'storm_risk_status', 
        'risk_score'
    ];

    
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_code', 'code');
    }

    
    public function outboundShipments(): HasMany
    {
        return $this->hasMany(Shipment::class, 'origin_port_id');
    }

    
    public function inboundShipments(): HasMany
    {
        return $this->hasMany(Shipment::class, 'destination_port_id');
    }

    public function weatherHistories(): HasMany
    {
        return $this->hasMany(PortWeatherHistory::class);
    }
}
