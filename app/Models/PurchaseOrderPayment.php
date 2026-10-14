<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PurchaseOrderPayment extends Model
{
    public const METHODS = [
        'Cash',
        'Cheque',
        'UPI',
        'Bank Transfer',
        'NEFT',
        'RTGS',
        'Card',
    ];

    public const ATTACHMENT_MIMES = [
        'pdf', 'xls', 'xlsx', 'doc', 'docx',
        'jpg', 'jpeg', 'png', 'csv', 'txt', 'zip',
    ];

    protected $fillable = [
        'purchase_order_id',
        'payment_amount',
        'payment_method',
        'payment_date',
        'reference_number',
        'remarks',
        'attachment',
        'created_by',
    ];

    protected $casts = [
        'payment_amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function hasAttachment(): bool
    {
        return filled($this->attachment) && Storage::disk('public')->exists($this->attachment);
    }

    public function attachmentUrl(): ?string
    {
        return $this->hasAttachment()
            ? Storage::disk('public')->url($this->attachment)
            : null;
    }

    public function attachmentName(): ?string
    {
        return $this->attachment ? basename($this->attachment) : null;
    }
}
