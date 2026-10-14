<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MsuPurchaseOrderItem extends Model
{
    protected $fillable = [
        'msu_purchase_order_id',
        'msu_item_master_id',
        'item_code',
        'item_name',
        'hsn',
        'quantity',
        'received_quantity',
        'price',
        'tax',
        'net_value',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'received_quantity' => 'decimal:3',
        'price' => 'decimal:2',
        'tax' => 'decimal:2',
        'net_value' => 'decimal:2',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(MsuPurchaseOrder::class, 'msu_purchase_order_id');
    }

    public function itemMaster(): BelongsTo
    {
        return $this->belongsTo(MsuItemMaster::class, 'msu_item_master_id');
    }

    public static function computeGrossValue(float $quantity, float $rate): float
    {
        return round($quantity * $rate, 2);
    }

    public static function computeTaxValue(float $quantity, float $rate, float $taxPercent): float
    {
        return round(self::computeGrossValue($quantity, $rate) * ($taxPercent / 100), 2);
    }

    public static function computeNetValue(float $quantity, float $price, float $taxPercent): float
    {
        $gross = self::computeGrossValue($quantity, $price);
        $taxAmount = self::computeTaxValue($quantity, $price, $taxPercent);

        return round($gross + $taxAmount, 2);
    }

    public function getGrossValueAttribute(): float
    {
        return self::computeGrossValue((float) $this->quantity, (float) $this->price);
    }

    public function getTaxValueAttribute(): float
    {
        return self::computeTaxValue((float) $this->quantity, (float) $this->price, (float) $this->tax);
    }
}
