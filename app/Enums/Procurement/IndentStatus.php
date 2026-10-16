<?php

namespace App\Enums\Procurement;

enum IndentStatus: string
{
    case Draft = 'Draft';
    case Submitted = 'Submitted';
    case PendingApproval = 'Pending Approval';
    case L1Pending = 'L1 Pending';
    case L2Pending = 'L2 Pending';
    case L3Pending = 'L3 Pending';
    case Approved = 'Approved';
    case Rejected = 'Rejected';
    case SentBack = 'Sent Back';
    case ConvertedToPurchaseRequisition = 'Converted to Purchase Requisition';
    case Completed = 'Completed';
    case Closed = 'Closed';

    public function label(): string
    {
        return match ($this) {
            self::ConvertedToPurchaseRequisition => 'Completed',
            self::Closed => 'Completed',
            default => $this->value,
        };
    }

    public function isTerminalSuccess(): bool
    {
        return in_array($this, [
            self::Approved,
            self::ConvertedToPurchaseRequisition,
            self::Completed,
            self::Closed,
        ], true);
    }

    public function isPendingApproval(): bool
    {
        return in_array($this, [
            self::Submitted,
            self::PendingApproval,
            self::L1Pending,
            self::L2Pending,
            self::L3Pending,
        ], true);
    }

    public function level(): ?int
    {
        return match ($this) {
            self::L1Pending => 1,
            self::L2Pending => 2,
            self::L3Pending => 3,
            default => null,
        };
    }

    public static function pendingForLevel(int $level): self
    {
        return match ($level) {
            1 => self::L1Pending,
            2 => self::L2Pending,
            3 => self::L3Pending,
            default => throw new \InvalidArgumentException("Unsupported approval level [{$level}]."),
        };
    }
}
