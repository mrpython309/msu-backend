<?php

namespace App\Enums\Procurement;

enum MsuWorkOrderStatus: string
{
    case WoCreated = 'wo_created';
    case Approved = 'approved';
    case PartiallyPassed = 'partially_passed';
    case Passed = 'passed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::WoCreated => 'WO Created',
            self::Approved => 'Approved',
            self::PartiallyPassed => 'Partially Passed',
            self::Passed => 'Passed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::WoCreated => 'primary',
            self::Approved => 'info',
            self::PartiallyPassed => 'warning',
            self::Passed => 'success',
            self::Cancelled => 'danger',
        };
    }

    public function badgeClass(): string
    {
        return $this->color();
    }

    public static function options(): array
    {
        return array_column(self::cases(), 'value', 'value');
    }

    public function canCancel(): bool
    {
        return $this === self::WoCreated;
    }

    public function isEditable(): bool
    {
        return $this === self::WoCreated;
    }

    public function canAddInvoice(): bool
    {
        // After approve (and while partially passed), allow adding invoices.
        return in_array($this, [self::Approved, self::PartiallyPassed], true);
    }
}
