<?php

namespace App\Models;

use App\Enums\Procurement\MsuWorkOrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MsuWorkInvoice extends Model
{
    protected $fillable = [
        'wi_number',
        'invoice_date',
        'invoice_number',
        'msu_work_order_id',
        'vendor_id',
        'branch_id',
        'notes',
        'document_path',
        'total_value',
        'sgst',
        'cgst',
        'igst',
        'grand_total',
        'miscellaneous_charges',
        'status',
        'created_by',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'total_value' => 'decimal:2',
        'sgst' => 'decimal:2',
        'cgst' => 'decimal:2',
        'igst' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'miscellaneous_charges' => 'decimal:2',
    ];

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(MsuWorkOrder::class, 'msu_work_order_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(MsuWorkInvoiceItem::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function statusEnum(): MsuWorkOrderStatus
    {
        return MsuWorkOrderStatus::tryFrom((string) $this->status)
            ?? $this->workOrder?->statusEnum()
            ?? MsuWorkOrderStatus::PartiallyPassed;
    }
}
