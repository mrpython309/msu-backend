<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalTemplateStep extends Model
{
    public const ASSIGNEE_USER = 'user';

    public const ASSIGNEE_ROLE = 'role';

    public const ASSIGNEE_PERMISSION = 'permission';

    protected $fillable = [
        'approval_template_id',
        'sequence',
        'assignee_type',
        'approver_user_id',
        'assignee_role',
        'assignee_permission',
        'is_mandatory',
    ];

    protected $casts = [
        'sequence' => 'integer',
        'is_mandatory' => 'boolean',
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(ApprovalTemplate::class, 'approval_template_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_user_id');
    }

    public function assigneeLabel(): string
    {
        return match ($this->assignee_type) {
            self::ASSIGNEE_ROLE => $this->assignee_role ?: '—',
            self::ASSIGNEE_PERMISSION => $this->assignee_permission ?: '—',
            default => $this->approver?->name ?: '—',
        };
    }

    public function hasAssigneeConfigured(): bool
    {
        $type = $this->assignee_type ?: self::ASSIGNEE_USER;

        return match ($type) {
            self::ASSIGNEE_USER => ! empty($this->approver_user_id),
            self::ASSIGNEE_ROLE => trim((string) $this->assignee_role) !== '',
            self::ASSIGNEE_PERMISSION => trim((string) $this->assignee_permission) !== '',
            default => false,
        };
    }
}
