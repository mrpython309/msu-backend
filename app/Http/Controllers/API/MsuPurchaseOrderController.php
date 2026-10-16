<?php

namespace App\Http\Controllers\API;

use App\Enums\Procurement\MsuPurchaseOrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\MsuPurchaseOrder\MsuPurchaseOrderRequest;
use App\Models\MsuItemMaster;
use App\Models\MsuPurchaseOrder;
use App\Models\MsuPurchaseOrderItem;
use App\Services\Procurement\DocumentNumberGenerator;
use App\Traits\ApiResponse;
use Illuminate\Support\Facades\DB;

class MsuPurchaseOrderController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $orders = MsuPurchaseOrder::with(['vendor', 'paymentTerm', 'items'])->latest()->paginate(20);

        return $this->successWithPagination($orders);
    }

    public function store(MsuPurchaseOrderRequest $request, DocumentNumberGenerator $numberGenerator)
    {
        $po = DB::transaction(function () use ($request, $numberGenerator) {
            $validated = $request->validated();
            
            $branch = \App\Models\Branch::with('company')->find($validated['branch_id']);
            $branchCode = $branch?->code ?: $branch?->name ?: 'BRN';
            $companyCode = $branch?->company?->code ?: 'MSULLP';
            
            $poNumber = $numberGenerator->nextMsuPoNumber($companyCode, $branchCode, $validated['po_date']);

            $po = MsuPurchaseOrder::create([
                'po_number' => $poNumber,
                'po_date' => $validated['po_date'],
                'vendor_id' => $validated['vendor_id'],
                'branch_id' => $validated['branch_id'],
                'shipping_address' => $validated['shipping_address'] ?? null,
                'payment_term_id' => $validated['payment_term_id'] ?? null,
                'miscellaneous_charges' => $validated['miscellaneous_charges'] ?? 0,
                'sgst' => $validated['sgst'] ?? 0,
                'cgst' => $validated['cgst'] ?? 0,
                'status' => $validated['status'] ?? 'po_created',
                'created_by' => auth()->id(),
                'total_value' => 0,
                'grand_total' => 0,
            ]);

            foreach (array_values($validated['items']) as $index => $row) {
                $master = MsuItemMaster::findOrFail($row['msu_item_master_id']);
                $qty = (float) $row['quantity'];
                $price = (float) $row['price'];
                $tax = (float) ($row['tax'] ?? 0);
                $po->items()->create([
                    'msu_item_master_id' => $master->id,
                    'item_code' => $master->item_code,
                    'item_name' => $master->item_name,
                    'hsn' => $row['hsn'] ?? null,
                    'quantity' => $qty,
                    'price' => $price,
                    'tax' => $tax,
                    'net_value' => MsuPurchaseOrderItem::computeNetValue($qty, $price, $tax),
                    'sort_order' => $index + 1,
                ]);
            }

            $po->recalculateTotals();

            return $po->load(['vendor', 'paymentTerm', 'items']);
        });

        return $this->success($po);
    }

    public function show(string $id)
    {
        $po = MsuPurchaseOrder::with(['vendor', 'paymentTerm', 'items'])->findOrFail($id);

        return $this->success($po);
    }

    public function update(MsuPurchaseOrderRequest $request, string $id)
    {
        $po = MsuPurchaseOrder::find($id);
        if (! $po) {
            return $this->error('Purchase order not found');
        }

        DB::transaction(function () use ($request, $po) {
            $validated = $request->validated();
            $po->update([
                'po_date' => $validated['po_date'],
                'vendor_id' => $validated['vendor_id'],
                'branch_id' => $validated['branch_id'],
                'shipping_address' => $validated['shipping_address'] ?? null,
                'payment_term_id' => $validated['payment_term_id'] ?? null,
                'miscellaneous_charges' => $validated['miscellaneous_charges'] ?? 0,
                'sgst' => $validated['sgst'] ?? 0,
                'cgst' => $validated['cgst'] ?? 0,
                'status' => $validated['status'] ?? $po->status,
            ]);
            $po->items()->delete();
            foreach (array_values($validated['items']) as $index => $row) {
                $master = MsuItemMaster::findOrFail($row['msu_item_master_id']);
                $qty = (float) $row['quantity'];
                $price = (float) $row['price'];
                $tax = (float) ($row['tax'] ?? 0);
                $po->items()->create([
                    'msu_item_master_id' => $master->id,
                    'item_code' => $master->item_code,
                    'item_name' => $master->item_name,
                    'hsn' => $row['hsn'] ?? null,
                    'quantity' => $qty,
                    'price' => $price,
                    'tax' => $tax,
                    'net_value' => MsuPurchaseOrderItem::computeNetValue($qty, $price, $tax),
                    'sort_order' => $index + 1,
                ]);
            }
            $po->recalculateTotals();
        });

        return $this->success($po->fresh(['vendor', 'paymentTerm', 'items']));
    }

    public function destroy(string $id)
    {
        $po = MsuPurchaseOrder::find($id);
        if (! $po) {
            return $this->error('Purchase order not found');
        }
        if ($po->statusEnum() !== MsuPurchaseOrderStatus::PoCreated) {
            return $this->error('Only created Purchase Orders can be deleted.');
        }

        $po->delete();

        return $this->success('Purchase order deleted successfully');
    }
}
