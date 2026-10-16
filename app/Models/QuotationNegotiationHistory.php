<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuotationNegotiationHistory extends Model
{
    protected $fillable = [
        'vendor_quotation_id',
        'round',
        'old_price',
        'new_price',
        'buyer_remarks',
        'vendor_response',
        'status',
        'negotiated_by',
        'negotiated_at',
    ];

    protected $casts = [
        'old_price' => 'decimal:2',
        'new_price' => 'decimal:2',
        'round' => 'integer',
        'negotiated_at' => 'datetime',
    ];

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(VendorQuotation::class, 'vendor_quotation_id');
    }

    public function negotiatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'negotiated_by');
    }
}
