<?php

namespace App\Models;

use App\Enums\Procurement\PurchaseOrderPaymentStatus;
use App\Enums\Procurement\PurchaseOrderSource;
use App\Enums\Procurement\PurchaseOrderStatus;
use App\Models\ApprovalTemplate;
use App\Models\PoApprovalStep;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class PurchaseOrder extends Model
{
    protected $fillable = [
        'po_no',
        'quotation_id',
        'pr_id',
        'indent_id',
        'vendor_id',
        'purchase_date',
        'delivery_date',
        'grn_store',
        'grn_store_id',
        'po_type',
        'po_source',
        'department_id',
        'department_name',
        'store_id',
        'payment_terms',
        'minimum_advance_payment_percentage',
        'currency',
        'tax',
        'discount',
        'discount_type',
        'shipping',
        'total_quantity',
        'sub_total',
        'total_discount',
        'taxable_amount',
        'total_gst',
        'total_cgst',
        'total_sgst',
        'total_igst',
        'grand_total',
        'status',
        'created_by',
        'remarks',
        'notes',
        'submitted_at',
        'approved_at',
        'current_sequence',
        'assigned_approver_id',
        'approval_template_id',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'delivery_date' => 'date',
        'minimum_advance_payment_percentage' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'shipping' => 'decimal:2',
        'total_quantity' => 'integer',
        'sub_total' => 'decimal:2',
        'total_discount' => 'decimal:2',
        'taxable_amount' => 'decimal:2',
        'total_gst' => 'decimal:2',
        'total_cgst' => 'decimal:2',
        'total_sgst' => 'decimal:2',
        'total_igst' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'status' => PurchaseOrderStatus::class,
        'po_source' => PurchaseOrderSource::class,
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
    ];

    public function statusEnum(): ?PurchaseOrderStatus
    {
        return $this->status instanceof PurchaseOrderStatus
            ? $this->status
            : PurchaseOrderStatus::tryFrom((string) $this->status);
    }

    public function sourceEnum(): ?PurchaseOrderSource
    {
        return $this->po_source instanceof PurchaseOrderSource
            ? $this->po_source
            : PurchaseOrderSource::tryFrom((string) $this->po_source);
    }

    public function scopeIssued(Builder $query): Builder
    {
        return $query->whereIn('status', [
            PurchaseOrderStatus::Sent->value,
            PurchaseOrderStatus::Issued->value,
            PurchaseOrderStatus::Accepted->value,
            PurchaseOrderStatus::Approved->value,
            PurchaseOrderStatus::PartiallyDelivered->value,
            PurchaseOrderStatus::PartiallyReceived->value,
            PurchaseOrderStatus::Completed->value,
            PurchaseOrderStatus::Received->value,
        ]);
    }

    public function scopeAwaitingGrn(Builder $query): Builder
    {
        return $query->whereIn('status', [
            PurchaseOrderStatus::Approved->value,
            PurchaseOrderStatus::Sent->value,
            PurchaseOrderStatus::Issued->value,
            PurchaseOrderStatus::Accepted->value,
            PurchaseOrderStatus::PartiallyDelivered->value,
            PurchaseOrderStatus::PartiallyReceived->value,
        ])->whereHas('items', function (Builder $itemQuery) {
            $itemQuery->whereRaw(
                '(SELECT COALESCE(SUM(grn_items.received_quantity), 0) FROM grn_items WHERE grn_items.po_item_id = po_items.id) < po_items.quantity'
            );
        });
    }

    public function scopeFiltered(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, function (Builder $q, string $search) {
                $q->where(function (Builder $inner) use ($search) {
                    $inner->where('po_no', 'like', "%{$search}%")
                        ->orWhereHas('vendor', fn (Builder $v) => $v->where('name', 'like', "%{$search}%"))
                        ->orWhereHas('indent', fn (Builder $i) => $i->where('indent_no', 'like', "%{$search}%"));
                });
            })
            ->when($filters['status'] ?? null, function (Builder $q, $status) {
                $q->whereIn('status', PurchaseOrderStatus::statusesMatchingFilter((string) $status));
            })
            ->when($filters['po_source'] ?? null, fn (Builder $q, $source) => $q->where('po_source', $source))
            ->when($filters['date_from'] ?? null, fn (Builder $q, $from) => $q->whereDate('purchase_date', '>=', $from))
            ->when($filters['date_to'] ?? null, fn (Builder $q, $to) => $q->whereDate('purchase_date', '<=', $to));
    }

    public function quotation(): BelongsTo
    {
        return $this->belongsTo(VendorQuotation::class, 'quotation_id');
    }

    public function isFromRfq(): bool
    {
        return $this->linkedRfq() !== null;
    }

    public function linkedRfq(): ?Rfq
    {
        if (filled($this->quotation_id)) {
            $this->loadMissing('quotation.rfq');

            return $this->quotation?->rfq;
        }

        if (filled($this->pr_id)) {
            $this->loadMissing('purchaseRequisition.rfqs');

            return $this->purchaseRequisition?->rfqs?->sortByDesc('id')->first();
        }

        return null;
    }

    public function rfqNo(): ?string
    {
        return $this->linkedRfq()?->rfq_no;
    }

    public function purchaseRequisition(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequisition::class, 'pr_id');
    }

    public function indent(): BelongsTo
    {
        return $this->belongsTo(Indent::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function store(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'store_id');
    }

    public function grnStore(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'grn_store_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PoItem::class, 'po_id');
    }

    /** @return list<string> */
    public function itemNameList(): array
    {
        $this->loadMissing('items');

        return $this->items
            ->pluck('item_name')
            ->filter(fn ($name) => filled($name))
            ->values()
            ->all();
    }

    public function grns(): HasMany
    {
        return $this->hasMany(Grn::class, 'po_id');
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'purchase_order_id');
    }

    public function approvalSteps(): HasMany
    {
        return $this->hasMany(PoApprovalStep::class, 'po_id')->orderBy('sequence');
    }

    public function assignedApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_approver_id');
    }

    public function approvalTemplate(): BelongsTo
    {
        return $this->belongsTo(ApprovalTemplate::class, 'approval_template_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PurchaseOrderPayment::class, 'purchase_order_id')->latest('payment_date')->latest('id');
    }

    public function paymentRequests(): HasMany
    {
        return $this->hasMany(PaymentRequest::class, 'purchase_order_id')->latest('id');
    }

    public function activePaymentRequest(): ?PaymentRequest
    {
        if ($this->relationLoaded('paymentRequests')) {
            return $this->paymentRequests->firstWhere('status', '!=', PaymentRequest::STATUS_REJECTED);
        }

        return $this->paymentRequests()->where('status', '!=', PaymentRequest::STATUS_REJECTED)->first();
    }

    public function orderTotalAmount(): float
    {
        return round((float) $this->grand_total, 2);
    }

    public function paidAmount(): float
    {
        if ($this->relationLoaded('payments')) {
            return round((float) $this->payments->sum('payment_amount'), 2);
        }

        return round((float) $this->payments()->sum('payment_amount'), 2);
    }

    public function remainingAmount(): float
    {
        return max(0, round($this->orderTotalAmount() - $this->paidAmount(), 2));
    }

    public function paymentStatusEnum(): PurchaseOrderPaymentStatus
    {
        return PurchaseOrderPaymentStatus::fromAmounts($this->paidAmount(), $this->remainingAmount());
    }

    public function isFullyPaid(): bool
    {
        return $this->remainingAmount() <= 0 && $this->orderTotalAmount() > 0;
    }

    public function isPendingApproval(): bool
    {
        return $this->status === \App\Enums\Procurement\PurchaseOrderStatus::Submitted;
    }

    /** Payment is allowed only after approval is complete (not Draft / Submitted / Rejected / Cancelled). */
    public function allowsPayment(): bool
    {
        $status = $this->statusEnum();

        return $status?->allowsPayment() ?? false;
    }

    public function isCompletedStatus(): bool
    {
        $status = $this->statusEnum();

        return in_array($status, [
            PurchaseOrderStatus::Approved,
            PurchaseOrderStatus::Completed,
            PurchaseOrderStatus::Received,
        ], true);
    }

    public function isRejectedStatus(): bool
    {
        return $this->statusEnum() === PurchaseOrderStatus::Rejected;
    }

    public function canBeEdited(): bool
    {
        if ($this->isCompletedStatus() || $this->isRejectedStatus()) {
            return false;
        }

        // Draft and Submitted (sent-back / pending) may be edited on the edit form.
        // Approve / Reject stay on the show page only.
        return $this->statusEnum()?->isEditable() ?? false;
    }

    public function canBeDeleted(): bool
    {
        if ($this->isCompletedStatus()) {
            return false;
        }

        // Rejected POs may be deleted; completed/closed/received may not.
        return ! in_array($this->statusEnum(), [
            PurchaseOrderStatus::Closed,
            PurchaseOrderStatus::Completed,
            PurchaseOrderStatus::Received,
        ], true);
    }

    /**
     * Category that drives the PO approval template.
     * Prefers the line-item subcategory (e.g. "Others") over the parent category,
     * then falls back to the linked indent — same leaf-category matching as templates.
     */
    public function workflowCategoryId(): ?int
    {
        $item = $this->items()
            ->where(function (Builder $q) {
                $q->whereNotNull('item_sub_category_id')
                    ->orWhereNotNull('item_category_id');
            })
            ->orderBy('id')
            ->first(['item_category_id', 'item_sub_category_id']);

        if ($item?->item_sub_category_id) {
            return (int) $item->item_sub_category_id;
        }

        if ($item?->item_category_id) {
            return (int) $item->item_category_id;
        }

        if ($this->indent_id) {
            $indent = $this->relationLoaded('indent') ? $this->indent : $this->indent()->first();
            if ($indent) {
                $fromIndent = $indent->workflowCategoryId();
                if ($fromIndent) {
                    return $fromIndent;
                }
            }
        }

        return null;
    }

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ProcurementActivityLog::class, 'subject')->latest('id');
    }
}
