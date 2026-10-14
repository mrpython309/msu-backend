<?php

namespace App\Services\Procurement;

use App\Enums\Procurement\NegotiationStatus;
use App\Enums\Procurement\PurchaseRequisitionStatus;
use App\Enums\Procurement\RfqStatus;
use App\Models\QuotationNegotiationHistory;
use App\Models\Rfq;
use App\Models\RfqVendor;
use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorQuotation;
use App\Models\VendorQuotationItem;
use App\Services\Procurement\Exceptions\ProcurementException;
use Illuminate\Support\Facades\DB;

class VendorQuotationService
{
    public function __construct(
        private readonly ProcurementActivityLogger $logger,
    ) {
    }

    /**
     * Upsert a vendor quotation for an RFQ (unique rfq_id + vendor_id).
     *
     * @param  array{
     *     contact_person?: ?string,
     *     contact_phone?: ?string,
     *     contact_email?: ?string,
     *     rating_snapshot?: float|int|null,
     *     price?: float|int,
     *     delivery_days?: int|null,
     *     warranty?: ?string,
     *     tax?: float|int,
     *     shipping?: float|int,
     *     total_cost?: float|int|null,
     *     notes?: ?string,
     *     items?: list<array{
     *         indent_item_id?: int|null,
     *         item_name?: ?string,
     *         quantity?: int,
     *         unit?: ?string,
     *         unit_price?: float|int,
     *         tax?: float|int,
     *         total?: float|int|null,
     *         notes?: ?string
     *     }>
     * }  $data
     */
    public function recordQuotation(Rfq $rfq, Vendor|int $vendor, array $data, ?User $actor = null): VendorQuotation
    {
        $vendorId = $vendor instanceof Vendor ? (int) $vendor->id : (int) $vendor;

        return DB::transaction(function () use ($rfq, $vendorId, $data, $actor) {
            $lockedRfq = Rfq::query()->whereKey($rfq->getKey())->lockForUpdate()->firstOrFail();

            if ($lockedRfq->status !== RfqStatus::Open) {
                throw new ProcurementException('Quotations can only be recorded against an open RFQ.');
            }

            RfqVendor::query()->firstOrCreate(
                ['rfq_id' => $lockedRfq->id, 'vendor_id' => $vendorId],
                ['status' => 'Quoted', 'invited_at' => now()]
            );

            RfqVendor::query()
                ->where('rfq_id', $lockedRfq->id)
                ->where('vendor_id', $vendorId)
                ->update(['status' => 'Quoted']);

            $items = $data['items'] ?? [];
            $price = (float) ($data['price'] ?? 0);
            $tax = (float) ($data['tax'] ?? 0);
            $shipping = (float) ($data['shipping'] ?? 0);

            if ($items !== []) {
                $itemsTotal = collect($items)->sum(function (array $item) {
                    $qty = (int) ($item['quantity'] ?? 1);
                    $unitPrice = (float) ($item['unit_price'] ?? 0);
                    $itemTax = (float) ($item['tax'] ?? 0);

                    return ($qty * $unitPrice) + $itemTax;
                });
                $price = $price > 0 ? $price : (float) $itemsTotal;
            }

            $totalCost = array_key_exists('total_cost', $data) && $data['total_cost'] !== null
                ? (float) $data['total_cost']
                : $price + $tax + $shipping;

            $quotation = VendorQuotation::query()
                ->where('rfq_id', $lockedRfq->id)
                ->where('vendor_id', $vendorId)
                ->lockForUpdate()
                ->first();

            $payload = [
                'contact_person' => $data['contact_person'] ?? null,
                'contact_phone' => $data['contact_phone'] ?? null,
                'contact_email' => $data['contact_email'] ?? null,
                'rating_snapshot' => $data['rating_snapshot'] ?? null,
                'price' => $price,
                'delivery_days' => $data['delivery_days'] ?? null,
                'warranty' => $data['warranty'] ?? null,
                'tax' => $tax,
                'shipping' => $shipping,
                'total_cost' => $totalCost,
                'notes' => $data['notes'] ?? null,
                'quoted_at' => now(),
            ];

            if ($quotation) {
                // Original total_cost must never be overwritten after first record.
                unset($payload['total_cost']);
                $quotation->update($payload);
            } else {
                $quotation = VendorQuotation::create(array_merge($payload, [
                    'rfq_id' => $lockedRfq->id,
                    'vendor_id' => $vendorId,
                    'is_selected' => false,
                    'negotiation_status' => NegotiationStatus::Pending->value,
                ]));
            }

            if ($items !== []) {
                $lockedRfq->loadMissing('purchaseRequisition.indent.items');
                $quotation->setRelation('rfq', $lockedRfq);
                $this->syncItems($quotation, $items);
            }

            $pr = $lockedRfq->purchaseRequisition()->lockForUpdate()->first();
            if ($pr && in_array($pr->status, [
                PurchaseRequisitionStatus::Pending,
                PurchaseRequisitionStatus::PendingPurchase,
                PurchaseRequisitionStatus::InProgress,
            ], true)) {
                $pr->update(['status' => PurchaseRequisitionStatus::Quoted]);
            }

            $this->logger->log($quotation, 'Quotation Received', null, null, $data['notes'] ?? null, [
                'rfq_id' => $lockedRfq->id,
                'vendor_id' => $vendorId,
            ], $actor?->id);

            return $quotation->fresh(['items', 'vendor']);
        });
    }

