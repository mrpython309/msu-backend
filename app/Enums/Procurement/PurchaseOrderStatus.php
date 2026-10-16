<?php

namespace App\Enums\Procurement;

enum PurchaseOrderStatus: string
{
    case Draft = 'Draft';
    case Submitted = 'Submitted';
    case Approved = 'Approved';
    case Sent = 'Sent';
    case Accepted = 'Accepted';
    case Rejected = 'Rejected';
    case Issued = 'Issued';
    case PartiallyDelivered = 'Partially Delivered';
    case PartiallyReceived = 'Partially Received';
    case Completed = 'Completed';
    case Received = 'Received';
    case Cancelled = 'Cancelled';
    case Closed = 'Closed';

    public function label(): string
    {
        return match ($this) {
            self::Approved, self::Received, self::Completed => self::Completed->value,
            self::Issued => self::Sent->value,
            self::PartiallyReceived => self::PartiallyDelivered->value,
            default => $this->value,
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Draft => 'secondary',
            self::Submitted => 'primary',
            self::Approved, self::Accepted, self::Sent, self::Issued, self::Completed, self::Received => 'success',
            self::Cancelled, self::Rejected => 'danger',
            self::Closed => 'info',
            default => 'primary',
        };
    }

    public function isEditable(): bool
    {
        return in_array($this, [self::Draft, self::Submitted], true);
    }

    public function isGrnReady(): bool
    {
        return in_array($this, [
            self::Approved,
            self::Accepted,
            self::Sent,
            self::Issued,
            self::PartiallyDelivered,
            self::PartiallyReceived,
        ], true);
    }

    /** Payments require completed approval (or an already operational post-approval status). */
    public function allowsPayment(): bool
    {
        return ! in_array($this, [
            self::Draft,
            self::Submitted,
            self::Rejected,
            self::Cancelled,
        ], true);
    }

    /** @return list<string> Unique display labels for the status filter (matches status column labels). */
    public static function filterOptions(): array
    {
        $exclude = [
            self::Cancelled->label(),
            self::Closed->label(),
            self::Sent->label(),              // Sent / Issued
            self::Accepted->label(),
            self::PartiallyDelivered->label(), // Partially Delivered / Partially Received
        ];

        $options = [];
        foreach (self::cases() as $case) {
            $label = $case->label();
            if (in_array($label, $exclude, true)) {
                continue;
            }
            $options[$label] = $label;
        }

        return array_values($options);
    }

    /**
     * Resolve a filter selection to all status column values that share that label
     * (e.g. "Completed" → Completed + Received).
     *
     * @return list<string>
     */
    public static function statusesMatchingFilter(string $status): array
    {
        $matched = [];
        foreach (self::cases() as $case) {
            if ($case->value === $status || $case->label() === $status) {
                $matched[] = $case->value;
            }
        }

        return $matched !== [] ? array_values(array_unique($matched)) : [$status];
    }
}
