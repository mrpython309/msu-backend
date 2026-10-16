<?php

namespace App\Models;

use App\Enums\Procurement\IndentStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Indent extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'indent_no',
        'request_date',
        'requested_at',
        'department_id',
        'from_store_id',
        'to_store_id',
        'item_category_id',
        'requested_by',
        'priority',
        'is_urgent',
        'tag',
        'required_date',
        'purpose',
        'status',
        'approval_levels_required',
        'current_level',
        'current_sequence',
        'approval_status',
        'assigned_permission',
        'assigned_approver_id',
        'approval_template_id',
        'approved_at',
        'remarks',
        'submitted_at',
        'finalized_at',
        'request_type',
    ];

    protected $casts = [
        'request_date' => 'date',
        'requested_at' => 'datetime',
        'required_date' => 'date',
        'submitted_at' => 'datetime',
        'finalized_at' => 'datetime',
        'approved_at' => 'datetime',
        'is_urgent' => 'boolean',
        'approval_levels_required' => 'integer',
        'current_level' => 'integer',
        'current_sequence' => 'integer',
        'status' => IndentStatus::class,
    ];

    public function scopeVisibleToUser(Builder $query, User $user): Builder
    {
        if ($user->hasAnyRole(['superadmin', 'Super Admin', 'Admin'])) {
            return $query;
        }

        $departmentIds = $user->departments()->pluck('departments.id');

        return $query->where(function (Builder $q) use ($departmentIds) {
            $q->whereIn('department_id', $departmentIds)
                ->orWhereIn('from_store_id', $departmentIds);
        });
    }

    public function scopeVisibleForApproval(Builder $query, User $user): Builder
    {
        if ($user->hasAnyRole(['superadmin', 'Super Admin', 'Admin'])) {
            return $query;
        }

        $departmentIds = $user->departments()->pluck('departments.id');

        return $query->where(function (Builder $q) use ($departmentIds, $user) {
            $q->where(function (Builder $dept) use ($departmentIds) {
                $dept->whereIn('department_id', $departmentIds)
                    ->orWhereIn('from_store_id', $departmentIds);
            })
                ->orWhere('assigned_approver_id', $user->id)
                ->orWhereHas('approvalSteps', fn (Builder $s) => $s->where('approver_user_id', $user->id));
        });
    }

    public function scopePendingApproval(Builder $query): Builder
    {
        return $query->whereIn('status', [
            IndentStatus::PendingApproval->value,
            IndentStatus::Submitted->value,
            IndentStatus::L1Pending->value,
            IndentStatus::L2Pending->value,
            IndentStatus::L3Pending->value,
        ]);
    }

    public function scopeApproved(Builder $query): Builder
    {
        return $query->whereIn('status', [
            IndentStatus::Approved->value,
            IndentStatus::ConvertedToPurchaseRequisition->value,
            IndentStatus::Completed->value,
        ]);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function fromStore(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'from_store_id');
    }

    public function toStore(): BelongsTo
    {
        return $this->belongsTo(Department::class, 'to_store_id');
    }

    public function itemCategory(): BelongsTo
    {
        return $this->belongsTo(AssetCategory::class, 'item_category_id');
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(IndentItem::class);
    }

    public function approvals(): HasMany
    {
        return $this->hasMany(IndentApproval::class)->orderBy('id');
    }

    public function approvalHistories(): HasMany
    {
        return $this->hasMany(ApprovalHistory::class)->orderBy('id');
    }

    public function approvalSteps(): HasMany
    {
        return $this->hasMany(IndentApprovalStep::class)->orderBy('sequence');
    }

    public function assignedApprover(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_approver_id');
    }

    public function approvalTemplate(): BelongsTo
    {
        return $this->belongsTo(ApprovalTemplate::class, 'approval_template_id');
    }

    /**
     * Category that drives the approval template (header first, then first item).
     */
    public function workflowCategoryId(): ?int
    {
        if ($this->item_category_id) {
            return (int) $this->item_category_id;
        }

        return $this->items()
            ->whereNotNull('category_id')
            ->orderBy('id')
            ->value('category_id');
    }

    public function purchaseRequisition(): HasOne
    {
        return $this->hasOne(PurchaseRequisition::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }

    public function scopeAvailableForPurchaseOrder(Builder $query): Builder
    {
        return $query
            ->whereIn('status', [
                IndentStatus::Approved->value,
                IndentStatus::ConvertedToPurchaseRequisition->value,
            ])
            ->whereDoesntHave('purchaseOrders')
            ->where(function (Builder $inner) {
                $inner->whereDoesntHave('purchaseRequisition')
                    ->orWhereDoesntHave('purchaseRequisition.purchaseOrders');
            });
    }

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ProcurementActivityLog::class, 'subject')->latest('id');
    }

    public function statusEnum(): ?IndentStatus
    {
        return $this->status instanceof IndentStatus
            ? $this->status
            : IndentStatus::tryFrom((string) $this->status);
    }

    public function statusLabel(): string
    {
        return $this->statusEnum()?->label() ?? (string) $this->status;
    }

    public function isFullyApproved(): bool
    {
        return $this->statusEnum()?->isTerminalSuccess() ?? false;
    }

    public function canBeEdited(): bool
    {
        $status = $this->statusEnum();

        if ($status === null) {
            return false;
        }

        if (in_array($status, [
            IndentStatus::Approved,
            IndentStatus::Completed,
            IndentStatus::ConvertedToPurchaseRequisition,
            IndentStatus::Closed,
            IndentStatus::Rejected,
        ], true)) {
            return false;
        }

        // Also block when approval was rejected even if status text differs.
        if (strcasecmp((string) $this->approval_status, 'Rejected') === 0) {
            return false;
        }

        return $status === IndentStatus::Draft
            || $status->isPendingApproval()
            || $status === IndentStatus::SentBack;
    }

    public function canBeDeleted(): bool
    {
        if (strcasecmp((string) $this->approval_status, 'Rejected') === 0
            || $this->statusEnum() === IndentStatus::Rejected) {
            return false;
        }

        return ! in_array($this->statusEnum(), [
            IndentStatus::Completed,
            IndentStatus::ConvertedToPurchaseRequisition,
            IndentStatus::Closed,
            IndentStatus::Rejected,
        ], true);
    }

    public function displayStatusLabel(): string
    {
        if ($this->approval_status === 'Rejected' || $this->status === IndentStatus::Rejected) {
            return 'Rejected';
        }

        if ($this->approval_status === 'Approved'
            || $this->status === IndentStatus::Approved
            || in_array($this->status, [
                IndentStatus::Completed,
                IndentStatus::ConvertedToPurchaseRequisition,
                IndentStatus::Closed,
            ], true)) {
            return 'Approved';
        }

        return $this->statusLabel();
    }

    public function latestDecisionHistory(): ?ApprovalHistory
    {
        $query = $this->relationLoaded('approvalHistories')
            ? $this->approvalHistories
            : $this->approvalHistories()->get();

        return $query
            ->whereIn('action', [ApprovalHistory::ACTION_APPROVED, ApprovalHistory::ACTION_REJECTED])
            ->sortByDesc('id')
            ->first();
    }

    public function currentApprovalLevelLabel(): ?string
    {
        if (! $this->current_sequence) {
            return null;
        }

        return 'Level '.$this->current_sequence;
    }
}
