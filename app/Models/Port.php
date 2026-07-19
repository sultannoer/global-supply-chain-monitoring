<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Port extends Model
{
    // Kolom yang diizinkan untuk diisi mass-assignment
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

    /**
     * Hubungan Pelabuhan ke Negara Asalnya (Inverse dari HasMany)
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'country_code', 'code');
    }

    /**
     * Hubungan Pelabuhan sebagai tempat KEBERANGKATAN kapal aktif
     */
    public function outboundShipments(): HasMany
    {
        return $this->hasMany(Shipment::class, 'origin_port_id');
    }

    /**
     * Hubungan Pelabuhan sebagai tempat TUJUAN AKHIR kapal aktif
     */
    public function inboundShipments(): HasMany
    {
        return $this->hasMany(Shipment::class, 'destination_port_id');
    }
}