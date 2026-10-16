<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemMaster extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'asset_type_id',
        'item_code',
        'item_name',
        'description',
        'uom',
        'status',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function assetType(): BelongsTo
    {
        return $this->belongsTo(AssetType::class, 'asset_type_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
