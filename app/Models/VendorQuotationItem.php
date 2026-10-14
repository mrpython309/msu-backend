<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VendorQuotationItem extends Model
{
    protected $fillable = [
        'quotation_id',
        'indent_item_id',
        'item_name',
        'quantity',
        'unit',
        'unit_price',
        'negotiated_unit_price',
        'tax',
        'total',
        'negotiated_total',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'negotiated_unit_price' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'negotiated_total' => 'decimal:2',
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(VendorQuotation::class, 'quotation_id');
    }

    public function indentItem(): BelongsTo
    {
        return $this->belongsTo(IndentItem::class, 'indent_item_id');
    }
}
