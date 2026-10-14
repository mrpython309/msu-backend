<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class MsuStockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'msu_item_master_id',
        'transaction_type', // 'in', 'out', 'adjustment'
        'quantity',
        'stock_before',
        'stock_after',
        'reference_type',
        'reference_id',
        'remarks',
        'created_by',
    ];

    protected static function booted()
    {
        static::created(function ($movement) {
            if (\App\Models\MsuItemMaster::$stockCache !== null) {
                $type = $movement->transaction_type;
                if (isset(\App\Models\MsuItemMaster::$stockCache[$type])) {
                    $current = \App\Models\MsuItemMaster::$stockCache[$type][$movement->msu_item_master_id] ?? 0;
                    \App\Models\MsuItemMaster::$stockCache[$type][$movement->msu_item_master_id] = $current + (float) $movement->quantity;
                }
            }
        });

        static::deleted(function ($movement) {
            if (\App\Models\MsuItemMaster::$stockCache !== null) {
                $type = $movement->transaction_type;
                if (isset(\App\Models\MsuItemMaster::$stockCache[$type])) {
                    $current = \App\Models\MsuItemMaster::$stockCache[$type][$movement->msu_item_master_id] ?? 0;
                    \App\Models\MsuItemMaster::$stockCache[$type][$movement->msu_item_master_id] = $current - (float) $movement->quantity;
                }
            }
        });
    }

    public function itemMaster(): BelongsTo
    {
        return $this->belongsTo(MsuItemMaster::class, 'msu_item_master_id');
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
