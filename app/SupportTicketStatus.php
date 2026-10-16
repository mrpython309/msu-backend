<?php

namespace App;

enum SupportTicketStatus: string
{
    case OPEN = 'open';
    case ACCEPTED = 'accepted';
    case WORK_IN_PROGRESS = 'work_in_progress';
    case HOLD = 'hold';
    case IN_PURCHASE = 'in_purchase';
    case COMPLETED = 'completed';
    case CLOSED = 'closed';
    case REOPEN = 'reopen';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match($this) {
            self::OPEN => 'Open',
            self::ACCEPTED => 'Accepted',
            self::WORK_IN_PROGRESS => 'Work In Progress',
            self::HOLD => 'On Hold',
            self::IN_PURCHASE => 'In Purchase',
            self::COMPLETED => 'Completed',
            self::CLOSED => 'Closed',
            self::REOPEN => 'Reopen',
            self::REJECTED => 'Rejected',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::OPEN => 'primary',
            self::ACCEPTED => 'info',
            self::WORK_IN_PROGRESS => 'warning',
            self::HOLD => 'secondary',
            self::IN_PURCHASE => 'dark',
            self::COMPLETED => 'success',
            self::CLOSED => 'danger',
            self::REOPEN => 'info',
            self::REJECTED => 'danger',
        };
    }
}
