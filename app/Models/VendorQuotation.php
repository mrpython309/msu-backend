<?php

namespace App\Models;

use App\Enums\Procurement\NegotiationStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class VendorQuotation extends Model
{
    protected $fillable = [
        'rfq_id',
        'vendor_id',
        'contact_person',
        'contact_phone',
        'contact_email',
        'rating_snapshot',
        'price',
        'delivery_days',
        'warranty',
        'tax',
        'shipping',
        'total_cost',
        'negotiated_total_cost',
        'negotiation_status',
        'buyer_remarks',
        'vendor_response',
        'negotiations_locked_at',
        'notes',
        'is_selected',
        'quoted_at',
        'selected_at',
        'selected_by',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'tax' => 'decimal:2',
        'shipping' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'negotiated_total_cost' => 'decimal:2',
        'rating_snapshot' => 'decimal:2',
        'delivery_days' => 'integer',
        'is_selected' => 'boolean',
        'quoted_at' => 'datetime',
        'selected_at' => 'datetime',
        'negotiations_locked_at' => 'datetime',
    ];

    public function scopeSelected(Builder $query): Builder
    {
        return $query->where('is_selected', true);
    }

    public function rfq(): BelongsTo
    {
        return $this->belongsTo(Rfq::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function selectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'selected_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(VendorQuotationItem::class, 'quotation_id');
    }

    public function negotiationHistories(): HasMany
    {
        return $this->hasMany(QuotationNegotiationHistory::class, 'vendor_quotation_id')
            ->orderBy('round');
    }

    public function purchaseOrder(): HasOne
    {
        return $this->hasOne(PurchaseOrder::class, 'quotation_id');
    }

    public function activityLogs(): MorphMany
    {
        return $this->morphMany(ProcurementActivityLog::class, 'subject')->latest('id');
    }

    /** Original quoted total — never changed by negotiation. */
    public function originalQuote(): float
    {
        return (float) ($this->total_cost ?? $this->price ?? 0);
    }

    /** Latest negotiated total, or original when never negotiated. */
    public function effectiveQuote(): float
    {
        if ($this->negotiated_total_cost !== null) {
            return (float) $this->negotiated_total_cost;
        }

        return $this->originalQuote();
    }

    public function savingsAmount(): float
    {
        return max(0, $this->originalQuote() - $this->effectiveQuote());
    }

    public function hasNegotiatedPrice(): bool
    {
        return $this->negotiated_total_cost !== null;
    }

    /**
     * Negotiated purchase rate for a quotation line (or blended unit rate from negotiated total).
     */
    public function negotiatedUnitRate(?VendorQuotationItem $item = null): ?float
    {
        if (! $this->hasNegotiatedPrice()) {
            return null;
        }

        $effectiveTotal = $this->effectiveQuote();
        if ($effectiveTotal <= 0) {
            return null;
        }

        if ($item && $item->negotiated_unit_price !== null && (float) $item->negotiated_unit_price >= 0) {
            return round((float) $item->negotiated_unit_price, 2);
        }

        if ($item && (float) $item->unit_price > 0) {
            $originalTotal = $this->originalQuote();
            if ($originalTotal > 0) {
                return round((float) $item->unit_price * ($effectiveTotal / $originalTotal), 2);
            }
        }

        $this->loadMissing('items');
        $qty = max(1, (int) $this->items->sum('quantity'));

        return round($effectiveTotal / $qty, 2);
    }

    public function isNegotiationLocked(): bool
    {
        if ($this->is_selected || ($this->rfq_id && self::where('rfq_id', $this->rfq_id)->where('is_selected', true)->exists())) {
            return true;
        }

        if ($this->negotiations_locked_at !== null) {
            return true;
        }

        if ($this->relationLoaded('purchaseOrder')) {
            return $this->purchaseOrder !== null;
        }

        return $this->purchaseOrder()->exists();
    }

    public function negotiationStatusEnum(): ?NegotiationStatus
    {
        return NegotiationStatus::tryFrom((string) ($this->negotiation_status ?? ''));
    }

    public function negotiationStatusBadgeClass(): string
    {
        return $this->negotiationStatusEnum()?->badgeClass() ?? 'bg-label-secondary';
    }
}
