<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Port extends Model
{
    protected $fillable = ['country_id', 'port_code', 'name', 'latitude', 'longitude', 'weather_status', 'congestion_level'];

    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    
    public function originShipments(): HasMany
    {
        return $this->hasMany(Shipment::class, 'origin_port_id');
    }

    
    public function destinationShipments(): HasMany
    {
        return $this->hasMany(Shipment::class, 'destination_port_id');
    }
}