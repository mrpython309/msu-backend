<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MsuWorkInvoiceItem extends Model
{
    protected $fillable = [
        'msu_work_invoice_id',
        'msu_work_order_item_id',
        'work_description',
        'quantity',
        'price',
        'tax',
        'net_value',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'price' => 'decimal:2',
        'tax' => 'decimal:2',
        'net_value' => 'decimal:2',
    ];

    public function workInvoice(): BelongsTo
    {
        return $this->belongsTo(MsuWorkInvoice::class, 'msu_work_invoice_id');
    }

    public function workOrderItem(): BelongsTo
    {
        return $this->belongsTo(MsuWorkOrderItem::class, 'msu_work_order_item_id');
    }

    /**
     * Gross billed on this line. A later price-only line stores quantity 0,
     * so the amount is taken from net value instead of quantity × rate.
     */
    public function grossValue(): float
    {
        $qty = (float) $this->quantity;
        if ($qty > 0) {
            return round($qty * (float) $this->price, 2);
        }

        $net = (float) $this->net_value;
        if ($net <= 0) {
            return 0.0;
        }

        $tax = (float) $this->tax;
        if ($tax > 0) {
            return round($net / (1 + ($tax / 100)), 2);
        }

        return round($net, 2);
    }
}
