<?php

namespace App\Enums\Procurement;

enum MsuPurchaseOrderStatus: string
{
    case PoCreated = 'po_created';
    case Approved = 'approved';
    case GrnPartiallyReceived = 'grn_partially_received';
    case GrnReceived = 'grn_received';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PoCreated => 'PO Created',
            self::Approved => 'Approved',
            self::GrnPartiallyReceived => 'GRN Partially Received',
            self::GrnReceived => 'GRN Received',
            self::Cancelled => 'Cancelled',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::PoCreated => 'primary',
            self::Approved => 'info',
            self::GrnPartiallyReceived => 'warning',
            self::GrnReceived => 'success',
            self::Cancelled => 'danger',
        };
    }

    public function isEditable(): bool
    {
        return $this === self::PoCreated;
    }

    public function canCancel(): bool
    {
        return $this === self::PoCreated;
    }

    public function allowsGrn(): bool
    {
        return in_array($this, [self::Approved, self::GrnPartiallyReceived], true);
    }

    /** @return array<string, string> */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }

    public static function fromLegacy(?string $status): self
    {
        return match ($status) {
            'confirmed', 'grn_received', 'Received' => self::GrnReceived,
            'grn_partially_received', 'Partially Received' => self::GrnPartiallyReceived,
            'cancelled', 'Cancelled' => self::Cancelled,
            default => self::PoCreated,
        };
    }
}
