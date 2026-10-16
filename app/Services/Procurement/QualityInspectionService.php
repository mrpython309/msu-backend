<?php

namespace App\Services\Procurement;

use App\Enums\Procurement\GrnStatus;
use App\Models\Grn;
use App\Models\GrnItem;
use App\Models\QualityInspection;
use App\Models\User;
use App\Services\Procurement\Exceptions\ProcurementException;
use Illuminate\Support\Facades\DB;

class QualityInspectionService
{
    public function __construct(
        private readonly GrnAssetConversionService $assetConversionService,
        private readonly ProcurementActivityLogger $logger,
        private readonly GrnService $grnService,
    ) {
    }

    /**
     * Inspect a single GRN Item, update quantities, trigger asset creation if approved, and record history.
     */
    public function inspectItem(
        GrnItem $item,
        User $inspector,
        string $status,
        ?string $remarks = null,
        ?string $inspectedAt = null,
        ?int $acceptedQty = null,
        ?int $rejectedQty = null
    ): QualityInspection {
        return DB::transaction(function () use ($item, $inspector, $status, $remarks, $inspectedAt, $acceptedQty, $rejectedQty) {
            /** @var GrnItem $lockedItem */
            $lockedItem = GrnItem::query()->with(['grn', 'poItem'])->whereKey($item->getKey())->lockForUpdate()->firstOrFail();
            $grn = $lockedItem->grn;

            if (! empty($lockedItem->inspection_status) && ! in_array($lockedItem->inspection_status, ['Pending', 'Draft', ''], true)) {
                throw new ProcurementException("Item [{$lockedItem->poItem?->item_name}] has already been inspected ({$lockedItem->inspection_status}) and cannot be re-inspected.");
            }

            $validStatuses = ['Approved', 'Rejected', 'Damaged', 'Hold', 'Pending'];
            $normalizedStatus = ucfirst(trim($status));
            if (! in_array($normalizedStatus, $validStatuses, true)) {
                throw new ProcurementException("Invalid inspection status [{$status}]. Allowed values: " . implode(', ', $validStatuses));
            }
            
            $received = (int) $lockedItem->received_quantity;
            
            // Handle partial quantities
            $finalAccepted = 0;
            $finalRejected = 0;
            
            if ($acceptedQty !== null && $rejectedQty !== null) {
                if ($acceptedQty + $rejectedQty !== $received) {
                    throw new ProcurementException("Accepted ({$acceptedQty}) plus Rejected ({$rejectedQty}) quantities must equal the total received quantity ({$received}).");
                }
                $finalAccepted = $acceptedQty;
                $finalRejected = $rejectedQty;
            } else {
                if (in_array($normalizedStatus, ['Approved', 'Passed', 'Accepted'], true)) {
                    $finalAccepted = $received;
                    $finalRejected = 0;
                } elseif (in_array($normalizedStatus, ['Rejected', 'Damaged', 'Failed'], true)) {
                    $finalAccepted = 0;
                    $finalRejected = $received;
                }
            }

            // Create or update inspection audit entry for this item
            $inspection = QualityInspection::updateOrCreate(
                [
                    'grn_id' => $grn->id,
                    'grn_item_id' => $lockedItem->id,
                ],
                [
                    'inspected_by' => $inspector->id,
                    'inspected_at' => $inspectedAt ? \Carbon\Carbon::parse($inspectedAt) : now(),
                    'inspection_status' => $normalizedStatus,
                    'remarks' => $remarks,
                ]
            );

            $freeQty = (float) ($lockedItem->free_qty ?? 0);
            $stockBefore = (float) ($lockedItem->stock_in_hand ?? 0);
            $inspectionTime = $inspection->inspected_at;

            $stockAfter = $stockBefore + $finalAccepted;

            $lockedItem->update([
                'inspection_status' => $normalizedStatus,
                'inspection_remarks' => $remarks,
                'inspected_by' => $inspector->id,
                'inspected_at' => $inspectionTime,
                'accepted_quantity' => $finalAccepted,
                'rejected_quantity' => $finalRejected,
                'stock_after' => $stockAfter,
                'damage_reason' => $normalizedStatus === 'Damaged' ? ($remarks ?: 'Defective / Damaged during quality inspection') : $lockedItem->damage_reason,
                'remarks' => $remarks ?? $lockedItem->remarks,
            ]);

            $this->logger->log($grn, 'grn_item.inspected', null, $normalizedStatus, $remarks, [
                'grn_item_id' => $lockedItem->id,
                'item_name' => $lockedItem->poItem?->item_name,
                'inspection_status' => $normalizedStatus,
            ], $inspector->id);

            $this->updateGrnOverallStatus($grn, $inspector);

            return $inspection;
        });
    }

