<?php

namespace App\Enums\Procurement;

enum PurchaseOrderSource: string
{
    case FromPurchaseRequisition = 'from_pr';
    case Direct = 'direct';
    /** @deprecated Use FromPurchaseRequisition */
    case FromIndent = 'from_indent';

    public function label(): string
    {
        return match ($this) {
            self::FromPurchaseRequisition, self::FromIndent => 'From Purchase Requisition',
            self::Direct => 'Direct Purchase Order',
        };
    }
}
