<?php

namespace App\Services\Procurement;

use App\AssetStatus;
use App\Enums\Procurement\IndentStatus;
use App\Enums\Procurement\PurchaseRequisitionStatus;
use App\Models\Asset;
use App\Models\AssetAssignment;
use App\Models\Branch;
use App\Models\Company;
use App\Models\AssetCustodian;
use App\Models\Indent;
use App\Models\PurchaseRequisition;
use App\Models\User;
use App\Services\Procurement\Exceptions\ProcurementException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class PurchaseRequisitionService
{
    public function __construct(
        private readonly DocumentNumberGenerator $numbers,
        private readonly ProcurementActivityLogger $logger,
        private readonly ProcurementNotificationService $notifications,
    ) {
    }

    /**
     * Idempotent: one PR per indent (enforced by unique indent_id).
     */
    public function createFromApprovedIndent(Indent $indent, ?int $purchaseOfficerId = null): PurchaseRequisition
    {
        $pr = DB::transaction(function () use ($indent, $purchaseOfficerId) {
            $locked = Indent::query()
                ->whereKey($indent->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $existing = PurchaseRequisition::query()
                ->where('indent_id', $locked->id)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                if ($locked->status?->value === 'Approved') {
                    $locked->update(['status' => \App\Enums\Procurement\IndentStatus::ConvertedToPurchaseRequisition]);
                }

                return $existing;
            }

            if (! in_array($locked->status, [
                \App\Enums\Procurement\IndentStatus::Approved,
                \App\Enums\Procurement\IndentStatus::ConvertedToPurchaseRequisition,
                \App\Enums\Procurement\IndentStatus::Completed,
            ], true)) {
                throw new ProcurementException('Purchase requisition can only be created from a fully approved indent.');
            }

            try {
                $pr = PurchaseRequisition::create([
                    'pr_no' => $this->numbers->next('pr', PurchaseRequisition::class, 'pr_no'),
                    'fan_no' => $this->numbers->next('fan', PurchaseRequisition::class, 'fan_no'),
                    'indent_id' => $locked->id,
                    'required_date' => $locked->required_date,
                    'purchase_officer_id' => $purchaseOfficerId,
                    'created_by' => $locked->requested_by,
                    'requisition_store_id' => $locked->to_store_id ?? $locked->from_store_id,
                    'status' => PurchaseRequisitionStatus::PendingPurchase,
                    'created_from_approval_at' => now(),
                    'remarks' => $locked->remarks,
                ]);
            } catch (QueryException $e) {
                if ($this->isUniqueViolation($e, 'purchase_requisitions_indent_id_unique')) {
                    return PurchaseRequisition::query()
                        ->where('indent_id', $locked->id)
                        ->firstOrFail();
                }

                throw $e;
            }

            $this->logger->log(
                $pr,
                'Purchase Requisition Created',
                null,
                PurchaseRequisitionStatus::PendingPurchase->value,
                'Auto-generated from final indent approval.',
                [
                    'indent_id' => $locked->id,
                    'indent_no' => $locked->indent_no,
                    'fan_no' => $pr->fan_no,
                ]
            );

            $locked->update(['status' => \App\Enums\Procurement\IndentStatus::ConvertedToPurchaseRequisition]);

            return $pr;
        });

        $this->notifications->notifyPurchaseOfficers(
            'New Purchase Requisition',
            "PR {$pr->pr_no} is pending and ready for procurement.",
            route('purchase-requisitions.show', $pr)
        );

        return $pr;
    }

    /**
     * Purchase Officer claims / starts processing a PR.
     */
    public function process(PurchaseRequisition $pr, User $officer, ?string $remarks = null): PurchaseRequisition
    {
        return DB::transaction(function () use ($pr, $officer, $remarks) {
            $locked = PurchaseRequisition::query()->whereKey($pr->getKey())->lockForUpdate()->firstOrFail();

            if (! $locked->allowsProcurement()) {
                throw new ProcurementException('This purchase requisition is closed and cannot be processed.');
            }

            if (! $locked->status?->isAwaitingPurchaseOfficer()
                && $locked->status !== PurchaseRequisitionStatus::InProgress) {
                throw new ProcurementException('Only pending requisitions can be processed.');
            }

            $from = $locked->status?->value;
            $locked->update([
                'status' => PurchaseRequisitionStatus::InProgress,
                'purchase_officer_id' => $officer->id,
                'processed_at' => $locked->processed_at ?? now(),
            ]);

            $this->logger->log(
                $locked,
                'Purchase Requisition Processed',
                $from,
                PurchaseRequisitionStatus::InProgress->value,
                $remarks ?? 'Assigned to Purchase Officer for RFQ.',
                ['purchase_officer_id' => $officer->id],
                $officer->id
            );

            return $locked->fresh();
        });
    }

    public function manualClose(PurchaseRequisition $pr, User $officer, string $reason): PurchaseRequisition
    {
        return DB::transaction(function () use ($pr, $officer, $reason) {
            $locked = PurchaseRequisition::query()->whereKey($pr->getKey())->lockForUpdate()->firstOrFail();

            if ($locked->status === PurchaseRequisitionStatus::Closed) {
                throw new ProcurementException('This purchase requisition is already closed.');
            }

            if (in_array($locked->status, [
                PurchaseRequisitionStatus::Rejected,
                PurchaseRequisitionStatus::Cancelled,
            ], true)) {
                throw new ProcurementException('This purchase requisition cannot be manually closed.');
            }

            $from = $locked->status?->value;

            $locked->update([
                'status' => PurchaseRequisitionStatus::Closed,
                'closed_by' => $officer->id,
                'closed_at' => now(),
                'close_reason' => $reason,
            ]);

            $this->logger->log(
                $locked,
                'Purchase Requisition Manually Closed',
                $from,
                PurchaseRequisitionStatus::Closed->value,
                $reason,
                ['closed_by' => $officer->id],
                $officer->id
            );

            return $locked->fresh();
        });
    }

    public function transferFromStore(PurchaseRequisition $pr, User $officer, ?string $remarks = null): PurchaseRequisition
    {
        return DB::transaction(function () use ($pr, $officer, $remarks) {
            $locked = PurchaseRequisition::query()->whereKey($pr->getKey())->lockForUpdate()->firstOrFail();

            if (! $locked->allowsProcurement()) {
                throw new ProcurementException('This purchase requisition is closed and cannot be processed.');
            }

            $locked->load(['indent.items.category', 'indent.fromStore', 'indent.toStore', 'indent.department', 'requisitionStore']);
            $indent = $locked->indent;
            if (! $indent || $indent->items->isEmpty()) {
                throw new ProcurementException('No requested items found to transfer from store.');
            }

            $destinationDepartmentId = $indent->department_id ?? $indent->to_store_id ?? $indent->from_store_id;

            $itemPlans = [];
            $unavailableItems = [];

            foreach ($indent->items as $item) {
                $needed = (float) $item->quantity;
                if ($needed <= 0) {
                    continue;
                }

                $availableAssets = Asset::query()
                    ->whereIn('status', [AssetStatus::AVAILABLE->value, 'available'])
                    ->where(function ($q) use ($item) {
                        $q->where(DB::raw('LOWER(asset_name)'), strtolower(trim($item->item_name)));
                    })
                    ->lockForUpdate()
                    ->get();

                $availableCount = $availableAssets->count();
                $stockInHand = (float) ($item->stock_in_hand ?? 0);

                if ($availableCount < $needed) {
                    $unavailableItems[] = $item->item_name;
                    continue;
                }

                $itemPlans[] = [
                    'item' => $item,
                    'needed' => $needed,
                    'availableAssets' => $availableAssets,
                    'stockInHand' => $stockInHand,
                ];
            }

            if ($unavailableItems !== []) {
                $names = implode(', ', array_unique($unavailableItems));
                throw new ProcurementException('Item is not in assets: '.$names);
            }

            if ($itemPlans === []) {
                throw new ProcurementException('No requested items found to transfer from store.');
            }

            foreach ($itemPlans as $plan) {
                $item = $plan['item'];
                $needed = $plan['needed'];
                $availableAssets = $plan['availableAssets'];
                $stockInHand = $plan['stockInHand'];

                if ($availableAssets->count() > 0) {
                    $transferAssets = $availableAssets->take((int) $needed);
                    $requester = $indent->requestedBy;
                    foreach ($transferAssets as $asset) {
                        $toCompanyId = $requester?->company_id ?? $asset->company_id ?? Company::query()->value('id') ?? 1;
                        $toBranchId = $requester?->branch_id ?? ($requester?->branches()->first()?->id) ?? $asset->branch_id ?? Branch::query()->value('id') ?? 1;

                        $targetStatus = $indent->requested_by ? AssetStatus::ASSIGNED->value : AssetStatus::TRANSFERRED->value;
                        $asset->update([
                            'status' => $targetStatus,
                            'department_id' => $destinationDepartmentId ?? $asset->department_id,
                            'indent_id' => $indent->id,
                        ]);

                        if ($indent->requested_by) {
                            $custodianId = null;
                            if ($requester && ($requester->email || $requester->name)) {
                                $custodianId = AssetCustodian::query()
                                    ->where(function ($query) use ($requester) {
                                        if ($requester->email) {
                                            $query->where('email', $requester->email);
                                        }
                                        if ($requester->name) {
                                            $query->orWhere('employee_name', $requester->name);
                                        }
                                    })
                                    ->value('id');
                            }

                            $asset->assignments()->create([
                                'asset_id' => $asset->id,
                                'assign_date' => now()->toDateString(),
                                'assigned_to' => $custodianId ? 'employee' : 'branch',
                                'assiged_emoloyee' => $custodianId,
                                'assigned_by' => $officer->id,
                                'assigned_at' => now(),
                                'from_company_id' => $asset->company_id,
                                'from_branch' => $asset->branch_id,
                                'from_location' => $asset->location_id,
                                'to_company_id' => $toCompanyId,
                                'to_branch' => $toBranchId,
                                'to_location' => $asset->location_id,
                                'transfer_status' => AssetAssignment::ACCEPTED,
                                'status' => 'assigned',
                                'accepted_by' => $indent->requested_by,
                                'accepted_at' => now(),
                                'accepted_notes' => 'Directly assigned via Transfer from Store (Indent #' . $indent->indent_no . ')',
                                'notes' => 'Direct transfer from store for Indent #' . $indent->indent_no,
                                'assigned_notes' => 'Transferred from store against Purchase Requisition #' . $locked->pr_no,
                            ]);
                        }
                    }
                }

                $item->update([
                    'stock_in_hand' => max(0, $stockInHand - $needed),
                    'facility_stock_in_hand' => (float) ($item->facility_stock_in_hand ?? 0) + $needed,
                    'to_stock_in_hand' => (float) ($item->to_stock_in_hand ?? 0) + $needed,
                ]);
            }

            $from = $locked->status?->value;

            $locked->update([
                'status' => PurchaseRequisitionStatus::Transferred,
                'closed_by' => $officer->id,
                'closed_at' => now(),
                'close_reason' => $remarks ?? 'Procurement stopped: Items transferred directly from store.',
                'processed_at' => $locked->processed_at ?? now(),
            ]);

            $indent->update([
                'status' => IndentStatus::Completed,
            ]);

            $this->logger->log(
                $locked,
                'Items Transferred From Store',
                $from,
                PurchaseRequisitionStatus::Transferred->value,
                $remarks ?? 'Requested items transferred from store; procurement process stopped.',
                ['transferred_by' => $officer->id],
                $officer->id
            );

            return $locked->fresh();
        });
    }

    /**
     * @return array{available: bool, message: string, unavailable_items: array<int, string>}
     */
    public function transferAssetAvailability(PurchaseRequisition $pr): array
    {
        $pr->loadMissing(['indent.items']);
        $indent = $pr->indent;

        if (! $indent || $indent->items->isEmpty()) {
            return [
                'available' => false,
                'message' => 'Item is not in assets',
                'unavailable_items' => [],
            ];
        }

        $unavailableItems = [];

        foreach ($indent->items as $item) {
            $needed = (float) $item->quantity;
            if ($needed <= 0) {
                continue;
            }

            $availableCount = Asset::query()
                ->whereIn('status', [AssetStatus::AVAILABLE->value, 'available'])
                ->where(function ($q) use ($item) {
                    $q->where(DB::raw('LOWER(asset_name)'), strtolower(trim($item->item_name)));
                })
                ->count();

            if ($availableCount < $needed) {
                $unavailableItems[] = $item->item_name;
            }
        }

        if ($unavailableItems === []) {
            return [
                'available' => true,
                'message' => '',
                'unavailable_items' => [],
            ];
        }

        $names = implode(', ', array_unique($unavailableItems));

        return [
            'available' => false,
            'message' => 'Item is not in assets',
            'unavailable_items' => array_values(array_unique($unavailableItems)),
        ];
    }

    private function isUniqueViolation(QueryException $e, string $indexHint): bool
    {
        $message = strtolower($e->getMessage());

        return str_contains($message, 'duplicate') && str_contains($message, strtolower($indexHint));
    }

    public function ensureAllApprovedIndentsHavePRs(): void
    {
        \App\Models\Indent::query()
            ->whereIn('status', [
                \App\Enums\Procurement\IndentStatus::Approved->value,
                \App\Enums\Procurement\IndentStatus::ConvertedToPurchaseRequisition->value,
                \App\Enums\Procurement\IndentStatus::Completed->value,
                'Approved', 'Converted to Purchase Requisition', 'Completed',
            ])
            ->whereDoesntHave('purchaseRequisition')
            ->chunk(50, function ($indents) {
                foreach ($indents as $indent) {
                    try {
                        $this->createFromApprovedIndent($indent);
                    } catch (\Throwable $e) {
                        // Suppress if creation fails for an individual indent
                    }
                }
            });
    }
}
