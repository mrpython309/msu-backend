<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MsuGrn;
use App\Models\MsuPurchaseOrder;
use App\Models\MsuPurchaseOrderItem;
use App\Enums\Procurement\MsuGrnStatus;
use App\Services\Procurement\DocumentNumberGenerator;
use App\Services\Procurement\MsuPurchaseOrderReceiptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponse;

class MsuGrnController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $grns = MsuGrn::with(['vendor', 'purchaseOrder', 'department', 'employeeMaster', 'items.itemMaster'])->latest()->paginate(20);
        return $this->successWithPagination($grns);
    }

    public function show(string $id)
    {
        $grn = MsuGrn::with(['vendor', 'purchaseOrder', 'department', 'employeeMaster', 'items.itemMaster'])->findOrFail($id);
        return $this->success($grn);
    }

    public function store(Request $request, DocumentNumberGenerator $numberGenerator, MsuPurchaseOrderReceiptService $receiptService)
    {
        $validated = $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'msu_purchase_order_id' => 'required|exists:msu_purchase_orders,id',
            'grn_date' => 'required|date',
            'receipt_number' => 'nullable|string',
            'department_id' => 'nullable|exists:departments,id',
            'employee_master_id' => 'nullable|exists:employee_masters,id',
            'vendor_invoice_number' => 'nullable|string',
            'vendor_invoice_date' => 'nullable|date',
            'remarks' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.msu_purchase_order_item_id' => 'required|exists:msu_purchase_order_items,id',
            'items.*.received_quantity' => 'required|numeric|min:0',
        ]);

        try {
            $grn = DB::transaction(function () use ($validated, $numberGenerator, $receiptService) {
                $po = MsuPurchaseOrder::with(['branch.company', 'items'])->findOrFail($validated['msu_purchase_order_id']);

                if (! $po->statusEnum()->allowsGrn()) {
                    throw new \Exception('GRN cannot be created for this PO status.');
                }

                if ((int) $po->branch_id !== (int) $validated['branch_id']) {
                    throw new \Exception('Selected PO does not belong to the selected branch.');
                }

                $branch = \App\Models\Branch::with('company')->findOrFail($validated['branch_id']);
                $branchCode = $branch->code ?: ($branch->name ?: 'BRN');
                $companyCode = $branch->company?->code ?: 'MSULLP';
                $grnNumber = $numberGenerator->nextMsuGrnNumber($companyCode, $branchCode, $validated['grn_date'] ?? now());

                $hasPreviousMiscCharges = MsuGrn::where('msu_purchase_order_id', $po->id)
                    ->where('miscellaneous_charges', '>', 0)
                    ->exists();

                $grn = MsuGrn::create([
                    'grn_number' => $grnNumber,
                    'receipt_number' => $validated['receipt_number'] ?? $grnNumber,
                    'msu_purchase_order_id' => $po->id,
                    'branch_id' => $branch->id,
                    'vendor_id' => $po->vendor_id,
                    'department_id' => $validated['department_id'] ?? $po->department_id,
                    'employee_master_id' => $validated['employee_master_id'] ?? $po->employee_master_id,
                    'vendor_invoice_number' => $validated['vendor_invoice_number'] ?? null,
                    'vendor_invoice_date' => $validated['vendor_invoice_date'] ?? null,
                    'grn_date' => $validated['grn_date'],
                    'status' => MsuGrnStatus::PartiallyReceived,
                    'remarks' => $validated['remarks'] ?? null,
                    'miscellaneous_charges' => $hasPreviousMiscCharges ? 0 : ($po->miscellaneous_charges ?? 0),
                    'miscellaneous_tax' => $hasPreviousMiscCharges ? 0 : ($po->miscellaneous_tax ?? 0),
                    'created_by' => Auth::id() ?: 1,
                ]);

                $receiptMap = [];

                foreach ($validated['items'] as $row) {
                    $receivedQty = (float) ($row['received_quantity'] ?? 0);
                    
                    if ($receivedQty <= 0) {
                        continue;
                    }
                    
                    $poItem = MsuPurchaseOrderItem::query()
                        ->where('msu_purchase_order_id', $po->id)
                        ->where('id', $row['msu_purchase_order_item_id'])
                        ->lockForUpdate()
                        ->firstOrFail();

                    $previously = (float) $poItem->received_quantity;
                    $balance = max(0, (float) $poItem->quantity - $previously);
                    if ($receivedQty > $balance) {
                        throw new \Exception("Received quantity for {$poItem->item_code} exceeds balance ({$balance}).");
                    }

                    $sort = $grn->items()->max('sort_order') + 1;
                    $grnItem = $grn->items()->create([
                        'msu_purchase_order_item_id' => $poItem->id,
                        'msu_item_master_id' => $poItem->msu_item_master_id,
                        'item_code' => $poItem->item_code,
                        'item_name' => $poItem->item_name,
                        'hsn' => $row['hsn'] ?? $poItem->hsn,
                        'ordered_quantity' => $poItem->quantity,
                        'previously_received_quantity' => $previously,
                        'received_quantity' => $receivedQty,
                        'sort_order' => $sort,
                    ]);

                    $receiptMap[$poItem->id] = $receivedQty;
                }

                if ($receiptMap === []) {
                    throw new \Exception('Enter received quantity for at least one item.');
                }

                $receiptService->applyReceipts($po, $receiptMap, false);
                
                $po->refresh();
                $grn->status = ($po->status === \App\Enums\Procurement\MsuPurchaseOrderStatus::GrnReceived) ? MsuGrnStatus::Received : MsuGrnStatus::PartiallyReceived;
                $grn->save();

                return $grn;
            });

            $grn->load(['vendor', 'purchaseOrder', 'department', 'employeeMaster', 'items.itemMaster']);
            return $this->success($grn, 'GRN created successfully.', 201);
            
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    public function destroy(string $id)
    {
        $msuGrn = MsuGrn::findOrFail($id);

        try {
            DB::transaction(function () use ($msuGrn) {
                $msuGrn->load('items');
                $po = MsuPurchaseOrder::query()->lockForUpdate()->find($msuGrn->msu_purchase_order_id);

                foreach ($msuGrn->items as $item) {
                    $poItem = MsuPurchaseOrderItem::query()
                        ->where('id', $item->msu_purchase_order_item_id)
                        ->lockForUpdate()
                        ->first();

                    if ($poItem) {
                        $poItem->update([
                            'received_quantity' => max(0, (float) $poItem->received_quantity - (float) $item->received_quantity),
                        ]);
                    }

                    if ($msuGrn->is_added_to_stock) {
                        $qty = (float) $item->received_quantity;
                        if ($qty > 0) {
                            $itemMaster = \App\Models\MsuItemMaster::lockForUpdate()->find($item->msu_item_master_id);
                            if ($itemMaster) {
                                $stockBefore = (float) $itemMaster->current_stock;
                                $stockAfter = max(0, $stockBefore - $qty);

                                $itemMaster->current_stock = $stockAfter;
                                $itemMaster->save();

                                \App\Models\MsuStockMovement::create([
                                    'msu_item_master_id' => $itemMaster->id,
                                    'transaction_type' => 'out',
                                    'quantity' => $qty,
                                    'stock_before' => $stockBefore,
                                    'stock_after' => $stockAfter,
                                    'reference_type' => get_class($msuGrn),
                                    'reference_id' => $msuGrn->id,
                                    'remarks' => 'Stock reversed for deleted GRN ' . $msuGrn->grn_number,
                                    'created_by' => Auth::id() ?: 1,
                                ]);
                            }
                        }
                    }
                }

                $msuGrn->delete();

                if ($po) {
                    $po->refresh();
                    $po->syncStatusFromReceipts();
                }
            });

            return $this->success(null, 'GRN deleted successfully.');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }
    public function poItems(string $id)
    {
        $msuPurchaseOrder = MsuPurchaseOrder::findOrFail($id);

        if (! $msuPurchaseOrder->statusEnum()->allowsGrn()) {
            return $this->error('This PO is not open for GRN receipt.', 422);
        }

        $msuPurchaseOrder->load(['vendor', 'department', 'employeeMaster', 'branch', 'items']);

        $items = $msuPurchaseOrder->items->map(function (MsuPurchaseOrderItem $item) {
            $ordered = (float) $item->quantity;
            $previously = (float) $item->received_quantity;
            $balance = max(0, $ordered - $previously);

            return [
                'id' => $item->id,
                'item_code' => $item->item_code,
                'item_name' => $item->item_name,
                'hsn' => $item->hsn,
                'ordered_quantity' => $ordered,
                'previously_received_quantity' => $previously,
                'balance_quantity' => $balance,
                'received_quantity' => $balance > 0 ? $balance : 0,
                'price' => $item->price,
                'tax' => $item->tax,
            ];
        })->filter(function ($item) {
            return $item['balance_quantity'] > 0;
        })->values();

        $hasPreviousMiscCharges = MsuGrn::where('msu_purchase_order_id', $msuPurchaseOrder->id)
            ->where('miscellaneous_charges', '>', 0)
            ->exists();

        return $this->success([
            'po' => [
                'id' => $msuPurchaseOrder->id,
                'po_number' => $msuPurchaseOrder->po_number,
                'vendor_id' => $msuPurchaseOrder->vendor_id,
                'vendor_name' => $msuPurchaseOrder->vendor?->name,
                'department_id' => $msuPurchaseOrder->department_id,
                'employee_master_id' => $msuPurchaseOrder->employee_master_id,
                'branch_code' => $msuPurchaseOrder->branch?->code ?: $msuPurchaseOrder->branch?->name,
                'miscellaneous_charges' => $msuPurchaseOrder->miscellaneous_charges,
                'miscellaneous_tax' => $msuPurchaseOrder->miscellaneous_tax,
                'has_previous_misc_charges' => $hasPreviousMiscCharges,
            ],
            'items' => $items,
        ]);
    }

    public function previewGrnNumber(Request $request, DocumentNumberGenerator $numberGenerator)
    {
        $request->validate([
            'msu_purchase_order_id' => 'nullable|exists:msu_purchase_orders,id',
            'grn_date' => 'nullable|date',
        ]);

        $branchCode = 'BRN';
        $companyCode = 'MSULLP';

        if ($request->filled('msu_purchase_order_id')) {
            $po = MsuPurchaseOrder::with('branch.company')->find($request->msu_purchase_order_id);
            $branchCode = $po?->branch?->code ?: ($po?->branch?->name ?: 'BRN');
            $companyCode = $po?->branch?->company?->code ?: $companyCode;
        }

        return $this->success([
            'grn_number' => $numberGenerator->previewMsuGrnNumber($companyCode, $branchCode, $request->grn_date),
        ]);
    }

    public function eligiblePOs()
    {
        $purchaseOrders = MsuPurchaseOrder::with(['vendor', 'branch'])
            ->whereIn('status', [
                \App\Enums\Procurement\MsuPurchaseOrderStatus::Approved->value,
                \App\Enums\Procurement\MsuPurchaseOrderStatus::GrnPartiallyReceived->value,
            ])
            ->orderByDesc('id')
            ->get(['id', 'po_number', 'po_date', 'vendor_id', 'branch_id', 'status']);
            
        return $this->success($purchaseOrders);
    }
}
