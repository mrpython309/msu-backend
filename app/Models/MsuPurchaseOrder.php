<?php

namespace App\Models;

use App\Enums\Procurement\MsuPurchaseOrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MsuPurchaseOrder extends Model
{
    protected $fillable = [
        'po_number',
        'po_date',
        'delivery_date',
        'date_of_commencement',
        'vendor_id',
        'branch_id',
        'employee_master_id',
        'department_id',
        'shipping_detail_id',
        'shipping_address',
        'shipping_gst',
        'shipping_contact_person',
        'remarks',
        'payment_term_id',
        'miscellaneous_charge_id',
        'total_value',
        'miscellaneous_charges',
        'miscellaneous_tax',
        'sgst',
        'cgst',
        'igst',
        'grand_total',
        'status',
        'cancel_reason',
        'created_by',
    ];

    protected $casts = [
        'po_date' => 'date',
        'delivery_date' => 'date',
        'date_of_commencement' => 'date',
        'total_value' => 'decimal:2',
        'miscellaneous_charges' => 'decimal:2',
        'miscellaneous_tax' => 'decimal:2',
        'sgst' => 'decimal:2',
        'cgst' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'status' => MsuPurchaseOrderStatus::class,
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function employeeMaster(): BelongsTo
    {
        return $this->belongsTo(EmployeeMaster::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function shippingDetail(): BelongsTo
    {
        return $this->belongsTo(ShippingDetail::class);
    }

    public function paymentTerm(): BelongsTo
    {
        return $this->belongsTo(PaymentTerm::class);
    }

    public function miscellaneousCharge(): BelongsTo
    {
        return $this->belongsTo(MiscellaneousCharge::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(MsuPurchaseOrderItem::class)->orderBy('sort_order')->orderBy('id');
    }

    public function charges(): HasMany
    {
        return $this->hasMany(MsuPurchaseOrderCharge::class);
    }

    public function grns(): HasMany
    {
        return $this->hasMany(MsuGrn::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recalculateTotals(): void
    {
        $aggregates = MsuPurchaseOrderItem::query()
            ->where('msu_purchase_order_id', $this->id)
            ->selectRaw('COALESCE(SUM(quantity * price), 0) as base, COALESCE(SUM((quantity * price) * (tax / 100)), 0) as tax')
            ->first();

        $lineBase = (float) $aggregates->base;
        $lineTax = (float) $aggregates->tax;

        $miscChargesAggregates = MsuPurchaseOrderCharge::query()
            ->where('msu_purchase_order_id', $this->id)
            ->selectRaw('COALESCE(SUM(amount), 0) as total_amount, COALESCE(SUM(amount * (tax_percentage / 100)), 0) as total_tax')
            ->first();

        $miscAmount = round((float) $miscChargesAggregates->total_amount, 2);
        $miscTaxAmount = round((float) $miscChargesAggregates->total_tax, 2);
        
        $this->miscellaneous_charges = $miscAmount;
        $this->miscellaneous_tax = $miscTaxAmount;

        $totalTax = $lineTax + $miscTaxAmount; // Items tax + misc charge tax
        $halfTax = round($totalTax / 2, 2);

        $this->loadMissing(['branch', 'vendor']);
        
        $isInterState = false;
        if ($this->branch && $this->vendor) {
            $branchState = trim(strtolower((string)$this->branch->state));
            $vendorState = trim(strtolower((string)$this->vendor->state));
            
            // If branch state is empty, assume base state 'karnataka'
            if ($branchState === '') $branchState = 'karnataka';
            if ($vendorState === '') $vendorState = 'karnataka';
            
            if ($branchState !== $vendorState) {
                $isInterState = true;
            }
        }

        $this->total_value = round($lineBase, 2);
        
        if ($isInterState) {
            $this->igst = round($totalTax, 2);
            $this->cgst = 0;
            $this->sgst = 0;
        } else {
            $this->igst = 0;
            $this->sgst = $halfTax;
            $this->cgst = round($totalTax - $halfTax, 2);
        }
        
        $this->grand_total = round(
            (float) $this->total_value
            + $miscAmount
            + $totalTax,
            2
        );
        $this->save();
    }

    public function getMiscellaneousTaxAmountAttribute(): float
    {
        return (float) $this->miscellaneous_tax;
    }

    public function getMiscellaneousTotalAttribute(): float
    {
        return round((float) $this->miscellaneous_charges + (float) $this->miscellaneous_tax, 2);
    }

    public function statusEnum(): MsuPurchaseOrderStatus
    {
        if ($this->status instanceof MsuPurchaseOrderStatus) {
            return $this->status;
        }

        return MsuPurchaseOrderStatus::fromLegacy(is_string($this->status) ? $this->status : null);
    }

    /**
     * Update PO status from GRN / receipt activity on line items.
     * Call this after any GRN receipt updates received_quantity on items.
     */
    public function syncStatusFromReceipts(): void
    {
        if ($this->statusEnum() === MsuPurchaseOrderStatus::Cancelled) {
            return;
        }

        $this->loadMissing('items');

        if ($this->items->isEmpty()) {
            $this->update(['status' => MsuPurchaseOrderStatus::PoCreated]);

            return;
        }

        $anyReceived = $this->items->contains(fn ($item) => (float) $item->received_quantity > 0);
        $fullyReceived = $this->items->every(
            fn ($item) => (float) $item->received_quantity >= (float) $item->quantity
        );

        $hasGrns = $this->grns()->exists();

        $status = match (true) {
            $fullyReceived && ($anyReceived || $hasGrns) => MsuPurchaseOrderStatus::GrnReceived,
            $anyReceived || $hasGrns => MsuPurchaseOrderStatus::GrnPartiallyReceived,
            default => MsuPurchaseOrderStatus::PoCreated,
        };

        if ($this->statusEnum() !== $status) {
            $this->update(['status' => $status]);
        }
    }

    public function cancel(): bool
    {
        if (! $this->statusEnum()->canCancel()) {
            return false;
        }

        $this->update(['status' => MsuPurchaseOrderStatus::Cancelled]);

        return true;
    }
}
