<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Shipment extends Model
{
    protected $fillable = [
        'shipment_number',
        'origin_port_id',
        'destination_port_id',
        'departure_date',
        'estimated_arrival_date',
        'actual_arrival_date',
        'initial_cost',
        'current_cost',
        'risk_status',
        'risk_reason'
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