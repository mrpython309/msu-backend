<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PaymentRequest extends Model
{
    public const STATUS_PENDING = 'Pending';
    public const STATUS_APPROVED = 'Approved';
    public const STATUS_PARTIALLY_PAID = 'Partially Paid';
    public const STATUS_PAID = 'Paid';
    public const STATUS_REJECTED = 'Rejected';

    protected $fillable = [
        'payment_request_no',
        'purchase_order_id',
        'requested_by',
        'request_date',
        'amount',
        'payment_method',
        'status',
        'payment_date',
        'reference_number',
        'remarks',
        'attachment',
        'approved_by',
        'paid_by',
        'rejected_by',
    ];

    protected $casts = [
        'request_date' => 'datetime',
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function paidBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'paid_by');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isPartiallyPaid(): bool
    {
        return $this->status === self::STATUS_PARTIALLY_PAID;
    }

    public function isPaid(): bool
    {
        return $this->status === self::STATUS_PAID;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_PENDING => 'bg-label-warning',
            self::STATUS_APPROVED => 'bg-label-info',
            self::STATUS_PARTIALLY_PAID => 'bg-label-primary',
            self::STATUS_PAID => 'bg-label-success',
            self::STATUS_REJECTED => 'bg-label-danger',
            default => 'bg-label-secondary',
        };
    }

    public function hasAttachment(): bool
    {
        return filled($this->attachment) && Storage::disk('public')->exists($this->attachment);
    }

    public function attachmentUrl(): ?string
    {
        return $this->hasAttachment() ? Storage::disk('public')->url($this->attachment) : null;
    }
}
