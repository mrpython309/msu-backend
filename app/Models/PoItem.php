<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PoItem extends Model
{
    protected $fillable = [
        'po_id',
        'indent_item_id',
        'quotation_item_id',
        'item_name',
        'item_code',
        'hsn_code',
        'description',
        'manufacturer',
        'marketer_name',
        'quantity',
        'unit',
        'has_free_qty',
        'free_qty',
        'purchase_unit',
        'price',
        'mrp',
        'gst_percent',
        'gst_amount',
        'cgst_amount',
        'sgst_amount',
        'igst_amount',
        'rate_after_discount',
        'taxable_amount',
        'base_amount',
        'discount_amount',
        'epr',
        'total',
        'item_type',
        'item_category_name',
        'item_sub_category_name',
        'item_category_id',
        'item_sub_category_id',
        'conversion',
        'discount',
        'discount_type',
        'remarks',
        'qoh',
        'estimated_delivery_date',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'has_free_qty' => 'boolean',
        'free_qty' => 'decimal:2',
        'price' => 'decimal:2',
        'mrp' => 'decimal:2',
        'gst_percent' => 'decimal:2',
        'gst_amount' => 'decimal:2',
        'cgst_amount' => 'decimal:2',
        'sgst_amount' => 'decimal:2',
        'igst_amount' => 'decimal:2',
        'rate_after_discount' => 'decimal:2',
        'taxable_amount' => 'decimal:2',
        'base_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'epr' => 'decimal:2',
        'total' => 'decimal:2',
        'discount' => 'decimal:2',
        'qoh' => 'decimal:2',
        'estimated_delivery_date' => 'date',
        'item_type' => \App\Enums\Procurement\ItemType::class,
    ];

    public function resolvedItemType(): \App\Enums\Procurement\ItemType
    {
        return \App\Enums\Procurement\ItemType::resolve(
            $this->item_type instanceof \App\Enums\Procurement\ItemType
                ? $this->item_type->value
                : $this->getRawOriginal('item_type'),
            $this->indentItem?->category
        );
    }

    public function isAssetItem(): bool
    {
        return $this->resolvedItemType()->createsAssetRecords();
    }

    /**
     * Quantity still open for receipt across all GRNs for this PO line.
     */
    public function remainingReceivableQuantity(): int
    {
        $alreadyReceived = (int) $this->grnItems()->sum('received_quantity');

        return max(0, (int) $this->quantity - $alreadyReceived);
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }

    public function indentItem(): BelongsTo
    {
        return $this->belongsTo(IndentItem::class, 'indent_item_id');
    }

    public function quotationItem(): BelongsTo
    {
        return $this->belongsTo(VendorQuotationItem::class, 'quotation_item_id');
    }

    public function grnItems(): HasMany
    {
        return $this->hasMany(GrnItem::class, 'po_item_id');
    }
}
