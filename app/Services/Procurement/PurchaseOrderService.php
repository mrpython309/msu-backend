<?php

namespace App\Services\Procurement;

use App\Enums\Procurement\ItemType;
use App\Enums\Procurement\PurchaseOrderSource;
use App\Enums\Procurement\PurchaseOrderStatus;
use App\Enums\Procurement\PurchaseRequisitionStatus;
use App\Enums\Procurement\IndentStatus;
use App\Models\AssetCategory;
use App\Models\Department;
use App\Models\Indent;
use App\Models\PurchaseRequisition;
use App\Models\PoItem;
use App\Models\PurchaseOrder;
use App\Models\User;
use App\Models\VendorQuotation;
use App\Services\Procurement\Exceptions\ProcurementException;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class PurchaseOrderService
{
    public function __construct(
        private readonly DocumentNumberGenerator $numbers,
        private readonly VendorQuotationService $quotations,
        private readonly ProcurementActivityLogger $logger,
        private readonly ProcurementNotificationService $notifications,
    ) {
    }

    /**
     * Idempotent: one PO per selected quotation (unique quotation_id).
     */
    public function createFromSelectedQuotation(
        VendorQuotation $quotation,
        User $actor,
        array $overrides = [],
        bool $selectIfNeeded = true,
    ): PurchaseOrder {
        return DB::transaction(function () use ($quotation, $actor, $overrides, $selectIfNeeded) {
            $lockedQuotation = VendorQuotation::query()
                ->with([
                    'items.indentItem.category',
                    'rfq.purchaseRequisition.indent.items.category',
                    'rfq.purchaseRequisition.indent.department',
                    'vendor',
                ])
                ->whereKey($quotation->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if (! $lockedQuotation->is_selected) {
                if (! $selectIfNeeded) {
                    throw new ProcurementException('Quotation must be selected before creating a purchase order.');
                }

                $this->quotations->selectQuotation($lockedQuotation, $actor);
                $lockedQuotation->refresh()->load([
                    'items.indentItem.category',
                    'rfq.purchaseRequisition.indent.items.category',
                    'rfq.purchaseRequisition.indent.department',
                    'vendor',
                ]);
            }

            $existing = PurchaseOrder::query()
                ->where('quotation_id', $lockedQuotation->id)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return $existing->load('items');
            }

            $pr = $lockedQuotation->rfq?->purchaseRequisition;

            if ($pr && ! $pr->allowsProcurement()) {
                throw new ProcurementException('This purchase requisition is closed. Purchase Order creation is not allowed.');
            }

            $tax = (float) ($overrides['tax'] ?? $lockedQuotation->tax ?? 0);
            $shipping = (float) ($overrides['shipping'] ?? $lockedQuotation->shipping ?? 0);
            $discount = (float) ($overrides['discount'] ?? 0);
            $discountType = (string) ($overrides['discount_type'] ?? null);

            // Prefer negotiated total when present; scale tax/shipping with the quote ratio.
            $originalTotal = $lockedQuotation->originalQuote();
            $effectiveTotal = $lockedQuotation->effectiveQuote();
            $scale = ($originalTotal > 0 && $lockedQuotation->hasNegotiatedPrice())
                ? ($effectiveTotal / $originalTotal)
                : 1.0;

            if ($scale !== 1.0 && ! array_key_exists('tax', $overrides)) {
                $tax = round($tax * $scale, 2);
            }
            if ($scale !== 1.0 && ! array_key_exists('shipping', $overrides)) {
                $shipping = round($shipping * $scale, 2);
            }

            try {
                $po = PurchaseOrder::create([
                    'po_no' => $this->numbers->next('po', PurchaseOrder::class, 'po_no'),
                    'quotation_id' => $lockedQuotation->id,
                    'pr_id' => $pr?->id,
                    'vendor_id' => $lockedQuotation->vendor_id,
                    'purchase_date' => $overrides['purchase_date'] ?? now()->toDateString(),
                    'delivery_date' => $overrides['delivery_date'] ?? null,
                    'grn_store' => $overrides['grn_store'] ?? null,
                    'po_type' => $overrides['po_type']
                        ?? ($pr ? 'with_pr' : 'without_pr'),
                    'department_name' => $overrides['department_name']
                        ?? ($pr?->indent?->department?->department_name),
                    'payment_terms' => $overrides['payment_terms'] ?? null,
                    'currency' => $overrides['currency'] ?? 'INR',
                    'tax' => $tax,
                    'discount' => $discount,
                    'discount_type' => $discountType,
                    'shipping' => $shipping,
                    'grand_total' => 0,
                    // Accepted = ready for store / GRN receipt
                    'status' => PurchaseOrderStatus::Accepted,
                    'created_by' => $actor->id,
                    'remarks' => $overrides['remarks'] ?? null,
                ]);
            } catch (QueryException $e) {
                if ($this->isUniqueViolation($e, 'purchase_orders_quotation_id_unique')) {
                    return PurchaseOrder::query()
                        ->where('quotation_id', $lockedQuotation->id)
                        ->firstOrFail()
                        ->load('items');
                }

                throw $e;
            }

            $lineTotal = $this->createPoItems($po, $lockedQuotation, (array) ($overrides['items'] ?? []), $scale);

            $baseTotal = $lineTotal + $tax + $shipping;
            $discountAmount = match (strtolower($discountType)) {
                'percentage', 'percent', '%' => ($baseTotal * max(0, $discount)) / 100,
                default => $discount,
            };

            $grandTotal = $baseTotal - $discountAmount;

            // If negotiated total exists and no discount override, pin grand total to negotiated amount.
            if ($lockedQuotation->hasNegotiatedPrice() && $discountAmount <= 0.00001) {
                $grandTotal = $effectiveTotal;
            }

            $po->update(['grand_total' => max(0, $grandTotal)]);

            $this->quotations->lockNegotiations($lockedQuotation);

            if ($pr) {
                $pr->update([
                    'vendor_id' => $lockedQuotation->vendor_id,
                    'status' => PurchaseRequisitionStatus::Ordered,
                ]);
            }

            $this->logger->log($po, 'Purchase Order Created', null, PurchaseOrderStatus::Accepted->value, $overrides['remarks'] ?? null, [
                'quotation_id' => $lockedQuotation->id,
                'pr_id' => $pr?->id,
                'negotiated_total' => $lockedQuotation->hasNegotiatedPrice() ? $effectiveTotal : null,
            ], $actor->id);

            $created = $po->fresh('items');

            $this->notifications->notifyGrnTeam(
                'Purchase Order Ready for GRN',
                "PO {$created->po_no} has been accepted and is ready for goods receipt.",
                route('grns.create', $created)
            );

            return $created;
        });
    }

    /**
     * Create a purchase order from the PO module (from PR or direct).
     */
    public function createFromModule(User $actor, array $data, bool $submit = false): PurchaseOrder
    {
        return DB::transaction(function () use ($actor, $data, $submit) {
            $source = PurchaseOrderSource::from($data['po_source']);
            $indent = null;
            $pr = null;

            if (in_array($source, [PurchaseOrderSource::FromPurchaseRequisition, PurchaseOrderSource::FromIndent], true)) {
                $pr = PurchaseRequisition::query()
                    ->with(['indent.items.category', 'indent.department', 'indent.fromStore', 'indent.toStore', 'requisitionStore'])
                    ->whereKey($data['pr_id'] ?? null)
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! PurchaseRequisition::query()->availableForPurchaseOrder()->whereKey($pr->id)->exists()) {
                    throw new ProcurementException('Selected purchase requisition is not available for purchase order creation.');
                }

                if (! $pr->allowsProcurement()) {
                    throw new ProcurementException('This purchase requisition is closed. Purchase order cannot be created.');
                }

                if (empty($data['vendor_id'])) {
                    throw new ProcurementException('Select a supplier before creating a purchase order for the requested indent assets.');
                }

                $indent = $pr->indent;
                $allowedIndentItemIds = $indent?->items?->pluck('id')->map(fn ($id) => (int) $id)->all() ?? [];
                foreach ($data['items'] ?? [] as $row) {
                    $indentItemId = (int) ($row['indent_item_id'] ?? 0);
                    if ($indentItemId > 0 && $allowedIndentItemIds !== [] && ! in_array($indentItemId, $allowedIndentItemIds, true)) {
                        throw new ProcurementException('One or more assets do not belong to the selected purchase requisition.');
                    }
                }
            } elseif (empty($data['vendor_id'])) {
                throw new ProcurementException('Select a supplier before creating a purchase order.');
            }

            $department = Department::query()->find($data['department_id'])
                ?? $indent?->department;

            $store = isset($data['store_id'])
                ? Department::query()->find($data['store_id'])
                : ($pr?->requisitionStore ?? $indent?->toStore ?? $indent?->fromStore);

            $status = PurchaseOrderStatus::Draft;
            $now = now();

            $quotation = null;
            if (!empty($data['quotation_id'])) {
                $quotation = \App\Models\VendorQuotation::find($data['quotation_id']);
            } elseif ($pr && !empty($data['vendor_id'])) {
                $pr->loadMissing('rfqs.quotations');
                $vendorQuotes = $pr->rfqs->flatMap->quotations->where('vendor_id', (int) $data['vendor_id']);
                $quotation = $vendorQuotes->firstWhere('is_selected', true)
                    ?? $vendorQuotes->first(fn ($q) => $q->hasNegotiatedPrice())
                    ?? $vendorQuotes->sortByDesc('id')->first();
            }

            $po = PurchaseOrder::create([
                'po_no' => $this->numbers->next('po', PurchaseOrder::class, 'po_no'),
                'indent_id' => $indent?->id,
                'pr_id' => $pr?->id,
                'quotation_id' => $quotation?->id,
                'negotiated_total' => $quotation?->hasNegotiatedPrice() ? $quotation->effectiveQuote() : null,
                'vendor_id' => $data['vendor_id'],
                'purchase_date' => $data['purchase_date'] ?? now()->toDateString(),
                'delivery_date' => $data['delivery_date'],
                'grn_store' => $data['grn_store'],
                'po_type' => $data['po_type'],
                'po_source' => $source === PurchaseOrderSource::FromIndent
                    ? PurchaseOrderSource::FromPurchaseRequisition
                    : $source,
                'department_id' => $department?->id,
                'department_name' => $data['department_name'] ?? $department?->department_name,
                'store_id' => $store?->id,
                'payment_terms' => $data['payment_terms'] ?? null,
                'minimum_advance_payment_percentage' => (float) ($data['minimum_advance_payment_percentage'] ?? 0),
                'currency' => $data['currency'] ?? 'INR',
                'tax' => (float) ($data['tax'] ?? 0),
                'discount' => (float) ($data['discount'] ?? 0),
                'discount_type' => $data['discount_type'] ?? null,
                'shipping' => (float) ($data['shipping'] ?? 0),
                'grand_total' => 0,
                // Always save as Draft; approval workflow is started by PurchaseOrderApprovalService::submit.
                'status' => $status,
                'created_by' => $actor->id,
                'remarks' => $data['remarks'] ?? null,
                'notes' => $data['notes'] ?? null,
                'submitted_at' => null,
                'approved_at' => null,
            ]);

            $itemsTotal = $this->createModulePoItems($po, $data['items'], $indent, $source);
            $grandTotal = $this->calculateGrandTotal(
                $itemsTotal,
                (float) ($data['tax'] ?? 0),
                (float) ($data['shipping'] ?? 0),
                (float) ($data['discount'] ?? 0),
                (string) ($data['discount_type'] ?? '')
            );

            $po->update(['grand_total' => max(0, $grandTotal)]);

            // PR is marked Ordered when the PO enters the approval workflow (controller / approval service).

            $this->logger->log(
                $po,
                'Purchase Order Draft Saved',
                null,
                $status->value,
                $data['remarks'] ?? $data['notes'] ?? null,
                [
                    'po_source' => $source->value,
                    'pr_id' => $pr?->id,
                    'indent_id' => $indent?->id,
                    'vendor_id' => $data['vendor_id'],
                    'will_submit' => $submit,
                ],
                $actor->id
            );

            return $po->fresh(['items', 'vendor', 'indent', 'department', 'store', 'purchaseRequisition']);
        });
    }

    public function submit(PurchaseOrder $purchaseOrder, User $actor): PurchaseOrder
    {
        return DB::transaction(function () use ($purchaseOrder, $actor) {
            $locked = PurchaseOrder::query()->whereKey($purchaseOrder->getKey())->lockForUpdate()->firstOrFail();

            if ($locked->status !== PurchaseOrderStatus::Draft) {
                throw new ProcurementException('Only draft purchase orders can be submitted.');
            }

            $now = now();
            $locked->update([
                'status' => PurchaseOrderStatus::Submitted,
                'submitted_at' => $now,
            ]);

            if ($locked->indent_id) {
                Indent::query()->whereKey($locked->indent_id)->update([
                    'status' => IndentStatus::Completed,
                    'finalized_at' => $now,
                ]);
            }

            $this->logger->log(
                $locked,
                'Purchase Order Submitted',
                PurchaseOrderStatus::Draft->value,
                PurchaseOrderStatus::Submitted->value,
                'Purchase order submitted for approval.',
                [],
                $actor->id
            );

            return $locked->fresh();
        });
    }

    public function approve(PurchaseOrder $purchaseOrder, User $actor): PurchaseOrder
    {
        return DB::transaction(function () use ($purchaseOrder, $actor) {
            $locked = PurchaseOrder::query()->whereKey($purchaseOrder->getKey())->lockForUpdate()->firstOrFail();

            if (! in_array($locked->status, [PurchaseOrderStatus::Draft, PurchaseOrderStatus::Submitted], true)) {
                throw new ProcurementException('Only draft or submitted purchase orders can be approved.');
            }

            $from = $locked->status?->value;
            $now = now();

            $locked->update([
                'status' => PurchaseOrderStatus::Approved,
                'submitted_at' => $locked->submitted_at ?? $now,
                'approved_at' => $now,
            ]);

            $this->logger->log(
                $locked,
                'Purchase Order Approved',
                $from,
                PurchaseOrderStatus::Approved->value,
                'Purchase order approved and ready for GRN.',
                [],
                $actor->id
            );

            $this->notifications->notifyGrnTeam(
                'Purchase Order Ready for GRN',
                "PO {$locked->po_no} has been approved and is ready for goods receipt.",
                route('grns.create', $locked)
            );

            return $locked->fresh();
        });
    }

    public function update(PurchaseOrder $purchaseOrder, User $actor, array $data): PurchaseOrder
    {
        return DB::transaction(function () use ($purchaseOrder, $actor, $data) {
            $locked = PurchaseOrder::query()->whereKey($purchaseOrder->getKey())->lockForUpdate()->firstOrFail();

            if (in_array($locked->status, [PurchaseOrderStatus::Cancelled, PurchaseOrderStatus::Completed, PurchaseOrderStatus::Closed, PurchaseOrderStatus::Received, PurchaseOrderStatus::Rejected], true)) {
                throw new ProcurementException('This purchase order can no longer be updated.');
            }

            if (! $locked->canBeEdited()) {
                throw new ProcurementException('This purchase order cannot be edited in its current status.');
            }

            $source = PurchaseOrderSource::from($data['po_source']);
            $indent = null;
            $pr = null;

            if (in_array($source, [PurchaseOrderSource::FromPurchaseRequisition, PurchaseOrderSource::FromIndent], true)) {
                $pr = PurchaseRequisition::query()
                    ->with(['indent.items.category', 'indent.department', 'indent.fromStore', 'indent.toStore', 'requisitionStore'])
                    ->whereKey($data['pr_id'] ?? null)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ((int) $pr->id !== (int) $locked->pr_id
                    && ! PurchaseRequisition::query()->availableForPurchaseOrder()->whereKey($pr->id)->exists()) {
                    throw new ProcurementException('Selected purchase requisition is not available for purchase order creation.');
                }

                if (! $pr->allowsProcurement() && (int) $pr->id !== (int) $locked->pr_id) {
                    throw new ProcurementException('This purchase requisition is closed. Purchase order cannot be updated.');
                }

                if (empty($data['vendor_id'])) {
                    throw new ProcurementException('Select a supplier before updating this purchase order.');
                }

                $indent = $pr->indent;
                $allowedIndentItemIds = $indent?->items?->pluck('id')->map(fn ($id) => (int) $id)->all() ?? [];
                foreach ($data['items'] ?? [] as $row) {
                    $indentItemId = (int) ($row['indent_item_id'] ?? 0);
                    if ($indentItemId > 0 && $allowedIndentItemIds !== [] && ! in_array($indentItemId, $allowedIndentItemIds, true)) {
                        throw new ProcurementException('One or more assets do not belong to the selected purchase requisition.');
                    }
                }
            } elseif (empty($data['vendor_id'])) {
                throw new ProcurementException('Select a supplier before updating this purchase order.');
            }

            $department = Department::query()->find($data['department_id'])
                ?? $indent?->department;

            $store = isset($data['store_id'])
                ? Department::query()->find($data['store_id'])
                : ($pr?->requisitionStore ?? $indent?->toStore ?? $indent?->fromStore);

            $locked->update([
                'indent_id' => $indent?->id,
                'pr_id' => $pr?->id,
                'vendor_id' => $data['vendor_id'],
                'purchase_date' => $data['purchase_date'] ?? $locked->purchase_date,
                'delivery_date' => $data['delivery_date'],
                'grn_store' => $data['grn_store'],
                'po_type' => $data['po_type'],
                'po_source' => $source === PurchaseOrderSource::FromIndent
                    ? PurchaseOrderSource::FromPurchaseRequisition
                    : $source,
                'department_id' => $department?->id,
                'department_name' => $data['department_name'] ?? $department?->department_name,
                'store_id' => $store?->id,
                'payment_terms' => $data['payment_terms'] ?? null,
                'minimum_advance_payment_percentage' => (float) ($data['minimum_advance_payment_percentage'] ?? $locked->minimum_advance_payment_percentage ?? 0),
                'currency' => $data['currency'] ?? $locked->currency ?? 'INR',
                'tax' => (float) ($data['tax'] ?? 0),
                'discount' => (float) ($data['discount'] ?? 0),
                'discount_type' => $data['discount_type'] ?? null,
                'shipping' => (float) ($data['shipping'] ?? 0),
                'remarks' => $data['remarks'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $locked->items()->delete();

            $itemsTotal = $this->createModulePoItems($locked, $data['items'], $indent, $source);
            $grandTotal = $this->calculateGrandTotal(
                $itemsTotal,
                (float) ($data['tax'] ?? 0),
                (float) ($data['shipping'] ?? 0),
                (float) ($data['discount'] ?? 0),
                (string) ($data['discount_type'] ?? '')
            );

            $locked->update(['grand_total' => max(0, $grandTotal)]);

            $this->logger->log(
                $locked,
                'Purchase Order Updated',
                null,
                $locked->status?->value,
                $data['remarks'] ?? $data['notes'] ?? 'Purchase order updated.',
                [
                    'po_source' => $source->value,
                    'pr_id' => $pr?->id,
                    'indent_id' => $indent?->id,
                    'vendor_id' => $data['vendor_id'],
                ],
                $actor->id
            );

            return $locked->fresh(['items', 'vendor', 'indent', 'department', 'store', 'purchaseRequisition']);
        });
    }

    public function cancel(PurchaseOrder $purchaseOrder, User $actor, ?string $remarks = null): PurchaseOrder
    {
        return DB::transaction(function () use ($purchaseOrder, $actor, $remarks) {
            $locked = PurchaseOrder::query()->whereKey($purchaseOrder->getKey())->lockForUpdate()->firstOrFail();

            if (in_array($locked->status, [PurchaseOrderStatus::Cancelled, PurchaseOrderStatus::Completed, PurchaseOrderStatus::Closed], true)) {
                throw new ProcurementException('This purchase order cannot be cancelled.');
            }

            $from = $locked->status?->value;
            $locked->update(['status' => PurchaseOrderStatus::Cancelled]);

            $this->logger->log(
                $locked,
                'Purchase Order Cancelled',
                $from,
                PurchaseOrderStatus::Cancelled->value,
                $remarks ?? 'Purchase order cancelled.',
                [],
                $actor->id
            );

            return $locked->fresh();
        });
    }

    public function delete(PurchaseOrder $purchaseOrder, User $actor): void
    {
        DB::transaction(function () use ($purchaseOrder, $actor) {
            $locked = PurchaseOrder::query()
                ->withCount(['grns', 'payments'])
                ->whereKey($purchaseOrder->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->grns_count > 0) {
                throw new ProcurementException('Purchase orders with GRNs cannot be deleted.');
            }

            if (in_array($locked->status, [PurchaseOrderStatus::Completed, PurchaseOrderStatus::Closed], true)) {
                throw new ProcurementException('Completed or closed purchase orders cannot be deleted.');
            }

            $this->logger->log(
                $locked,
                'Purchase Order Deleted',
                $locked->status?->value,
                null,
                'Purchase order deleted.',
                ['po_no' => $locked->po_no],
                $actor->id
            );

            $locked->items()->delete();
            $locked->payments()->delete();
            $locked->delete();
        });
    }

    /**
     * @param  array<int, array<string, mixed>>  $itemOverrides
     */
    private function createPoItems(PurchaseOrder $po, VendorQuotation $quotation, array $itemOverrides, float $scale = 1.0): float
    {
        $total = 0.0;
        $maps = $this->buildOverrideMaps($itemOverrides);
        $byQuotationItemId = $maps['byQuotationItemId'] ?? [];
        $byIndentItemId = $maps['byIndentItemId'] ?? [];
        $scale = $scale > 0 ? $scale : 1.0;

        if ($quotation->items->isNotEmpty()) {
            foreach ($quotation->items as $item) {
                $unitPrice = $quotation->negotiatedUnitRate($item) ?? round((float) $item->unit_price * $scale, 2);
                $line = round($unitPrice * (int) $item->quantity, 2);
                $total += $line;

                $rowOverride = $byQuotationItemId[(int) $item->id] ?? [];
                $cat = $item->indentItem?->category;
                $catId = $cat?->parent_id ?: $cat?->id;
                $subCatId = $cat?->parent_id ? $cat->id : null;
                $itemName = $item->item_name;
                if (! $subCatId && ! empty($itemName)) {
                    $itemMaster = \App\Models\ItemMaster::query()->with(['assetType', 'category.parent'])->where('item_name', $itemName)->first();
                    if ($itemMaster?->assetType?->sub_category_id) {
                        $subCatId = (int) $itemMaster->assetType->sub_category_id;
                        $subCatObj = \App\Models\AssetCategory::query()->find($subCatId);
                        if ($subCatObj?->parent_id && ! $catId) $catId = $subCatObj->parent_id;
                    } elseif ($itemMaster?->category?->parent_id) {
                        $subCatId = (int) $itemMaster->category->id;
                        $catId = (int) $itemMaster->category->parent_id;
                    }
                }

                PoItem::create([
                    'po_id' => $po->id,
                    'indent_item_id' => $item->indent_item_id,
                    'quotation_item_id' => $item->id,
                    'item_name' => $item->item_name,
                    'description' => $item->notes,
                    'quantity' => $item->quantity,
                    'unit' => $item->unit ?? 'Nos',
                    'price' => $unitPrice,
                    'total' => $line,
                    'item_type' => $item->indentItem?->resolvedItemType()->value,
                    'manufacturer' => filled($rowOverride['manufacturer'] ?? null)
                        ? $rowOverride['manufacturer']
                        : $item->indentItem?->manufacturer,
                    'marketer_name' => $rowOverride['marketer_name'] ?? null,
                    'item_code' => $rowOverride['item_code'] ?? null,
                    'hsn_code' => $rowOverride['hsn_code'] ?? null,
                    'has_free_qty' => ! empty($rowOverride['has_free_qty']),
                    'free_qty' => ! empty($rowOverride['has_free_qty']) ? (float) ($rowOverride['free_qty'] ?? 0) : 0,
                    'item_category_id' => $catId,
                    'item_sub_category_id' => $subCatId,
                    'item_category_name' => $catId ? $this->categoryName(\App\Models\AssetCategory::query()->find($catId)) : $this->categoryName($cat),
                    'item_sub_category_name' => $subCatId ? \App\Models\AssetCategory::query()->whereKey($subCatId)->value('category_name') : $this->subCategoryName($cat),
                ]);
            }

            return $total;
        }

        $indentItems = $quotation->rfq?->purchaseRequisition?->indent?->items ?? collect();

        if ($indentItems->isNotEmpty()) {
            $basePrice = $quotation->hasNegotiatedPrice() ? (float) $quotation->effectiveQuote() : (float) $quotation->price * $scale;
            $unitPrice = $indentItems->sum('quantity') > 0
                ? ($basePrice / (int) $indentItems->sum('quantity'))
                : $basePrice;

            foreach ($indentItems as $indentItem) {
                $line = ((float) $indentItem->quantity) * $unitPrice;
                $total += $line;

                $rowOverride = $byIndentItemId[(int) $indentItem->id] ?? [];
                $cat = $indentItem->category;
                $catId = $cat?->parent_id ?: $cat?->id;
                $subCatId = $cat?->parent_id ? $cat->id : null;

                PoItem::create([
                    'po_id' => $po->id,
                    'indent_item_id' => $indentItem->id,
                    'item_name' => $indentItem->item_name,
                    'description' => $indentItem->description,
                    'quantity' => $indentItem->quantity,
                    'unit' => $indentItem->unit ?? 'Nos',
                    'price' => round($unitPrice, 2),
                    'total' => round($line, 2),
                    'item_type' => $indentItem->resolvedItemType()->value,
                    'manufacturer' => filled($rowOverride['manufacturer'] ?? null)
                        ? $rowOverride['manufacturer']
                        : $indentItem->manufacturer,
                    'marketer_name' => $rowOverride['marketer_name'] ?? null,
                    'item_code' => $rowOverride['item_code'] ?? null,
                    'hsn_code' => $rowOverride['hsn_code'] ?? null,
                    'has_free_qty' => ! empty($rowOverride['has_free_qty']),
                    'free_qty' => ! empty($rowOverride['has_free_qty']) ? (float) ($rowOverride['free_qty'] ?? 0) : 0,
                    'item_category_id' => $catId,
                    'item_sub_category_id' => $subCatId,
                    'item_category_name' => $this->categoryName($cat),
                    'item_sub_category_name' => $subCatId ? \App\Models\AssetCategory::query()->whereKey($subCatId)->value('category_name') : $this->subCategoryName($cat),
                ]);
            }

            return $total;
        }

        $line = $quotation->hasNegotiatedPrice() ? (float) $quotation->effectiveQuote() : round((float) $quotation->price * $scale, 2);
        PoItem::create([
            'po_id' => $po->id,
            'item_name' => 'Quoted supply',
            'quantity' => 1,
            'unit' => 'Nos',
            'price' => $line,
            'total' => $line,
        ]);

        return $line;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     */
    private function createModulePoItems(PurchaseOrder $po, array $rows, ?Indent $indent, PurchaseOrderSource $source): float
    {
        $total = 0.0;
        $totalQty = 0;
        $subTotal = 0.0;
        $totalDiscount = 0.0;
        $taxableTotal = 0.0;
        $totalGst = 0.0;
        $totalCgst = 0.0;
        $totalSgst = 0.0;
        $totalIgst = 0.0;

        $vendor = \App\Models\Vendor::query()->find($po->vendor_id);
        $vendorState = strtolower(trim((string) ($vendor?->state ?? '')));
        $company = \App\Models\Company::query()->first();
        $companyState = strtolower(trim((string) ($company?->state ?? '')));
        $isIntraState = (empty($vendorState) || empty($companyState) || $vendorState === $companyState);

        $indentItems = $indent?->items?->keyBy('id') ?? collect();

        foreach ($rows as $row) {
            $indentItem = null;
            $fromPr = in_array($source, [PurchaseOrderSource::FromPurchaseRequisition, PurchaseOrderSource::FromIndent], true);

            if ($fromPr) {
                $indentItem = $indentItems->get((int) ($row['indent_item_id'] ?? 0));
            }

            $quantity = (int) ($row['quantity'] ?? ($indentItem?->quantity ?? 0));
            $price = (float) ($row['price'] ?? 0);
            $mrp = (float) ($row['mrp'] ?? 0);

            $baseAmount = $quantity * $price;
            $lineDiscount = $this->discountAmount(
                $baseAmount,
                (float) ($row['discount'] ?? 0),
                (string) ($row['discount_type'] ?? '')
            );
            $taxableAmount = max(0, $baseAmount - $lineDiscount);
            $rateAfterDiscount = $quantity > 0 ? round($taxableAmount / $quantity, 2) : max(0, $price - $lineDiscount);

            $gstPercent = (float) ($row['gst_percent'] ?? 0);
            $gstAmount = round($taxableAmount * ($gstPercent / 100), 2);
            $cgstAmount = 0.0;
            $sgstAmount = 0.0;
            $igstAmount = 0.0;
            if ($isIntraState) {
                $cgstAmount = round($gstAmount / 2, 2);
                $sgstAmount = $gstAmount - $cgstAmount;
            } else {
                $igstAmount = $gstAmount;
            }

            $lineTotal = round($taxableAmount + $gstAmount, 2);
            $total += $lineTotal;

            $totalQty += $quantity;
            $subTotal += $baseAmount;
            $totalDiscount += $lineDiscount;
            $taxableTotal += $taxableAmount;
            $totalGst += $gstAmount;
            $totalCgst += $cgstAmount;
            $totalSgst += $sgstAmount;
            $totalIgst += $igstAmount;

            $category = isset($row['item_category_id'])
                ? AssetCategory::query()->find($row['item_category_id'])
                : $indentItem?->category;

            $subCategory = isset($row['item_sub_category_id'])
                ? AssetCategory::query()->find($row['item_sub_category_id'])
                : null;

            $subCatId = $subCategory?->id ?? ($category?->parent_id ? $category->id : null);
            $catId = $subCategory?->parent_id ?: ($category?->parent_id ?: $category?->id);
            if (! $subCatId && ! empty($row['item_name'])) {
                $itemMaster = \App\Models\ItemMaster::query()->with(['assetType', 'category.parent'])->where('item_name', $row['item_name'])->first();
                if ($itemMaster?->assetType?->sub_category_id) {
                    $subCatId = (int) $itemMaster->assetType->sub_category_id;
                    $subCategory = AssetCategory::query()->find($subCatId);
                    if ($subCategory?->parent_id && ! $catId) $catId = $subCategory->parent_id;
                } elseif ($itemMaster?->category?->parent_id) {
                    $subCatId = (int) $itemMaster->category->id;
                    $catId = (int) $itemMaster->category->parent_id;
                    $subCategory = $itemMaster->category;
                }
            }

            $rawItemType = $row['item_type']
                ?? $indentItem?->resolvedItemType()->value
                ?? $category?->itemTypeEnum()->value;
            // Free-text asset type names are display-only; persist enum Asset/Consumable only.
            $itemType = ItemType::tryFromMixed($rawItemType)?->value ?? ItemType::Asset->value;

            $hasFreeQty = ! empty($row['has_free_qty']);
            $freeQty = $hasFreeQty ? (float) ($row['free_qty'] ?? 0) : 0;

            PoItem::create([
                'po_id' => $po->id,
                'indent_item_id' => $indentItem?->id,
                'item_name' => $row['item_name'] ?? $indentItem?->item_name,
                'description' => $indentItem?->description,
                'item_code' => $row['item_code'] ?? null,
                'hsn_code' => $row['hsn_code'] ?? null,
                'manufacturer' => $row['manufacturer'] ?? $indentItem?->manufacturer,
                'marketer_name' => $row['marketer_name'] ?? null,
                'quantity' => $quantity,
                'unit' => $row['unit'] ?? $indentItem?->unit ?? 'Nos',
                'purchase_unit' => $row['purchase_unit'] ?? $row['unit'] ?? $indentItem?->unit ?? 'Nos',
                'price' => $price,
                'mrp' => $mrp,
                'gst_percent' => $gstPercent,
                'gst_amount' => $gstAmount,
                'cgst_amount' => $cgstAmount,
                'sgst_amount' => $sgstAmount,
                'igst_amount' => $igstAmount,
                'rate_after_discount' => $rateAfterDiscount,
                'taxable_amount' => $taxableAmount,
                'base_amount' => $baseAmount,
                'discount_amount' => $lineDiscount,
                'epr' => (float) ($row['epr'] ?? 0),
                'total' => round($lineTotal, 2),
                'has_free_qty' => $hasFreeQty,
                'free_qty' => $freeQty,
                'discount' => (float) ($row['discount'] ?? 0),
                'discount_type' => $row['discount_type'] ?? null,
                'conversion' => $row['conversion'] ?? $indentItem?->conversion,
                'qoh' => $row['qoh'] ?? $indentItem?->stock_in_hand,
                'estimated_delivery_date' => $row['estimated_delivery_date'] ?? null,
                'remarks' => $row['remarks'] ?? null,
                'item_type' => $itemType,
                'item_category_id' => $catId,
                'item_sub_category_id' => $subCatId,
                'item_category_name' => $catId
                    ? $this->categoryName(AssetCategory::query()->find($catId))
                    : $this->categoryName($category),
                'item_sub_category_name' => $subCategory
                    ? $subCategory->category_name
                    : ($subCatId ? AssetCategory::query()->whereKey($subCatId)->value('category_name') : $this->subCategoryName($category)),
            ]);
        }

        $po->update([
            'total_quantity' => $totalQty,
            'sub_total' => round($subTotal, 2),
            'total_discount' => round($totalDiscount, 2),
            'taxable_amount' => round($taxableTotal, 2),
            'total_gst' => round($totalGst, 2),
            'total_cgst' => round($totalCgst, 2),
            'total_sgst' => round($totalSgst, 2),
            'total_igst' => round($totalIgst, 2),
            'grand_total' => round($total + (float) ($po->shipping ?? 0), 2),
        ]);

        return $total;
    }

    private function calculateGrandTotal(
        float $itemsTotal,
        float $tax,
        float $shipping,
        float $discount,
        string $discountType,
    ): float {
        $baseTotal = $itemsTotal + $tax + $shipping;

        return $baseTotal - $this->discountAmount($baseTotal, $discount, $discountType);
    }

    private function discountAmount(float $base, float $discount, string $discountType): float
    {
        if ($discount <= 0) {
            return 0;
        }

        return match (strtolower($discountType)) {
            'percentage', 'percent', '%' => ($base * $discount) / 100,
            default => $discount,
        };
    }

    /**
     * @param  array<int, array<string, mixed>>  $itemOverrides
     */
    private function buildOverrideMaps(array $itemOverrides): array
    {
        $byQuotationItemId = [];
        $byIndentItemId = [];

        foreach ($itemOverrides as $row) {
            $quotationId = $row['quotation_item_id'] ?? null;
            if (! empty($quotationId)) {
                $byQuotationItemId[(int) $quotationId] = $row;
            }

            $indentId = $row['indent_item_id'] ?? null;
            if (! empty($indentId)) {
                $byIndentItemId[(int) $indentId] = $row;
            }
        }

        return [
            'byQuotationItemId' => $byQuotationItemId,
            'byIndentItemId' => $byIndentItemId,
        ];
    }

    private function categoryName(?AssetCategory $category): ?string
    {
        if (! $category) {
            return null;
        }

        if (! $category->parent_id) {
            return $category->category_name;
        }

        return AssetCategory::query()->whereKey($category->parent_id)->value('category_name');
    }

    private function subCategoryName(?AssetCategory $category): ?string
    {
        if (! $category) {
            return null;
        }

        return $category->parent_id
            ? $category->category_name
            : null;
    }

    private function isUniqueViolation(QueryException $e, string $indexHint): bool
    {
        $message = strtolower($e->getMessage());

        return str_contains($message, 'duplicate') && str_contains($message, strtolower($indexHint));
    }
}
