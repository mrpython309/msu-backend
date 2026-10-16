<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MsuWorkOrderCharge extends Model
{
    protected $fillable = [
        'msu_work_order_id',
        'charge_name',
        'amount',
        'tax_percentage',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
    ];

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(MsuWorkOrder::class, 'msu_work_order_id');
    }

    public function getTaxAmountAttribute(): float
    {
        return round((float) $this->amount * ((float) $this->tax_percentage / 100), 2);
    }
}
