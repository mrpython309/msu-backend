<?php

namespace App\Models;

use App\Enums\Procurement\MsuWorkOrderFrequency;
use App\Enums\Procurement\MsuWorkOrderStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MsuWorkOrder extends Model
{
    protected $fillable = [
        'wo_number',
        'wo_date',
        'contract_period_from',
        'contract_period_to',
        'frequency',
        'recurrence_source_id',
        'next_occurrence_date',
        'subject',
        'delivery_date',
        'date_of_commencement',
        'vendor_id',
        'branch_id',
        'employee_master_id',
        'department_id',
        'shipping_detail_id',
        'shipping_address',
        'shipping_contact_person',
        'shipping_gst',
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
        'wo_date' => 'date',
        'contract_period_from' => 'date',
        'contract_period_to' => 'date',
        'next_occurrence_date' => 'date',
        'frequency' => MsuWorkOrderFrequency::class,
        'delivery_date' => 'date',
        'date_of_commencement' => 'date',
        'total_value' => 'decimal:2',
        'miscellaneous_charges' => 'decimal:2',
        'miscellaneous_tax' => 'decimal:2',
        'sgst' => 'decimal:2',
        'cgst' => 'decimal:2',
        'igst' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'status' => MsuWorkOrderStatus::class,
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
        return $this->hasMany(MsuWorkOrderItem::class)->orderBy('sort_order')->orderBy('id');
    }

    public function charges(): HasMany
    {
        return $this->hasMany(MsuWorkOrderCharge::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(MsuWorkInvoice::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function recurrenceSource(): BelongsTo
    {
        return $this->belongsTo(self::class, 'recurrence_source_id');
    }

    public function occurrences(): HasMany
    {
        return $this->hasMany(self::class, 'recurrence_source_id');
    }

    public function recalculateTotals(): void
    {
        $aggregates = MsuWorkOrderItem::query()
            ->where('msu_work_order_id', $this->id)
            ->selectRaw('COALESCE(SUM(quantity * price), 0) as base, COALESCE(SUM((quantity * price) * (tax / 100)), 0) as tax')
            ->first();

        $lineBase = (float) $aggregates->base;
        $lineTax = (float) $aggregates->tax;

        $miscChargesAggregates = MsuWorkOrderCharge::query()
            ->where('msu_work_order_id', $this->id)
            ->selectRaw('COALESCE(SUM(amount), 0) as total_amount, COALESCE(SUM(amount * (tax_percentage / 100)), 0) as total_tax')
            ->first();

        $miscAmount = round((float) $miscChargesAggregates->total_amount, 2);
        $miscTaxAmount = round((float) $miscChargesAggregates->total_tax, 2);
        
        $this->miscellaneous_charges = $miscAmount;
        $this->miscellaneous_tax = $miscTaxAmount;

        $totalTax = $lineTax + $miscTaxAmount; // Items tax + misc tax
        $halfTax = round($totalTax / 2, 2);

        $this->loadMissing(['branch', 'vendor']);
        
        $isInterState = false;
        if ($this->branch && $this->vendor) {
            $branchState = trim(strtolower((string)$this->branch->state));
            $vendorState = trim(strtolower((string)$this->vendor->state));
            
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

    public function statusEnum(): MsuWorkOrderStatus
    {
        if ($this->status instanceof MsuWorkOrderStatus) {
            return $this->status;
        }
        return MsuWorkOrderStatus::tryFrom($this->status) ?? MsuWorkOrderStatus::WoCreated;
    }

    public function frequencyEnum(): ?MsuWorkOrderFrequency
    {
        if ($this->frequency instanceof MsuWorkOrderFrequency) {
            return $this->frequency;
        }

        return is_string($this->frequency) ? MsuWorkOrderFrequency::tryFrom($this->frequency) : null;
    }

    /**
     * Passed only when every line is fully passed on both quantity and price.
     * Any other mix of partial and full progress stays Partially Passed.
     */
    public function syncStatusFromInvoices(): void
    {
        if ($this->statusEnum() === MsuWorkOrderStatus::Cancelled) {
            return;
        }

        $this->load(['items.invoiceItems']);
        if ($this->items->isEmpty()) {
            if ($this->statusEnum() !== MsuWorkOrderStatus::Approved) {
                $this->update(['status' => MsuWorkOrderStatus::WoCreated]);
            }
            return;
        }

        $quantityFullyPassed = $this->items->every(
            fn ($item) => (float) $item->received_quantity + 0.0000001 >= (float) $item->quantity
        );
        $priceFullyPassed = $this->items->every(
            fn ($item) => $item->billedGrossAmount() + 0.009 >= $item->orderedAmount()
        );

        $anyQuantity = $this->items->contains(fn ($item) => (float) $item->received_quantity > 0);
        $anyPrice = $this->items->contains(fn ($item) => $item->billedGrossAmount() > 0.009);
        $hasInvoices = $this->invoices()->exists();
        $previous = $this->statusEnum();

        $status = match (true) {
            $quantityFullyPassed && $priceFullyPassed && ($anyQuantity || $anyPrice || $hasInvoices) => MsuWorkOrderStatus::Passed,
            $anyQuantity || $anyPrice || $hasInvoices => MsuWorkOrderStatus::PartiallyPassed,
            $previous === MsuWorkOrderStatus::Approved => MsuWorkOrderStatus::Approved,
            default => MsuWorkOrderStatus::WoCreated,
        };

        if ($previous !== $status) {
            $this->update(['status' => $status]);
        }
    }

    public function cancel(): bool
    {
        if (! $this->statusEnum()->canCancel()) {
            return false;
        }

        $this->update(['status' => MsuWorkOrderStatus::Cancelled]);
        return true;
    }
}
