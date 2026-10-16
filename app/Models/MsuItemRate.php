<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MsuItemRate extends Model
{
    protected $fillable = [
        'msu_item_master_id',
        'rate',
        'tax',
        'effective_from',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'tax' => 'decimal:2',
        'effective_from' => 'date',
    ];

    public function itemMaster(): BelongsTo
    {
        return $this->belongsTo(MsuItemMaster::class, 'msu_item_master_id');
    }
}
