<?php

namespace App\Services\Procurement;

use App\Models\MsuPurchaseOrder;
use App\Models\MsuPurchaseOrderItem;
use Illuminate\Support\Facades\DB;

/**
 * Updates MSU PO receipt quantities and status from GRN activity.
 * Call applyReceipts() when GRN lines are accepted against an MSU PO.
 */
class MsuPurchaseOrderReceiptService
{
    /**
     * @param  array<int, float|int>  $receivedByItemId  [msu_purchase_order_item_id => received_qty_delta_or_absolute]
     * @param  bool  $absolute  when true, values replace received_quantity; otherwise they are added
     */
    public function applyReceipts(MsuPurchaseOrder $po, array $receivedByItemId, bool $absolute = false): void
    {
        DB::transaction(function () use ($po, $receivedByItemId, $absolute) {
            foreach ($receivedByItemId as $itemId => $qty) {
                $item = MsuPurchaseOrderItem::query()
                    ->where('msu_purchase_order_id', $po->id)
                    ->where('id', $itemId)
                    ->lockForUpdate()
                    ->first();

                if (! $item) {
                    continue;
                }

                $next = $absolute
                    ? (float) $qty
                    : (float) $item->received_quantity + (float) $qty;

                $item->update([
                    'received_quantity' => max(0, min($next, (float) $item->quantity)),
                ]);
            }

            $po->refresh();
            $po->syncStatusFromReceipts();
        });
    }

    public function syncStatus(MsuPurchaseOrder $po): void
    {
        $po->syncStatusFromReceipts();
    }
}
