<?php

namespace App\Enums\Procurement;

enum GrnStatus: string
{
    case Draft = 'Draft';
    case PendingInspection = 'Pending Inspection';
    case UnderInspection = 'Under Inspection';
    case PartiallyAccepted = 'Partially Accepted';
    case Accepted = 'Accepted';
    case Rejected = 'Rejected';
    case Closed = 'Closed';

    public function label(): string
    {
        return $this->value;
    }

    public function allowsAssetGeneration(): bool
    {
        return in_array($this, [self::Accepted, self::PartiallyAccepted, self::Closed], true);
    }

    public function isEditable(): bool
    {
        return in_array($this, [self::Draft, self::PendingInspection, self::UnderInspection], true);
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Accepted, self::PartiallyAccepted, self::Closed => 'success',
            self::Rejected => 'danger',
            self::Draft => 'secondary',
            default => 'warning',
        };
    }
}
