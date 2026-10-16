<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalHistory extends Model
{
    public const ACTION_SUBMITTED = 'Submitted';

    public const ACTION_APPROVED = 'Approved';

    public const ACTION_REJECTED = 'Rejected';

    public const ACTION_SENT_BACK = 'Sent Back';

    protected $table = 'approval_history';

    protected $fillable = [
        'indent_id',
        'workflow_id',
        'sequence',
        'approver_id',
        'action',
        'remarks',
        'action_date',
    ];

    protected $casts = [
        'sequence' => 'integer',
        'action_date' => 'datetime',
    ];

    public function indent(): BelongsTo
    {
        return $this->belongsTo(Indent::class);
    }

    public function workflow(): BelongsTo
    {
        return $this->belongsTo(ApprovalWorkflow::class, 'workflow_id');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }
}
