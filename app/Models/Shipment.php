<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipment extends Model
{
    
    protected $fillable = [
        'tracking_number', 
        'vessel_name', 
        'origin_port_id', 
        'destination_port_id',
        'current_lat', 
        'current_lng', 
        'departure_date', 
        'baseline_eta', 
        'adaptive_eta',
        'initial_cost_usd', 
        'current_exchange_rate', 
        'status', 
        'risk_score'
    ];

    
    protected $casts = [
        'departure_date' => 'date',
        'baseline_eta' => 'date',
        'adaptive_eta' => 'date',
    ];

    
    public function originPort(): BelongsTo
    {
        return $this->belongsTo(Port::class, 'origin_port_id');
    }

   
    public function destinationPort(): BelongsTo
    {
        return $this->belongsTo(Port::class, 'destination_port_id');
    }
}