    /**
     * Inspect multiple GRN Items within a single transaction (supports partial inspections).
     *
     * @param array<int, array{grn_item_id: int, inspection_status: string, remarks?: string, inspected_at?: string}> $itemsData
     */
    public function inspectBatch(Grn $grn, User $inspector, array $itemsData): void
    {
        DB::transaction(function () use ($grn, $inspector, $itemsData) {
            foreach ($itemsData as $data) {
                $itemId = (int) ($data['grn_item_id'] ?? 0);
                $item = GrnItem::query()->where('grn_id', $grn->id)->whereKey($itemId)->firstOrFail();

                $status = $data['inspection_status'] ?? 'Pending';
                $remarks = $data['remarks'] ?? null;
                $inspectedAt = $data['inspected_at'] ?? null;

                $this->inspectItem($item, $inspector, $status, $remarks, $inspectedAt);
            }
        });
    }

    /**
     * Update overall GRN status based on individual item inspection states.
     */
    public function updateGrnOverallStatus(Grn $grn, ?User $actor = null): Grn
    {
        return DB::transaction(function () use ($grn, $actor) {
            /** @var Grn $lockedGrn */
            $lockedGrn = Grn::query()->with('items')->whereKey($grn->getKey())->lockForUpdate()->firstOrFail();

            $items = $lockedGrn->items;
            $total = $items->count();
            if ($total === 0) {
                return $lockedGrn;
            }

            $oldStatus = $lockedGrn->status;

            $approved = $items->filter(fn (GrnItem $item) => $item->isApproved())->count();
            $rejectedOrDamaged = $items->filter(fn (GrnItem $item) => $item->isRejectedOrDamaged())->count();
            $hold = $items->filter(fn (GrnItem $item) => $item->isHold())->count();
            $pending = $items->filter(fn (GrnItem $item) => $item->isPending())->count();

            $newStatusEnum = match (true) {
                $approved === $total => GrnStatus::Accepted,
                $approved > 0 => GrnStatus::PartiallyAccepted,
                $rejectedOrDamaged === $total => GrnStatus::Rejected,
                $pending === $total && $hold === 0 && $approved === 0 && $rejectedOrDamaged === 0 => GrnStatus::PendingInspection,
                default => GrnStatus::UnderInspection,
            };

            $newInspectionStatus = match (true) {
                $approved === $total => 'Approved',
                $approved > 0 => 'Partially Approved',
                $rejectedOrDamaged === $total => 'Rejected',
                default => 'Under Inspection',
            };

            $updates = [
                'status' => $newStatusEnum->value,
                'inspection_status' => $newInspectionStatus,
                'inspected_by' => $actor?->id ?? $lockedGrn->inspected_by,
                'inspected_at' => now(),
            ];

            if (in_array($newStatusEnum, [GrnStatus::Accepted, GrnStatus::PartiallyAccepted], true)) {
                $updates['approved_by'] = $actor?->id ?? $lockedGrn->approved_by;
                $updates['accepted_at'] = $lockedGrn->accepted_at ?? now();
            }

            if ($oldStatus !== $newStatusEnum->value) {
                $lockedGrn->update($updates);

                $this->logger->log($lockedGrn, 'grn.status_updated_via_inspection', $oldStatus, $newStatusEnum->value, null, [
                    'approved' => $approved,
                    'rejected_or_damaged' => $rejectedOrDamaged,
                    'hold' => $hold,
                    'pending' => $pending,
                ], $actor?->id);
            }

            $lockedGrn->refresh();
            $lockedGrn->load(['purchaseOrder.items.grnItems', 'items.poItem', 'items.assets']);

            try {
                $assetsCreated = (int) $lockedGrn->items->flatMap->assets->unique('id')->count();
                $consumablesCount = $lockedGrn->items->filter(fn ($it) => $it->poItem && ! $it->poItem->resolvedItemType()->createsAssetRecords() && (int) $it->accepted_quantity > 0)->count();
                $this->grnService->syncPoAndPrAfterGrnUpdate($lockedGrn, $assetsCreated, $consumablesCount, $actor);
            } catch (\Throwable $e) {
                report($e);
            }

            return $lockedGrn;
        });
    }
}
