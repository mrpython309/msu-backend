<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MsuGrnItem extends Model
{
    protected $fillable = [
        'msu_grn_id',
        'msu_purchase_order_item_id',
        'msu_item_master_id',
        'item_code',
        'item_name',
        'hsn',
        'ordered_quantity',
        'previously_received_quantity',
        'received_quantity',
        'is_capex',
        'is_group_asset',
        'sort_order',
    ];

    protected $casts = [
        'ordered_quantity' => 'decimal:3',
        'previously_received_quantity' => 'decimal:3',
        'received_quantity' => 'decimal:3',
        'is_capex' => 'boolean',
        'is_group_asset' => 'boolean',
    ];

    public function grn(): BelongsTo
    {
        return $this->belongsTo(MsuGrn::class, 'msu_grn_id');
    }

    public function purchaseOrderItem(): BelongsTo
    {
        return $this->belongsTo(MsuPurchaseOrderItem::class, 'msu_purchase_order_item_id');
    }

    public function itemMaster(): BelongsTo
    {
        return $this->belongsTo(MsuItemMaster::class, 'msu_item_master_id');
    }

    public function assets()
    {
        return $this->hasMany(Asset::class, 'msu_grn_item_id');
    }

    public function getBalanceQuantityAttribute(): float
    {
        return max(0, (float) $this->ordered_quantity - (float) $this->previously_received_quantity);
    }

    public function expenseTypeLabel(): string
    {
        return $this->is_capex ? 'Capex' : 'Opex';
    }
}
