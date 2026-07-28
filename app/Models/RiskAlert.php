<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
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

    /** Only unresolved alerts from the last 24 hours belong in live feeds. */
    public function scopeActive(Builder $query): Builder
    {
        return $query
            ->where('is_resolved', false)
            ->where('created_at', '>=', now()->subHours(24));
    }

    public function shipment(): BelongsTo
    {
        return $this->belongsTo(Shipment::class);
    }

    public function port(): BelongsTo
    {
        return $this->belongsTo(Port::class);
    }
}
