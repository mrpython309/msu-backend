<?php

namespace App\Models;

use App\Enums\Procurement\IndentApprovalAction;
use App\Enums\Procurement\IndentApprovalLevel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class IndentApproval extends Model
{
    protected $fillable = [
        'indent_id',
        'approver_id',
        'level',
        'action',
        'from_status',
        'to_status',
        'comments',
        'meta',
        'acted_at',
    ];

    protected $casts = [
        'level' => IndentApprovalLevel::class,
        'action' => IndentApprovalAction::class,
        'meta' => 'array',
        'acted_at' => 'datetime',
    ];

    public function levelEnum(): ?IndentApprovalLevel
    {
        return $this->level instanceof IndentApprovalLevel
            ? $this->level
            : IndentApprovalLevel::tryFrom((string) $this->level);
    }

    public function actionEnum(): ?IndentApprovalAction
    {
        return $this->action instanceof IndentApprovalAction
            ? $this->action
            : IndentApprovalAction::tryFrom((string) $this->action);
    }

    public function indent(): BelongsTo
    {
        return $this->belongsTo(Indent::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
