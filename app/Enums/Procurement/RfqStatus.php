<?php

namespace App\Enums\Procurement;

enum RfqStatus: string
{
    case Draft = 'Draft';
    case Open = 'Open';
    case Closed = 'Closed';
    case Cancelled = 'Cancelled';
    case Awarded = 'Awarded';

    public function label(): string
    {
        return $this->value;
    }

    public function isEditable(): bool
    {
        return $this === self::Draft;
    }

    public function canInviteVendors(): bool
    {
        return in_array($this, [self::Draft, self::Open], true);
    }
}
