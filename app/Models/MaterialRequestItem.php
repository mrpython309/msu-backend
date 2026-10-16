<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialRequestItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'material_request_id',
        'msu_item_master_id',
        'requested_qty',
        'issued_qty',
        'unit_id',
        'remarks',
    ];

    public function materialRequest(): BelongsTo
    {
        return $this->belongsTo(MaterialRequest::class);
    }

    public function itemMaster(): BelongsTo
    {
        return $this->belongsTo(MsuItemMaster::class, 'msu_item_master_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function getRemainingQtyAttribute(): float
    {
        return max(0, $this->requested_qty - $this->issued_qty);
    }
}
