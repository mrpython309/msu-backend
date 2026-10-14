<?php

namespace App\Models;

use App\Enums\Procurement\GrnStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Grn extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'grn_no',
        'po_id',
        'vendor_id',
        'received_date',
        'received_by',
        'received_by_phone',
        'delivered_by',
        'delivered_by_phone',
        'vehicle_no',
        'warehouse',
        'invoice_number',
        'invoice_date',
        'challan_number',
        'challan_date',
        'bill_date',
        'bill_amount',
        'delivery_memo_no',
        'delivery_memo_date',
        'gate_entry_no',
        'gate_entry_date',
        'cost_center_name',
        'characteristic',
        'department_name',
        'po_type',
        'inspection_status',
        'inspection_remarks',
        'status',
        'invoice_path',
        'delivery_challan_path',
        'remarks',
        'inspected_by',
        'inspected_at',
        'approved_by',
        'accepted_at',
    ];

    protected $casts = [
        'received_date' => 'date',
        'invoice_date' => 'date',
        'challan_date' => 'date',
        'bill_date' => 'date',
        'delivery_memo_date' => 'date',
        'gate_entry_date' => 'datetime',
        'bill_amount' => 'decimal:2',
        'inspected_at' => 'datetime',
        'accepted_at' => 'datetime',
        'status' => GrnStatus::class,
    ];

    public function statusEnum(): ?GrnStatus
    {
        return $this->status instanceof GrnStatus
            ? $this->status
            : GrnStatus::tryFrom((string) $this->status);
    }

    public function scopePendingInspection(Builder $query): Builder
    {
        return $query->where('status', GrnStatus::PendingInspection->value);
    }

    public function scopeFiltered(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, function (Builder $q, string $search) {
                $q->where(function (Builder $inner) use ($search) {
                    $inner->where('grn_no', 'like', "%{$search}%")
                        ->orWhereHas('purchaseOrder', fn (Builder $po) => $po->where('po_no', 'like', "%{$search}%"))
                        ->orWhereHas('vendor', fn (Builder $v) => $v->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($filters['status'] ?? null, fn (Builder $q, $status) => $q->where('status', $status))
            ->when($filters['date_from'] ?? null, fn (Builder $q, $from) => $q->whereDate('received_date', '>=', $from))
            ->when($filters['date_to'] ?? null, fn (Builder $q, $to) => $q->whereDate('received_date', '<=', $to));
    }

    public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class, 'po_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function inspectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspected_by');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(GrnItem::class, 'grn_id');
    }

    /** @return list<string> */
    public function itemNameList(): array
    {
        $this->loadMissing('items.poItem');

        return $this->items
            ->map(fn (GrnItem $item) => $item->poItem?->item_name)
            ->filter(fn ($name) => filled($name))
            ->values()
            ->all();
    }

    public function documents(): HasMany
    {
        return $this->hasMany(GrnDocument::class, 'grn_id');
    }

    public function photos(): HasMany
    {
        return $this->hasMany(GrnDocument::class, 'grn_id')->where('document_type', 'photo');
    }

    public function histories(): HasMany
    {
        return $this->hasMany(GrnHistory::class, 'grn_id')->latest('id');
    }

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ProcurementActivityLog::class, 'subject')->latest('id');
    }

    public function qualityInspections(): HasMany
    {
        return $this->hasMany(QualityInspection::class, 'grn_id')->latest();
    }

    public function inspectionSummary(): array
    {
        $this->loadMissing('items');
        $items = $this->items;
        $total = $items->count();
        $approved = $items->filter(fn ($i) => $i->isApproved())->count();
        $rejected = $items->filter(fn ($i) => $i->isRejectedOrDamaged())->count();
        $hold = $items->filter(fn ($i) => $i->isHold())->count();
        $pending = $items->filter(fn ($i) => $i->isPending())->count();

        return [
            'total' => $total,
            'approved' => $approved,
            'rejected_or_damaged' => $rejected,
            'hold' => $hold,
            'pending' => $pending,
            'is_fully_inspected' => ($pending === 0 && $hold === 0 && $total > 0),
        ];
    }
}
