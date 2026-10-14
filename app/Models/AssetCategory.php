<?php

namespace App\Models;

use App\Enums\Procurement\ItemType;
use Illuminate\Database\Eloquent\Model;

class AssetCategory extends Model
{
    protected $fillable = [
        'category_name',
        'parent_id',
        'status',
        'item_type',
    ];

    protected $casts = [
        'item_type' => ItemType::class,
    ];

    public function subCategories()
    {
        return $this->hasMany(AssetCategory::class, 'parent_id');
    }

    public function parent()
    {
        return $this->belongsTo(AssetCategory::class, 'parent_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function itemTypeEnum(): ItemType
    {
        return $this->item_type instanceof ItemType
            ? $this->item_type
            : (ItemType::tryFromMixed($this->item_type) ?? ItemType::Asset);
    }

    public function isAssetCategory(): bool
    {
        return $this->itemTypeEnum()->createsAssetRecords();
    }
}
