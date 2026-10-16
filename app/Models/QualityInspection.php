<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QualityInspection extends Model
{
    protected $fillable = [
        'grn_id',
        'grn_item_id',
        'inspected_by',
        'inspected_at',
        'inspection_status',
        'remarks',
    ];

    protected $casts = [
        'inspected_at' => 'datetime',
    ];

    public function grn(): BelongsTo
    {
        return $this->belongsTo(Grn::class);
    }

    public function grnItem(): BelongsTo
    {
        return $this->belongsTo(GrnItem::class);
    }

    public function inspector(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspected_by');
    }

    public function statusBadgeClass(): string
    {
        return match (ucfirst((string) $this->inspection_status)) {
            'Approved', 'Passed', 'Accepted' => 'success',
            'Rejected', 'Failed' => 'danger',
            'Damaged' => 'warning',
            'Hold', 'Under Review' => 'info',
            default => 'secondary',
        };
    }
}
