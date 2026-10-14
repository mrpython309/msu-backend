<?php

namespace App\Enums\Procurement;

enum IndentApprovalAction: string
{
    case Approved = 'Approved';
    case Rejected = 'Rejected';
    case SentBack = 'Sent Back';
    case Submitted = 'Submitted';

    public function label(): string
    {
        return $this->value;
    }
}
