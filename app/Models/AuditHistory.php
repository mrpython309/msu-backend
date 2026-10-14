<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'asset_id',
        'auditor_id',
        'auditor_name',
        'audit_status',
        'location_status',
        'remarks',
        'audited_at',
    ];

    protected $casts = [
        'audited_at' => 'datetime',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(Asset::class);
    }

    public function auditor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'auditor_id');
    }

    /**
     * Map audit status to UI badge CSS classes.
     */
    public function getBadgeClassAttribute(): string
    {
        return match ($this->audit_status) {
            'Audited', 'audited' => 'bg-label-success',
            'Not Found', 'not_found' => 'bg-label-danger',
            default => 'bg-label-warning',
        };
    }

    /**
     * Format for Mobile App timeline response.
     */
    public function toMobileArray(): array
    {
        $dt = $this->audited_at ?? $this->created_at ?? now();
        $formattedDate = Carbon::parse($dt)->format('d M Y');
        $formattedTime = Carbon::parse($dt)->format('h:i A');
        $formattedDateTime = Carbon::parse($dt)->format('d M Y, h:i A');

        return [
            'id' => $this->id,
            'status' => $this->audit_status,
            'audit_status' => $this->audit_status,
            'badge_class' => $this->badge_class,
            'auditor' => $this->auditor_name ?: 'System',
            'auditor_name' => $this->auditor_name ?: 'System',
            'date_time' => $formattedDateTime,
            'date' => $formattedDate,
            'time' => $formattedTime,
            'audited_at' => Carbon::parse($dt)->toIso8601String(),
            'remarks' => $this->remarks ?: '-',
            'location_status' => $this->location_status,
        ];
    }
}
