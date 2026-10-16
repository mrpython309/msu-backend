<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MsuWorkOrderItem extends Model
{
    protected $fillable = [
        'msu_work_order_id',
        'work_description',
        'scope_of_work',
        'hsn',
        'unit_id',
        'quantity',
        'price',
        'tax',
        'net_value',
        'received_quantity',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'decimal:3',
        'price' => 'decimal:2',
        'tax' => 'decimal:2',
        'net_value' => 'decimal:2',
        'received_quantity' => 'decimal:3',
    ];

    public function workOrder(): BelongsTo
    {
        return $this->belongsTo(MsuWorkOrder::class, 'msu_work_order_id');
    }

    public function unit(): BelongsTo
    {
        return $this->belongsTo(Unit::class);
    }

    public function invoiceItems(): HasMany
    {
        return $this->hasMany(MsuWorkInvoiceItem::class);
    }

    /**
     * Quantity passed is the invoiced quantity, capped at the work order quantity.
     * A later invoice that only clears a remaining price stores quantity 0 and must not push this above the ordered quantity.
     */
    public function syncReceivedQuantityFromInvoices(): void
    {
        $this->unsetRelation('invoiceItems');
        $received = min(max(0, (float) $this->quantity), $this->billedQuantity());

        $this->update(['received_quantity' => $received]);
    }

    public function orderedAmount(): float
    {
        return round((float) $this->quantity * (float) $this->price, 2);
    }

    public function billedQuantity(): float
    {
        $lines = $this->relationLoaded('invoiceItems')
            ? $this->invoiceItems
            : $this->invoiceItems()->get(['quantity']);

        return round((float) $lines->sum(fn ($line) => (float) $line->quantity), 3);
    }

    public function billedGrossAmount(): float
    {
        $lines = $this->relationLoaded('invoiceItems')
            ? $this->invoiceItems
            : $this->invoiceItems()->get(['quantity', 'price', 'tax', 'net_value']);

        return round((float) $lines->sum(fn ($line) => $line->grossValue()), 2);
    }

    public function remainingQuantity(): float
    {
        return round(max(0, (float) $this->quantity - $this->billedQuantity()), 3);
    }

    public function remainingAmount(): float
    {
        return round(max(0, $this->orderedAmount() - $this->billedGrossAmount()), 2);
    }

    /**
     * Build one invoice line without billing more quantity or a higher rate than the work order allows.
     * When quantity is already fully billed, the saved quantity is 0 so invoice quantities do not add up past the work order.
     *
     * @return array{quantity: float, price: float, gross: float}|null
     */
    public function buildInvoiceLine(float $qty, float $rate): ?array
    {
        $woRate = round(max(0, (float) $this->price), 2);
        $rate = round(min(max(0, $rate), $woRate), 2);
        $remainingQty = $this->remainingQuantity();
        $remainingAmount = $this->remainingAmount();
        $ordered = (float) $this->quantity;
        $qty = round(min(max(0, $qty), $remainingQty), 3);

        if ($remainingQty <= 0.0001) {
            $gross = round(min($remainingAmount, $ordered * $rate), 2);
            if ($gross <= 0.009) {
                return null;
            }

            $storedRate = $ordered > 0 ? round(min($woRate, $gross / $ordered), 2) : $gross;

            return [
                'quantity' => 0.0,
                'price' => $storedRate,
                'gross' => $gross,
            ];
        }

        if ($qty <= 0) {
            return null;
        }

        $gross = round(min($remainingAmount, $qty * $rate), 2);
        if ($gross <= 0.009) {
            return null;
        }

        return [
            'quantity' => $qty,
            'price' => round(min($woRate, $gross / $qty), 2),
            'gross' => $gross,
        ];
    }

    public static function computeGrossValue(float $quantity, float $rate): float
    {
        return round($quantity * $rate, 2);
    }

    public static function computeTaxValue(float $quantity, float $rate, float $taxPercent): float
    {
        return round(self::computeGrossValue($quantity, $rate) * ($taxPercent / 100), 2);
    }

    public static function computeNetValue(float $quantity, float $price, float $taxPercent): float
    {
        $gross = self::computeGrossValue($quantity, $price);
        $taxAmount = self::computeTaxValue($quantity, $price, $taxPercent);

        return round($gross + $taxAmount, 2);
    }

    public function getGrossValueAttribute(): float
    {
        return self::computeGrossValue((float) $this->quantity, (float) $this->price);
    }

    public function getTaxValueAttribute(): float
    {
        return self::computeTaxValue((float) $this->quantity, (float) $this->price, (float) $this->tax);
    }
}
