<?php

namespace App\Models;

use App\Enums\Procurement\RfqStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Rfq extends Model
{
    protected $fillable = [
        'rfq_no',
        'rfq_date',
        'pr_id',
        'priority',
        'status',
        'notes',
        'notes_to_vendor',
        'created_by',
        'closes_at',
        'expected_delivery_date',
        'delivery_store',
        'currency',
        'payment_terms',
        'delivery_terms',
        'warranty_required',
        'amc_required',
        'tax_included',
        'freight_included',
    ];

    protected $casts = [
        'rfq_date' => 'date',
        'closes_at' => 'datetime',
        'expected_delivery_date' => 'date',
        'warranty_required' => 'boolean',
        'amc_required' => 'boolean',
        'tax_included' => 'boolean',
        'freight_included' => 'boolean',
        'status' => RfqStatus::class,
    ];

    public function statusEnum(): ?RfqStatus
    {
        return $this->status instanceof RfqStatus
            ? $this->status
            : RfqStatus::tryFrom((string) $this->status);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', RfqStatus::Open->value);
    }

    public function canExportBeforeQuotations(): bool
    {
        return true;
    }

    public function purchaseRequisition(): BelongsTo
    {
        return $this->belongsTo(PurchaseRequisition::class, 'pr_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function quotations(): HasMany
    {
        return $this->hasMany(VendorQuotation::class, 'rfq_id');
    }

    public function selectedQuotation(): HasOne
    {
        return $this->hasOne(VendorQuotation::class, 'rfq_id')->where('is_selected', true);
    }

    public function invitedVendors(): BelongsToMany
    {
        return $this->belongsToMany(Vendor::class, 'rfq_vendors')
            ->withPivot(['status', 'invited_at'])
            ->withTimestamps();
    }

    public function rfqVendors(): HasMany
    {
        return $this->hasMany(RfqVendor::class, 'rfq_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(RfqAttachment::class, 'rfq_id');
    }

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ProcurementActivityLog::class, 'subject')->latest('id');
    }
}
