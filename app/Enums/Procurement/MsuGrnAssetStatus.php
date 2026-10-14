<?php

namespace App\Enums\Procurement;

enum MsuGrnAssetStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending Asset Approval',
            self::Approved => 'Added to Asset',
            self::Cancelled => 'Asset Cancelled',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'warning',
            self::Approved => 'success',
            self::Cancelled => 'danger',
        };
    }

    public function canApprove(): bool
    {
        return $this === self::Pending;
    }

    public function canCancel(): bool
    {
        return $this === self::Pending;
    }
}
