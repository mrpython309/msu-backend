<?php

namespace App\Models;

use App\AssetCondition;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class GrnItem extends Model
{
    protected $fillable = [
        'grn_id',
        'po_item_id',
        'ordered_quantity',
        'unit_rate',
        'received_quantity',
        'accepted_quantity',
        'rejected_quantity',
        'free_qty',
        'damage_reason',
        'remarks',
        'item_code',
        'hsn_code',
        'manufacturer',
        'marketer_name',
        'purchase_unit',
        'brand_name',
        'model_name',
        'batch_number',
        'serial_numbers',
        'condition',
        'accessories',
        'stock_in_hand',
        'stock_after',
        'inspection_status',
        'inspection_remarks',
        'inspected_by',
        'inspected_at',
        'assets_generated_at',
    ];

    protected $casts = [
        'ordered_quantity' => 'integer',
        'received_quantity' => 'integer',
        'accepted_quantity' => 'integer',
        'rejected_quantity' => 'integer',
        'free_qty' => 'decimal:2',
        'unit_rate' => 'decimal:3',
        'stock_in_hand' => 'decimal:3',
        'stock_after' => 'decimal:3',
        'serial_numbers' => 'array',
        'condition' => AssetCondition::class,
        'inspected_at' => 'datetime',
        'assets_generated_at' => 'datetime',
    ];

    public function conditionEnum(): ?AssetCondition
    {
        return $this->condition instanceof AssetCondition
            ? $this->condition
            : AssetCondition::tryFrom(strtolower((string) $this->condition));
    }

    public function grn(): BelongsTo
    {
        return $this->belongsTo(Grn::class, 'grn_id');
    }

    public function poItem(): BelongsTo
    {
        return $this->belongsTo(PoItem::class, 'po_item_id');
    }

    public function inspectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'inspected_by');
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class, 'grn_item_id');
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(QualityInspection::class, 'grn_item_id')->latest();
    }

    public function latestInspection(): HasOne
    {
        return $this->hasOne(QualityInspection::class, 'grn_item_id')->latestOfMany();
    }

    public function isApproved(): bool
    {
        return in_array(ucfirst((string) $this->inspection_status), ['Approved', 'Passed', 'Accepted'], true);
    }

    public function isRejectedOrDamaged(): bool
    {
        return in_array(ucfirst((string) $this->inspection_status), ['Rejected', 'Damaged', 'Failed'], true);
    }

    public function isHold(): bool
    {
        return strtolower((string) $this->inspection_status) === 'hold';
    }

    public function isPending(): bool
    {
        return empty($this->inspection_status) || strtolower((string) $this->inspection_status) === 'pending';
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
