<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MsuDailyItemStock extends Model
{
    protected $fillable = [
        'msu_item_master_id',
        'date',
        'opening_stock',
        'stock_in',
        'stock_out',
        'closing_stock',
    ];

    protected $casts = [
        'date' => 'date',
        'opening_stock' => 'decimal:2',
        'stock_in' => 'decimal:2',
        'stock_out' => 'decimal:2',
        'closing_stock' => 'decimal:2',
    ];

    public function itemMaster()
    {
        return $this->belongsTo(MsuItemMaster::class, 'msu_item_master_id');
    }
}
