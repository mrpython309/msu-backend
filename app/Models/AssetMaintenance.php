<?php

namespace App\Models;

use App\AssetOperationalStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class AssetMaintenance extends Model
{
    use HasFactory;

    protected $table = 'asset_maintenance';

    protected $fillable = [
        'asset_id',
        'maintenance_code',
        'source_type',
        'support_ticket_id',
        'maintenance_date',
        'maintenance_notes',
        'maintenance_cost',
        'vendor_id',
        'priority',
        'status',
        'start_date',
        'end_date',
        'remarks',
        'reported_by',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'maintenance_date' => 'date',
        'start_date'       => 'date',
        'end_date'         => 'date',
        'maintenance_cost' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($maintenance) {
            if (!$maintenance->maintenance_code) {
                $lastId = self::max('id') + 1;
                $maintenance->maintenance_code = 'MTN-' . str_pad($lastId, 5, '0', STR_PAD_LEFT);
            }

            if ($maintenance->asset_id) {
                static::markAssetUnderMaintenance($maintenance->asset_id);
            }
        });


        static::updating(function ($maintenance) {
            if (! $maintenance->isDirty('status') || ! $maintenance->asset_id) {
                return;
            }

            if (in_array($maintenance->status, ['completed', 'cancelled'], true)) {
                static::restoreWorkingIfNoOpenMaintenance($maintenance->asset_id, $maintenance->id);
            }
        });

        


    }


     /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function asset()
    {
        return $this->belongsTo(Asset::class);
    }

    public function supportTicket()
    {
        return $this->belongsTo(SupportTicket::class);
    }

    /**
     * Close the maintenance row created for this asset ticket:
     * vendor, cost, start/end dates, and status become completed.
     * Asset operational status returns to Working when nothing else is still open.
     */
    public static function completeFromSupportTicket(SupportTicket $ticket): void
    {
        if (! $ticket->asset_id) {
            return;
        }

        $maintenance = static::openMaintenanceForTicket($ticket);

        if (! $maintenance) {
            static::restoreWorkingIfNoOpenMaintenance($ticket->asset_id);

            return;
        }

        $startDate = $maintenance->start_date
            ?? $maintenance->maintenance_date
            ?? $ticket->created_at;

        $maintenance->fill([
            'support_ticket_id' => $ticket->id,
            'source_type' => 'support_ticket',
            'status' => 'completed',
            'vendor_id' => $ticket->vendor_id ?: $maintenance->vendor_id,
            'maintenance_cost' => $ticket->total_cost ?? 0,
            'start_date' => $startDate,
            'end_date' => now(),
            'updated_by' => auth()->id(),
        ]);

        $maintenance->save();
    }

    public static function openMaintenanceForTicket(SupportTicket $ticket): ?self
    {
        $linked = static::query()
            ->where('support_ticket_id', $ticket->id)
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->latest('id')
            ->first();

        if ($linked || ! $ticket->asset_id) {
            return $linked;
        }

        $query = static::query()
            ->where('asset_id', $ticket->asset_id)
            ->whereNull('support_ticket_id')
            ->where('source_type', 'support_ticket')
            ->whereNotIn('status', ['completed', 'cancelled']);

        if ($ticket->created_at) {
            $matchedByTime = (clone $query)
                ->whereBetween('created_at', [
                    $ticket->created_at->copy()->subMinutes(2),
                    $ticket->created_at->copy()->addMinutes(2),
                ])
                ->latest('id')
                ->first();

            if ($matchedByTime) {
                return $matchedByTime;
            }
        }

        if ($ticket->description) {
            return (clone $query)
                ->where('maintenance_notes', $ticket->description)
                ->latest('id')
                ->first();
        }

        return null;
    }

    public static function markAssetUnderMaintenance(int $assetId): void
    {
        Asset::where('id', $assetId)
            ->where(function ($query) {
                $query->whereNull('operational_status')
                    ->orWhereNotIn('operational_status', [
                        AssetOperationalStatus::DISPOSED->value,
                        AssetOperationalStatus::UNDER_MAINTENANCE->value,
                    ]);
            })
            ->update([
                'operational_status' => AssetOperationalStatus::UNDER_MAINTENANCE->value,
            ]);
    }

    public static function restoreWorkingIfNoOpenMaintenance(int $assetId, ?int $exceptMaintenanceId = null): void
    {
        $hasOpen = static::query()
            ->where('asset_id', $assetId)
            ->when($exceptMaintenanceId, fn ($query) => $query->where('id', '!=', $exceptMaintenanceId))
            ->whereNotIn('status', ['completed', 'cancelled'])
            ->exists();

        if ($hasOpen) {
            return;
        }

        Asset::where('id', $assetId)
            ->where(function ($query) {
                $query->whereNull('operational_status')
                    ->orWhereNotIn('operational_status', [
                        AssetOperationalStatus::DISPOSED->value,
                        AssetOperationalStatus::WORKING->value,
                    ]);
            })
            ->update([
                'operational_status' => AssetOperationalStatus::WORKING->value,
            ]);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /*
    |--------------------------------------------------------------------------
    | Scopes
    |--------------------------------------------------------------------------
    */

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCritical($query)
    {
        return $query->where('priority', 'critical');
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function markAsCompleted()
    {
        $this->update([
            'status'   => 'completed',
            'end_date' => now(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getFormattedCostAttribute()
    {
        return $this->maintenance_cost
            ? number_format($this->maintenance_cost, 2)
            : '0.00';
    }
}
