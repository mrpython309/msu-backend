<?php

namespace App\Models;

use App\Enums\Procurement\PurchaseRequisitionStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class PurchaseRequisition extends Model
{
    protected $fillable = [
        'pr_no',
        'fan_no',
        'indent_id',
        'vendor_id',
        'required_date',
        'purchase_officer_id',
        'created_by',
        'requisition_store_id',
        'status',
        'created_from_approval_at',
        'remarks',
        'closed_by',
        'closed_at',
        'close_reason',
        'rejected_reason',
        'processed_at',
    ];

    protected $casts = [
        'required_date' => 'date',
        'created_from_approval_at' => 'datetime',
        'closed_at' => 'datetime',
        'processed_at' => 'datetime',
        'status' => PurchaseRequisitionStatus::class,
    ];

    public function statusEnum(): ?PurchaseRequisitionStatus
    {
        return $this->status instanceof PurchaseRequisitionStatus
            ? $this->status
            : PurchaseRequisitionStatus::tryFrom((string) $this->status);
    }

    public function allowsProcurement(): bool
    {
        return $this->statusEnum()?->allowsProcurement() ?? false;
    }

    public function canManualClose(): bool
    {
        if (! $this->allowsProcurement()) {
            return false;
        }

        return $this->status !== PurchaseRequisitionStatus::Closed;
    }

    public function scopePending(Builder $query): Builder
    {
        return $query->whereIn('status', [
            PurchaseRequisitionStatus::Pending->value,
            PurchaseRequisitionStatus::PendingPurchase->value,
        ]);
    }

    public function scopeAwaitingOfficer(Builder $query): Builder
    {
        return $this->scopePending($query);
    }

    public function scopeAvailableForPurchaseOrder(Builder $query): Builder
    {
        return $query
            ->whereIn('status', [
                PurchaseRequisitionStatus::Pending->value,
                PurchaseRequisitionStatus::PendingPurchase->value,
                PurchaseRequisitionStatus::PartialProcess->value,
                PurchaseRequisitionStatus::InProgress->value,
                PurchaseRequisitionStatus::Quoted->value,
            ])
            ->whereHas('indent')
            ->whereHas('rfqs.quotations', function (Builder $quoteQuery) {
                $quoteQuery->where('is_selected', true);
            })
            ->whereDoesntHave('purchaseOrders', function (Builder $poQuery) {
                $poQuery->whereNotIn('status', [
                    \App\Enums\Procurement\PurchaseOrderStatus::Cancelled->value,
                    \App\Enums\Procurement\PurchaseOrderStatus::Rejected->value,
                ]);
            })
            ->whereDoesntHave('rfqs.quotations.purchaseOrder', function (Builder $poQuery) {
                $poQuery->whereNotIn('status', [
                    \App\Enums\Procurement\PurchaseOrderStatus::Cancelled->value,
                    \App\Enums\Procurement\PurchaseOrderStatus::Rejected->value,
                ]);
            });
    }

    public function scopeFiltered(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, function (Builder $q, string $search) {
                $q->where(function (Builder $inner) use ($search) {
                    $inner->where('pr_no', 'like', "%{$search}%")
                        ->orWhereHas('indent', fn (Builder $indent) => $indent
                            ->where('indent_no', 'like', "%{$search}%"));
                });
            })
            ->when($filters['status'] ?? null, function (Builder $q, string $status) {
                $values = match ($status) {
                    'Pending' => [
                        PurchaseRequisitionStatus::Pending->value,
                        PurchaseRequisitionStatus::PendingPurchase->value,
                    ],
                    'Partial Process' => [
                        PurchaseRequisitionStatus::PartialProcess->value,
                        PurchaseRequisitionStatus::InProgress->value,
                        PurchaseRequisitionStatus::Quoted->value,
                        PurchaseRequisitionStatus::Ordered->value,
                    ],
                    'Completed' => [PurchaseRequisitionStatus::Completed->value],
                    'Closed' => [PurchaseRequisitionStatus::Closed->value],
                    'Rejected' => [
                        PurchaseRequisitionStatus::Rejected->value,
                        PurchaseRequisitionStatus::Cancelled->value,
                    ],
                    default => [$status],
                };
                $q->whereIn('status', $values);
            })
            ->when($filters['store_id'] ?? null, fn (Builder $q, $storeId) => $q->where('requisition_store_id', $storeId))
            ->when($filters['date_from'] ?? null, fn (Builder $q, $from) => $q->whereDate('created_at', '>=', $from))
            ->when($filters['date_to'] ?? null, fn (Builder $q, $to) => $q->whereDate('created_at', '<=', $to));
    }

    public function reasonLabel(): ?string
    {
        if ($this->close_reason) {
            return $this->close_reason;
        }

        if ($this->rejected_reason) {
            return $this->rejected_reason;
        }

        return $this->indent?->purpose;
    }

    public function indent(): BelongsTo
    {
        return $this->belongsTo(Indent::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function purchaseOfficer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'purchase_officer_id');
    }

    public function createdByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function closedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function requisitionStore(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'requisition_store_id');
    }

    public function rfq(): HasOne
    {
        return $this->hasOne(Rfq::class, 'pr_id');
    }

    public function rfqs(): HasMany
    {
        return $this->hasMany(Rfq::class, 'pr_id');
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class, 'pr_id');
    }

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ProcurementActivityLog::class, 'subject')->latest('id');
    }
}
