<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PoApprovalStep extends Model
{
    public const STATUS_PENDING = 'Pending';
    public const STATUS_APPROVED = 'Approved';
    public const STATUS_REJECTED = 'Rejected';
    public const STATUS_SENT_BACK = 'Sent Back';
    public const STATUS_SKIPPED = 'Skipped';

    protected $table = 'po_approval_steps';

    protected $fillable = [
        'po_id',
        'template_step_id',
        'sequence',
        'assignee_type',
        'approver_user_id',
        'assignee_role',
        'assignee_permission',
        'assignee_label',
        'is_mandatory',
        'status',
        'acted_by',
        'remarks',
        'acted_at',
    ];

    protected $casts = [
        'sequence'     => 'integer',
        'is_mandatory' => 'boolean',
        'acted_at'     => 'datetime',
    ];

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }

    public function templateStep(): BelongsTo
    {
        return $this->belongsTo(ApprovalTemplateStep::class, 'template_step_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_user_id');
    }

    public function actedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'acted_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function displayLabel(): string
    {
        return $this->assignee_label
            ?: ($this->approver?->name ?? ('Level '.$this->sequence));
    }
}
