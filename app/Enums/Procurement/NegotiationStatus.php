<?php

namespace App\Enums\Procurement;

enum NegotiationStatus: string
{
    case Pending = 'Pending';
    case NegotiationSent = 'Negotiation Sent';
    case VendorResponded = 'Vendor Responded';
    case Accepted = 'Accepted';
    case Rejected = 'Rejected';
    case Closed = 'Closed';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Pending => 'bg-label-secondary',
            self::NegotiationSent => 'bg-label-info',
            self::VendorResponded => 'bg-label-primary',
            self::Accepted => 'bg-label-success',
            self::Rejected => 'bg-label-danger',
            self::Closed => 'bg-label-dark',
        };
    }
}
