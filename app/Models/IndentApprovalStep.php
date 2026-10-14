<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IndentApprovalStep extends Model
{
    public const STATUS_PENDING = 'Pending';

    public const STATUS_APPROVED = 'Approved';

    public const STATUS_REJECTED = 'Rejected';

    public const STATUS_SKIPPED = 'Skipped';

    public const STATUS_SENT_BACK = 'Sent Back';

    protected $fillable = [
        'indent_id',
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
        'sequence' => 'integer',
        'is_mandatory' => 'boolean',
        'acted_at' => 'datetime',
    ];

    public function indent(): BelongsTo
    {
        return $this->belongsTo(Indent::class);
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
        // Prefer live user name for person assignees (never email / level text).
        if (! $this->isPoolAssignee() && $this->approver?->name) {
            return $this->approver->name;
        }

        $raw = $this->assignee_label
            ?: ($this->assignee_role ?: ($this->assignee_permission ?: null));

        if ($raw) {
            return $this->nameOnlyLabel($raw);
        }

        return $this->approver?->name ?? '—';
    }

    public function isPoolAssignee(): bool
    {
        return in_array($this->assignee_type, ['role', 'permission'], true);
    }

    /** Strip emails and "Level N" prefixes from stored assignee labels. */
    public function nameOnlyLabel(string $label): string
    {
        $label = preg_replace('/^Level\s*\d+\s*[—\-:|.]?\s*/i', '', $label) ?? $label;
        $label = preg_replace('/\s*\([^)]*@[^)]*\)\s*/', '', $label) ?? $label;
        $label = preg_replace('/\S+@\S+/', '', $label) ?? $label;
        $label = preg_replace('/^(Role|Permission)\s*:\s*/i', '', $label) ?? $label;

        $trimmed = trim($label);

        return $trimmed !== '' ? $trimmed : '—';
    }
}
