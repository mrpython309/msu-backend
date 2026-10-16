<?php

namespace App\Models;

use App\Enums\Procurement\ItemType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class IndentItem extends Model
{
    protected $fillable = [
        'indent_id',
        'item_name',
        'category_id',
        'description',
        'quantity',
        'unit',
        'estimated_price',
        'total_amount',
        'item_type',
        'stock_in_hand',
        'item_remarks',
        'manufacturer',
        'composition',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'estimated_price' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'stock_in_hand' => 'decimal:2',
        'item_type' => ItemType::class,
    ];

    public function indent(): BelongsTo
    {
        return $this->belongsTo(Indent::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'category_id');
    }

    public function quotationItems(): HasMany
    {
        return $this->hasMany(VendorQuotationItem::class, 'indent_item_id');
    }

    public function poItems(): HasMany
    {
        return $this->hasMany(PoItem::class, 'indent_item_id');
    }

    public function resolvedItemType(): ItemType
    {
        return ItemType::resolve(
            $this->item_type instanceof ItemType ? $this->item_type->value : $this->getRawOriginal('item_type'),
            $this->category
        );
    }

    public function isAssetItem(): bool
    {
        return $this->resolvedItemType()->createsAssetRecords();
    }
}
