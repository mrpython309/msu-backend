<?php

namespace App\Services\Procurement;

use App\AssetCondition;
use App\Enums\Procurement\GrnStatus;
use App\Enums\Procurement\PurchaseOrderStatus;
use App\Enums\Procurement\PurchaseRequisitionStatus;
use App\Models\Grn;
use App\Models\GrnDocument;
use App\Models\GrnHistory;
use App\Models\GrnItem;
use App\Models\PoItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseRequisition;
use App\Models\User;
use App\Services\Procurement\Exceptions\ProcurementException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class GrnService
{
    public function __construct(
        private readonly DocumentNumberGenerator $numbers,
        private readonly GrnAssetConversionService $assetConversion,
        private readonly ProcurementActivityLogger $logger,
        private readonly ProcurementNotificationService $notifications,
    ) {
    }

    public function createFromPurchaseOrder(PurchaseOrder $po, User $receiver, array $data): Grn
    {
        return DB::transaction(function () use ($po, $receiver, $data) {
            $lockedPo = PurchaseOrder::query()
                ->with(['items.indentItem', 'items.grnItems', 'purchaseRequisition.indent', 'vendor'])
                ->whereKey($po->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (in_array($lockedPo->status, [
                PurchaseOrderStatus::Cancelled,
                PurchaseOrderStatus::Closed,
            ], true)) {
                throw new ProcurementException('Cannot create GRN for a cancelled or closed purchase order.');
            }

            if (empty($data['items']) || ! is_array($data['items'])) {
                throw new ProcurementException('GRN items are required.');
            }

            $asDraft = (bool) ($data['save_as_draft'] ?? false);
            $initialStatus = $asDraft ? GrnStatus::Draft : GrnStatus::PendingInspection;

            $grn = Grn::create([
                'grn_no' => $this->numbers->next('grn', Grn::class, 'grn_no'),
                'po_id' => $lockedPo->id,
                'vendor_id' => $lockedPo->vendor_id,
                'received_date' => $data['received_date'] ?? now()->toDateString(),
                'received_by' => !empty($data['received_by']) ? $data['received_by'] : $receiver->id,
                'received_by_phone' => $data['received_by_phone'] ?? null,
                'delivered_by' => $data['delivered_by'] ?? null,
                'delivered_by_phone' => $data['delivered_by_phone'] ?? null,
                'vehicle_no' => $data['vehicle_no'] ?? null,
                'warehouse' => $data['warehouse'] ?? $lockedPo->grn_store,
                'invoice_number' => $data['invoice_number'] ?? null,
                'invoice_date' => $data['invoice_date'] ?? null,
                'bill_date' => $data['bill_date'] ?? $data['invoice_date'] ?? null,
                'bill_amount' => $data['bill_amount'] ?? null,
                'challan_number' => $data['challan_number'] ?? null,
                'challan_date' => $data['challan_date'] ?? null,
                'delivery_memo_no' => $data['delivery_memo_no'] ?? null,
                'delivery_memo_date' => $data['delivery_memo_date'] ?? null,
                'gate_entry_no' => $data['gate_entry_no'] ?? null,
                'gate_entry_date' => $data['gate_entry_date'] ?? null,
                'cost_center_name' => $data['cost_center_name'] ?? $lockedPo->purchaseRequisition?->fan_no,
                'department_name' => $lockedPo->department_name ?? $lockedPo->purchaseRequisition?->indent?->department?->department_name,
                'po_type' => $lockedPo->po_type,
                'characteristic' => $data['characteristic'] ?? null,
                'inspection_status' => 'pending',
                'status' => $initialStatus,
                'invoice_path' => $data['invoice_path'] ?? null,
                'delivery_challan_path' => $data['delivery_challan_path'] ?? null,
                'remarks' => $data['remarks'] ?? null,
            ]);

            $poItems = $lockedPo->items->keyBy('id');

            foreach ($data['items'] as $itemData) {
                $this->createGrnItemFromPayload($grn, $poItems, $itemData);
            }

            $this->attachUploadedDocuments($grn, $data, $receiver);
            $this->recordHistory($grn, 'created', null, $initialStatus->value, $receiver, $data['remarks'] ?? null);
            $this->logger->log($grn, 'GRN Created', null, $initialStatus->value, $data['remarks'] ?? null, [
                'po_id' => $lockedPo->id,
                'po_no' => $lockedPo->po_no,
            ], $receiver->id);

            $this->syncPurchaseOrderReceiptStatus($lockedPo);

            try {
                $this->notifications->notifyGrnCreated($grn, $receiver);
            } catch (\Throwable) {
                // Notifications must never block GRN creation.
            }

            // Leave non-draft GRNs in Pending Inspection — inspect/approve is a separate step.
            return $grn->fresh(['items', 'documents', 'histories', 'purchaseOrder', 'vendor', 'receivedBy']);
        });
    }

    /**
     * Idempotent: create asset records for accepted GRN lines when not yet generated.
     */
    public function ensureAssetsGenerated(Grn $grn, ?User $actor = null): Grn
    {
        $grn->loadMissing(['items.poItem.indentItem.category', 'items.assets', 'purchaseOrder', 'vendor']);

        if (! $grn->statusEnum()?->allowsAssetGeneration()) {
            return $grn;
        }

        $this->assetConversion->generateFromGrn($grn, $actor);

        return $grn->fresh(['items.assets', 'items.poItem', 'purchaseOrder', 'vendor']);
    }

    /**
     * Backfill assets for all approved GRNs that still have missing asset-type units.
     *
     * @return array{grns_processed: int, assets_created: int}
     */
    public function syncApprovedGrnAssets(?User $actor = null): array
    {
        $this->assetConversion->normalizeGrnAssetWarehouse($actor);

        $grns = Grn::query()
            ->whereIn('status', [
                GrnStatus::Accepted->value,
                GrnStatus::PartiallyAccepted->value,
                GrnStatus::Closed->value,
            ])
            ->with(['items.poItem.indentItem.category', 'items.assets'])
            ->orderBy('id')
            ->get();

        $processed = 0;
        $created = 0;

        foreach ($grns as $grn) {
            if (! $this->grnHasAssetTypeAcceptedLines($grn)) {
                continue;
            }

            $before = $this->countGrnAssets($grn);

            try {
                $this->ensureAssetsGenerated($grn, $actor);
            } catch (ProcurementException) {
                continue;
            }

            $grn->load(['items.assets']);
            $after = $this->countGrnAssets($grn);

            $processed++;
            $created += max(0, $after - $before);
        }

        return [
            'grns_processed' => $processed,
            'assets_created' => $created,
        ];
    }

    private function grnHasAssetTypeAcceptedLines(Grn $grn): bool
    {
        foreach ($grn->items as $item) {
            if ((int) $item->accepted_quantity < 1) {
                continue;
            }

            if ($item->poItem?->resolvedItemType()?->createsAssetRecords()) {
                return true;
            }
        }

        return false;
    }

    private function countGrnAssets(Grn $grn): int
    {
        return (int) $grn->items->flatMap->assets->unique('id')->count();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function inspectionPayloadFromGrn(Grn $grn): array
    {
        return $grn->items->map(function (GrnItem $item) {
            $received = (int) $item->received_quantity;
            $rejected = (int) $item->rejected_quantity;
            $accepted = (int) $item->accepted_quantity ?: max(0, $received - $rejected);

            return [
                'grn_item_id' => $item->id,
                'received_quantity' => $received,
                'rejected_quantity' => $rejected,
                'accepted_quantity' => $accepted,
                'remarks' => $item->remarks,
            ];
        })->values()->all();
    }

    public function approve(
        Grn $grn,
        User $approver,
        array $itemInspections,
        string $inspectionStatus = 'passed',
        bool $generateAssets = true,
        ?string $comments = null,
        ?string $inspectionRemarks = null,
    ): Grn {
        return DB::transaction(function () use ($grn, $approver, $itemInspections, $inspectionStatus, $generateAssets, $comments, $inspectionRemarks) {
            $locked = Grn::query()
                ->with(['items.poItem.indentItem', 'purchaseOrder.purchaseRequisition.indent'])
                ->whereKey($grn->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! in_array($locked->statusEnum(), [GrnStatus::PendingInspection, GrnStatus::UnderInspection, GrnStatus::Draft], true)) {
                throw new ProcurementException('This GRN cannot be approved in its current status.');
            }

            $from = $locked->status;
            $itemsById = $locked->items->keyBy('id');
            $assetsCreated = 0;

            foreach ($itemInspections as $inspection) {
                $itemId = $inspection['grn_item_id'] ?? null;
                if (! $itemsById->has($itemId)) {
                    throw new ProcurementException('Invalid GRN item for approval.');
                }

                /** @var GrnItem $item */
                $item = GrnItem::query()->whereKey($itemId)->lockForUpdate()->firstOrFail();

                $received = max(0, (int) ($inspection['received_quantity'] ?? $item->received_quantity));
                $rejected = max(0, (int) ($inspection['rejected_quantity'] ?? 0));

                if ($rejected > $received) {
                    throw new ProcurementException('Reject quantity cannot exceed received quantity.');
                }

                $accepted = array_key_exists('accepted_quantity', $inspection)
                    ? max(0, (int) $inspection['accepted_quantity'])
                    : max(0, $received - $rejected);

                if ($accepted > $received) {
                    throw new ProcurementException('Accepted quantity cannot exceed received quantity.');
                }

                if (($accepted + $rejected) > $received) {
                    throw new ProcurementException('Accepted + rejected quantity cannot exceed received quantity.');
                }

                $stockBefore = (float) ($item->stock_in_hand ?? 0);
                $stockAfter = $stockBefore + $accepted;

                $item->update([
                    'received_quantity' => $received,
                    'accepted_quantity' => $accepted,
                    'rejected_quantity' => $rejected,
                    'stock_after' => $stockAfter,
                    'remarks' => $inspection['remarks'] ?? $item->remarks,
                    'inspected_by' => $approver->id,
                    'inspected_at' => now(),
                ]);
            }

            $locked->refresh()->load('items.poItem');

            $totalAccepted = (int) $locked->items->sum('accepted_quantity');
            $totalRejected = (int) $locked->items->sum('rejected_quantity');
            $totalReceived = (int) $locked->items->sum('received_quantity');

            $status = match (true) {
                strtolower($inspectionStatus) === 'failed' || ($totalAccepted === 0 && $totalRejected > 0) => GrnStatus::Rejected,
                $totalAccepted > 0 && $totalAccepted < $totalReceived => GrnStatus::PartiallyAccepted,
                $totalAccepted > 0 => GrnStatus::Accepted,
                default => GrnStatus::UnderInspection,
            };

            $locked->update([
                'status' => $status->value,
                'inspection_status' => strtolower($inspectionStatus),
                'inspection_remarks' => $inspectionRemarks,
                'inspected_by' => $approver->id,
                'inspected_at' => now(),
                'approved_by' => $approver->id,
                'accepted_at' => in_array($status, [GrnStatus::Accepted, GrnStatus::PartiallyAccepted], true) ? now() : null,
            ]);

            $this->recordHistory($locked, 'approved', $from, $status->value, $approver, $comments ?? $inspectionRemarks);
            $this->logger->log($locked, 'GRN Approved', $from, $status->value, $comments ?? $inspectionRemarks, [
                'inspection_status' => $inspectionStatus,
            ], $approver->id);

            if ($generateAssets && $status->allowsAssetGeneration()) {
                $locked->refresh()->load([
                    'items.poItem.indentItem.category',
                    'items.poItem.indentItem.indent',
                    'items.poItem.purchaseOrder',
                    'purchaseOrder',
                    'vendor',
                ]);

                try {
                    $result = $this->assetConversion->generateFromGrn($locked, $approver);
                    $assetsCreated = $result['assets']->count();
                    $this->logger->log($locked, 'Asset Created', $status->value, $status->value, null, [
                        'asset_count' => $assetsCreated,
                    ], $approver->id);
                } catch (\Throwable $e) {
                    report($e);
                    $this->logger->log($locked, 'Asset Creation Failed', $status->value, $status->value, $e->getMessage(), [
                        'error' => $e->getMessage(),
                    ], $approver->id);
                    // Keep GRN accepted; assets can be backfilled from GRN show / assets index sync.
                }
            }

            $consumableCount = $this->logConsumableReceipts($locked, $approver);

            if ($locked->purchaseOrder) {
                $this->syncPurchaseOrderReceiptStatus($locked->purchaseOrder);
            }

            // After GRN acceptance / asset transfer, mark linked PR as Completed when PO is fully received.
            if (in_array($status, [GrnStatus::Accepted, GrnStatus::PartiallyAccepted], true)) {
                $this->syncPurchaseRequisitionCompletion($locked, $assetsCreated, $consumableCount, $approver);
            }

            try {
                $this->notifications->notifyGrnApproved($locked, $approver, $assetsCreated);
            } catch (\Throwable) {
                // Notifications must never block GRN approval.
            }

            return $locked->fresh(['items.assets', 'documents', 'histories', 'purchaseOrder', 'approvedBy']);
        });
    }

    /** @deprecated Use approve() */
    public function inspect(
        Grn $grn,
        User $inspector,
        array $itemInspections,
        bool $generateAssets = true,
        ?string $comments = null,
    ): Grn {
        return $this->approve($grn, $inspector, $itemInspections, 'passed', $generateAssets, $comments);
    }

    public function attachDocument(Grn $grn, array $document, ?User $uploader = null): GrnDocument
    {
        $allowed = config('procurement.grn_document_types', []);
        $type = $document['document_type'] ?? 'other';

        if ($allowed && ! in_array($type, $allowed, true)) {
            throw new ProcurementException("Unsupported GRN document type [{$type}].");
        }

        $row = GrnDocument::create([
            'grn_id' => $grn->id,
            'document_type' => $type,
            'path' => $document['path'],
            'original_name' => $document['original_name'] ?? null,
            'mime_type' => $document['mime_type'] ?? null,
            'file_size' => $document['file_size'] ?? null,
            'uploaded_by' => $uploader?->id,
            'remarks' => $document['remarks'] ?? null,
        ]);

        $this->recordHistory($grn, 'document_attached', $grn->status, $grn->status, $uploader, null, [
            'document_id' => $row->id,
            'document_type' => $type,
        ]);

        return $row;
    }

    /**
     * @param  Collection<int, PoItem>|array<string, PoItem>  $poItems
     * @param  array<string, mixed>  $itemData
     */
    private function createGrnItemFromPayload(Grn $grn, Collection|array $poItems, array $itemData): GrnItem
    {
        $poItems = $poItems instanceof Collection ? $poItems : collect($poItems);
        $poItem = $poItems->get($itemData['po_item_id'] ?? null);

        if (! $poItem) {
            throw new ProcurementException('Invalid PO item for GRN.');
        }

        $received = max(0, (int) ($itemData['received_quantity'] ?? 0));
        $rejected = max(0, (int) ($itemData['rejected_quantity'] ?? 0));

        if ($rejected > $received) {
            throw new ProcurementException("Reject quantity for [{$poItem->item_name}] exceeds received quantity.");
        }

        $accepted = max(0, $received - $rejected);
        $alreadyReceived = (int) $poItem->grnItems()->sum('received_quantity');
        $remaining = max(0, ((int) $poItem->quantity + (float) $poItem->free_qty) - $alreadyReceived);

        if ($received > $remaining) {
            throw new ProcurementException(
                "Received quantity for [{$poItem->item_name}] ({$received}) exceeds remaining open quantity ({$remaining})."
            );
        }

        $stockBefore = (float) ($poItem->indentItem?->stock_in_hand ?? $poItem->getRawOriginal('qoh') ?? 0);
        $stockAfter = $stockBefore + $accepted;

        return GrnItem::create([
            'grn_id' => $grn->id,
            'po_item_id' => $poItem->id,
            'ordered_quantity' => $poItem->quantity,
            'unit_rate' => (float) $poItem->price,
            'received_quantity' => $received,
            'accepted_quantity' => $accepted,
            'rejected_quantity' => $rejected,
            'free_qty' => $itemData['free_qty'] ?? $poItem->free_qty ?? 0,
            'item_code' => $poItem->item_code,
            'hsn_code' => $poItem->hsn_code,
            'manufacturer' => $poItem->manufacturer ?? $poItem->indentItem?->manufacturer,
            'marketer_name' => $poItem->marketer_name,
            'purchase_unit' => $poItem->purchase_unit ?? $poItem->unit,
            'brand_name' => $itemData['brand_name'] ?? null,
            'model_name' => $itemData['model_name'] ?? null,
            'batch_number' => $itemData['batch_number'] ?? null,
            'serial_numbers' => $itemData['serial_numbers'] ?? null,
            'remarks' => $itemData['remarks'] ?? null,
            'stock_in_hand' => $stockBefore,
            'stock_after' => $stockAfter,
            'condition' => $this->normalizeCondition($itemData['condition'] ?? null),
            'accessories' => $itemData['accessories'] ?? null,
        ]);
    }

    private function attachUploadedDocuments(Grn $grn, array $data, User $receiver): void
    {
        if (! empty($data['invoice_path'])) {
            $this->attachDocument($grn, [
                'document_type' => 'invoice',
                'path' => $data['invoice_path'],
            ], $receiver);
        }

        if (! empty($data['delivery_challan_path'])) {
            $this->attachDocument($grn, [
                'document_type' => 'delivery_challan',
                'path' => $data['delivery_challan_path'],
            ], $receiver);
        }
    }

    public function syncPoAndPrAfterGrnUpdate(Grn $grn, int $assetsCreated = 0, int $consumableCount = 0, ?User $actor = null): void
    {
        if ($grn->purchaseOrder) {
            $this->syncPurchaseOrderReceiptStatus($grn->purchaseOrder);
        }

        $user = $actor ?? $grn->inspectedBy ?? $grn->receivedBy ?? User::query()->first();
        if ($user && in_array($grn->statusEnum(), [GrnStatus::Accepted, GrnStatus::PartiallyAccepted], true)) {
            $this->syncPurchaseRequisitionCompletion($grn, $assetsCreated, $consumableCount, $user);
        }
    }

    public function syncPurchaseOrderReceiptStatus(PurchaseOrder $po): void
    {
        $po->load('items.grnItems');
        $fullyReceived = $po->items->every(
            fn ($poItem) => (int) $poItem->grnItems->sum('accepted_quantity') >= ((int) $poItem->quantity + (float) $poItem->free_qty)
        );

        $po->update([
            'status' => $fullyReceived
                ? PurchaseOrderStatus::Received
                : PurchaseOrderStatus::PartiallyReceived,
        ]);
    }

    /**
     * Mark PR Completed once GRN goods are transferred to assets (or fully received).
     */
    private function syncPurchaseRequisitionCompletion(
        Grn $grn,
        int $assetsCreated,
        int $consumableCount,
        User $actor,
    ): void {
        $po = $grn->purchaseOrder;
        if (! $po) {
            return;
        }

        $po->refresh()->load(['items.grnItems', 'purchaseRequisition']);
        $pr = $po->purchaseRequisition;
        if (! $pr) {
            return;
        }

        $fullyReceived = $po->items->isNotEmpty() && $po->items->every(
            fn ($poItem) => (int) $poItem->grnItems->sum('accepted_quantity') >= ((int) $poItem->quantity + (float) $poItem->free_qty)
        );

        // Complete after asset transfer, or when the full PO quantity is accepted on GRN.
        $shouldComplete = $assetsCreated > 0 || ($fullyReceived && ($assetsCreated > 0 || $consumableCount > 0 || $grn->statusEnum() === GrnStatus::Accepted));

        if (! $shouldComplete) {
            return;
        }

        $lockedPr = PurchaseRequisition::query()
            ->whereKey($pr->getKey())
            ->lockForUpdate()
            ->first();

        if (! $lockedPr || $lockedPr->status === PurchaseRequisitionStatus::Completed) {
            return;
        }

        if (in_array($lockedPr->status, [
            PurchaseRequisitionStatus::Closed,
            PurchaseRequisitionStatus::Rejected,
            PurchaseRequisitionStatus::Cancelled,
        ], true)) {
            return;
        }

        $from = $lockedPr->status instanceof PurchaseRequisitionStatus
            ? $lockedPr->status->value
            : (string) $lockedPr->status;

        $lockedPr->update(['status' => PurchaseRequisitionStatus::Completed]);

        $this->logger->log($lockedPr, 'PR Completed', $from, PurchaseRequisitionStatus::Completed->value, 'Completed after GRN transfer to assets.', [
            'grn_id' => $grn->id,
            'grn_no' => $grn->grn_no,
            'po_id' => $po->id,
            'assets_created' => $assetsCreated,
        ], $actor->id);
    }

    private function logConsumableReceipts(Grn $grn, User $actor): int
    {
        $count = 0;

        foreach ($grn->items as $item) {
            if ($item->poItem && ! $item->poItem->resolvedItemType()->createsAssetRecords()) {
                $count++;
                $this->logger->log($grn, 'Inventory Updated', $grn->status?->value, $grn->status?->value, null, [
                    'grn_item_id' => $item->id,
                    'item_name' => $item->poItem->item_name,
                    'accepted_quantity' => $item->accepted_quantity,
                    'stock_before' => $item->stock_in_hand,
                    'stock_after' => $item->stock_after,
                ], $actor->id);
            }
        }

        return $count;
    }

    private function recordHistory(
        Grn $grn,
        string $action,
        null|string|GrnStatus $fromStatus,
        null|string|GrnStatus $toStatus,
        ?User $user,
        ?string $comments = null,
        array $meta = [],
    ): GrnHistory {
        return GrnHistory::create([
            'grn_id' => $grn->id,
            'action' => $action,
            'from_status' => $fromStatus instanceof GrnStatus ? $fromStatus->value : $fromStatus,
            'to_status' => $toStatus instanceof GrnStatus ? $toStatus->value : $toStatus,
            'user_id' => $user?->id,
            'comments' => $comments,
            'meta' => $meta ?: null,
        ]);
    }

    private function normalizeCondition(null|string|AssetCondition $condition): ?string
    {
        if ($condition instanceof AssetCondition) {
            return $condition->value;
        }

        if ($condition === null || $condition === '') {
            return null;
        }

        return AssetCondition::tryFrom(strtolower(trim((string) $condition)))?->value;
    }
}
