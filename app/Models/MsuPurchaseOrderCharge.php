<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MsuPurchaseOrderCharge extends Model
{
    use HasFactory;

    protected $fillable = [
        'msu_purchase_order_id',
        'charge_name',
        'amount',
        'tax_percentage',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(MsuPurchaseOrder::class, 'msu_purchase_order_id');
    }

    public function getTaxAmountAttribute(): float
    {
        return round((float) $this->amount * ((float) $this->tax_percentage / 100), 2);
    }
}