    public function selectQuotation(VendorQuotation $quotation, User $actor): VendorQuotation
    {
        return DB::transaction(function () use ($quotation, $actor) {
            $locked = VendorQuotation::query()
                ->whereKey($quotation->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $rfq = Rfq::query()->whereKey($locked->rfq_id)->lockForUpdate()->firstOrFail();

            if ($rfq->status === RfqStatus::Cancelled) {
                throw new ProcurementException('Cannot select a quotation for a cancelled RFQ.');
            }

            VendorQuotation::query()
                ->where('rfq_id', $rfq->id)
                ->where('id', '!=', $locked->id)
                ->where('is_selected', true)
                ->update([
                    'is_selected' => false,
                    'selected_at' => null,
                    'selected_by' => null,
                ]);

            $locked->update([
                'is_selected' => true,
                'selected_at' => now(),
                'selected_by' => $actor->id,
            ]);

            $rfq->update(['status' => RfqStatus::Awarded]);

            \App\Models\RfqVendor::query()
                ->where('rfq_id', $rfq->id)
                ->where('status', '!=', 'Quoted')
                ->update(['status' => 'Cancelled']);

            $this->logger->log($locked, 'Vendor Selected', null, null, 'Vendor quotation selected.', [
                'rfq_id' => $rfq->id,
            ], $actor->id);

            return $locked->refresh();
        });
    }

    /**
     * @param  array{
     *     negotiated_total_cost: float|int|string,
     *     buyer_remarks?: ?string,
     *     vendor_response?: ?string,
     *     negotiation_status?: ?string
     * }  $data
     */
    public function saveNegotiation(VendorQuotation $quotation, array $data, User $actor): VendorQuotation
    {
        return DB::transaction(function () use ($quotation, $data, $actor) {
            $locked = VendorQuotation::query()
                ->with('purchaseOrder')
                ->whereKey($quotation->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->isNegotiationLocked()) {
                throw new ProcurementException('Negotiations are locked because a purchase order has already been generated.');
            }

            $status = NegotiationStatus::tryFrom((string) ($data['negotiation_status'] ?? ''))
                ?? $this->inferNegotiationStatus($data);

            $newPrice = round((float) $data['negotiated_total_cost'], 2);
            if ($newPrice < 0) {
                throw new ProcurementException('Negotiated price cannot be negative.');
            }

            $oldPrice = $locked->hasNegotiatedPrice()
                ? (float) $locked->negotiated_total_cost
                : $locked->originalQuote();

            $round = (int) QuotationNegotiationHistory::query()
                ->where('vendor_quotation_id', $locked->id)
                ->max('round') + 1;

            QuotationNegotiationHistory::create([
                'vendor_quotation_id' => $locked->id,
                'round' => max(1, $round),
                'old_price' => $oldPrice,
                'new_price' => $newPrice,
                'buyer_remarks' => $data['buyer_remarks'] ?? null,
                'vendor_response' => $data['vendor_response'] ?? null,
                'status' => $status->value,
                'negotiated_by' => $actor->id,
                'negotiated_at' => now(),
            ]);

            $locked->update([
                'negotiated_total_cost' => $newPrice,
                'negotiation_status' => $status->value,
                'buyer_remarks' => $data['buyer_remarks'] ?? null,
                'vendor_response' => $data['vendor_response'] ?? null,
            ]);

            if (!empty($data['items']) && is_array($data['items'])) {
                foreach ($data['items'] as $itemData) {
                    $item = VendorQuotationItem::where('quotation_id', $locked->id)->find($itemData['id'] ?? 0);
                    if ($item) {
                        $rate = round((float) ($itemData['negotiated_unit_price'] ?? 0), 2);
                        $item->update([
                            'negotiated_unit_price' => $rate,
                            'negotiated_total' => round($rate * (int) $item->quantity, 2),
                        ]);
                    }
                }
            }

            $this->logger->log($locked, 'Vendor Negotiation Saved', (string) $oldPrice, (string) $newPrice, $data['buyer_remarks'] ?? null, [
                'round' => max(1, $round),
                'status' => $status->value,
            ], $actor->id);

            return $locked->fresh(['vendor', 'negotiationHistories.negotiatedBy', 'purchaseOrder', 'items.indentItem']);
        });
    }

    public function lockNegotiations(VendorQuotation $quotation): void
    {
        VendorQuotation::query()
            ->whereKey($quotation->getKey())
            ->whereNull('negotiations_locked_at')
            ->update([
                'negotiations_locked_at' => now(),
                'negotiation_status' => NegotiationStatus::Closed->value,
            ]);
    }

    /**
     * @param  list<array<string, mixed>>  $items
     */
    private function syncItems(VendorQuotation $quotation, array $items): void
    {
        $keepIds = [];
        $allowedIndentItemIds = $quotation->rfq?->purchaseRequisition?->indent?->items()
            ->pluck('id')
            ->all() ?? [];

        foreach ($items as $item) {
            $indentItemId = $item['indent_item_id'] ?? null;

            if ($indentItemId && $allowedIndentItemIds !== [] && ! in_array((int) $indentItemId, array_map('intval', $allowedIndentItemIds), true)) {
                throw new ProcurementException('Quotation items must belong to the approved indent of this purchase requisition.');
            }

            $qty = max(1, (int) ($item['quantity'] ?? 1));
            $unitPrice = (float) ($item['unit_price'] ?? 0);
            $itemTax = (float) ($item['tax'] ?? 0);
            $total = array_key_exists('total', $item) && $item['total'] !== null
                ? (float) $item['total']
                : ($qty * $unitPrice) + $itemTax;

            $attributes = [
                'item_name' => $item['item_name'] ?? null,
                'quantity' => $qty,
                'unit' => $item['unit'] ?? 'Nos',
                'unit_price' => $unitPrice,
                'tax' => $itemTax,
                'total' => $total,
                'notes' => $item['notes'] ?? null,
            ];

            if ($indentItemId) {
                $row = VendorQuotationItem::query()->updateOrCreate(
                    [
                        'quotation_id' => $quotation->id,
                        'indent_item_id' => $indentItemId,
                    ],
                    $attributes
                );
            } else {
                $row = VendorQuotationItem::create(array_merge($attributes, [
                    'quotation_id' => $quotation->id,
                    'indent_item_id' => null,
                ]));
            }

            $keepIds[] = $row->id;
        }

        VendorQuotationItem::query()
            ->where('quotation_id', $quotation->id)
            ->whereNotIn('id', $keepIds)
            ->delete();
    }

    /**
     * @param  array{buyer_remarks?: ?string, vendor_response?: ?string}  $data
     */
    private function inferNegotiationStatus(array $data): NegotiationStatus
    {
        if (trim((string) ($data['vendor_response'] ?? '')) !== '') {
            return NegotiationStatus::VendorResponded;
        }

        return NegotiationStatus::NegotiationSent;
    }
}
