<?php

namespace App\Enums\Procurement;

enum PurchaseOrderPaymentStatus: string
{
    case Pending = 'Pending';
    case PartiallyPaid = 'Partially Paid';
    case Paid = 'Paid';

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::PartiallyPaid => 'info',
            self::Paid => 'success',
        };
    }

    public static function fromAmounts(float $paid, float $remaining): self
    {
        if ($paid <= 0) {
            return self::Pending;
        }

        if ($remaining <= 0) {
            return self::Paid;
        }

        return self::PartiallyPaid;
    }
}
