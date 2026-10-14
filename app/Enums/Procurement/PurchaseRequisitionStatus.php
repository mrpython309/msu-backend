<?php

namespace App\Enums\Procurement;

enum PurchaseRequisitionStatus: string
{
    case Pending = 'Pending';
    case PendingPurchase = 'Pending Purchase';
    case PartialProcess = 'Partial Process';
    case InProgress = 'In Progress';
    case Quoted = 'Quoted';
    case Ordered = 'Ordered';
    case Completed = 'Completed';
    case Closed = 'Closed';
    case Rejected = 'Rejected';
    case Cancelled = 'Cancelled';
    case Transferred = 'Transferred';

    public function label(): string
    {
        return match ($this) {
            self::Pending, self::PendingPurchase => 'Pending',
            self::InProgress, self::Quoted, self::Ordered => 'Partial Process',
            self::Completed => 'Completed',
            default => $this->value,
        };
    }

    public function badgeClass(): string
    {
        return match ($this->label()) {
            'Pending' => 'warning',
            'Partial Process', 'Transferred' => 'info',
            'Completed' => 'success',
            'Closed' => 'secondary',
            'Rejected', 'Cancelled' => 'danger',
            default => 'primary',
        };
    }

    public function isAwaitingPurchaseOfficer(): bool
    {
        return in_array($this, [self::Pending, self::PendingPurchase], true);
    }

    public function allowsProcurement(): bool
    {
        return ! in_array($this, [
            self::Completed,
            self::Closed,
            self::Rejected,
            self::Cancelled,
            self::Transferred,
        ], true);
    }

    /** @return list<string> */
    public static function filterOptions(): array
    {
        return ['Pending', 'Partial Process', 'Completed', 'Closed', 'Transferred'];
    }
}
