<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiskAlert extends Model
{
    protected $fillable = [
        'shipment_id',
        'port_id',
        'alert_level',
        'risk_type',
        'message',
        'is_resolved'
    ];

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function port(): BelongsTo
    {
        return $this->belongsTo(Port::class);
    }
}