<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MaterialIssue;
use App\Models\MaterialIssueItem;
use App\Models\MaterialRequest;
use App\Models\MaterialRequestItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponse;

class MaterialIssueController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $issues = MaterialIssue::with(['department', 'employee', 'materialRequest', 'items.itemMaster'])->latest()->paginate(20);
        return $this->successWithPagination($issues);
    }

    public function show(string $id)
    {
        $issue = MaterialIssue::with(['department', 'employee', 'materialRequest', 'items.itemMaster', 'items.materialRequestItem'])->findOrFail($id);
        return $this->success($issue);
    }

    public function store(Request $request)
    {
        $request->validate([
            'issue_date' => 'required|date',
            'material_request_id' => 'required|exists:material_requests,id',
            'department_id' => 'nullable|exists:departments,id',
            'employee_master_id' => 'nullable|exists:employee_masters,id',
            'items' => 'required|array|min:1',
            'items.*.material_request_item_id' => 'required|exists:material_request_items,id',
            'items.*.issue_qty' => 'required|numeric|min:0.001',
        ]);

        try {
            $mi = DB::transaction(function () use ($request) {
                $mr = MaterialRequest::findOrFail($request->material_request_id);

                if (in_array($mr->status, ['fully_issued', 'created'])) {
                    throw new \Exception('Invalid Material Request status for issue.');
                }

                $mi = MaterialIssue::where('material_request_id', $mr->id)->first();
                
                if ($mi) {
                    $mi->issue_date = $request->issue_date;
                    $mi->remarks = $request->remarks ?? $mi->remarks;
                    $mi->save();
                } else {
                    $branch = \App\Models\Branch::with('company')->find($mr->branch_id);
                    $branchCode = $branch ? ($branch->code ?: $branch->name) : 'BRN';
                    $companyCode = $branch?->company?->code ?: 'MSULLP';
                    $miNumber = app(\App\Services\Procurement\DocumentNumberGenerator::class)->nextMiNumber($companyCode, $branchCode, $request->issue_date);

                    $mi = MaterialIssue::create([
                        'mi_number' => $miNumber,
                        'issue_date' => $request->issue_date,
                        'department_id' => $request->department_id,
                        'employee_master_id' => $request->employee_master_id,
                        'material_request_id' => $mr->id,
                        'status' => 'approved',
                        'created_by' => Auth::id() ?: 1,
                        'remarks' => $request->remarks ?? null,
                    ]);
                }

                $hasIssues = false;

                foreach ($request->items as $index => $itemData) {
                    if ($itemData['issue_qty'] <= 0) continue;
                    $hasIssues = true;

                    $mrItem = MaterialRequestItem::lockForUpdate()->findOrFail($itemData['material_request_item_id']);
                    
                    if ($itemData['issue_qty'] > $mrItem->remaining_qty) {
                        throw new \Exception("Issue quantity cannot exceed remaining quantity for item.");
                    }

                    $itemMaster = \App\Models\MsuItemMaster::lockForUpdate()->find($mrItem->msu_item_master_id);
                    if (!$itemMaster) {
                        throw new \Exception("Item master not found.");
                    }

                    $stockBefore = (float) $itemMaster->current_stock;
                    if ($itemData['issue_qty'] > $stockBefore) {
                        throw new \Exception("Issue quantity cannot exceed current stock ({$stockBefore}) for item {$itemMaster->item_name}.");
                    }

                    $issueItem = \App\Models\MaterialIssueItem::where('material_issue_id', $mi->id)
                        ->where('material_request_item_id', $mrItem->id)
                        ->first();
                    
                    if ($issueItem) {
                        $issueItem->issue_qty += $itemData['issue_qty'];
                        if (!empty($itemData['remarks'])) {
                            $issueItem->remarks = $itemData['remarks'];
                        }
                        $issueItem->save();
                    } else {
                        MaterialIssueItem::create([
                            'material_issue_id' => $mi->id,
                            'material_request_item_id' => $mrItem->id,
                            'msu_item_master_id' => $mrItem->msu_item_master_id,
                            'issue_qty' => $itemData['issue_qty'],
                            'remarks' => $itemData['remarks'] ?? null,
                        ]);
                    }

                    $mrItem->issued_qty += $itemData['issue_qty'];
                    $mrItem->save();

                    $stockAfter = $stockBefore - (float) $itemData['issue_qty'];
                    $itemMaster->current_stock = $stockAfter;
                    $itemMaster->save();

                    \App\Models\MsuStockMovement::create([
                        'msu_item_master_id' => $itemMaster->id,
                        'transaction_type' => 'out',
                        'quantity' => $itemData['issue_qty'],
                        'stock_before' => $stockBefore,
                        'stock_after' => $stockAfter,
                        'reference_type' => get_class($mi),
                        'reference_id' => $mi->id,
                        'remarks' => 'Stock out via Material Issue ' . $mi->mi_number,
                        'created_by' => Auth::id() ?: 1,
                    ]);
                }

                if (!$hasIssues) {
                    throw new \Exception("Please provide issue quantity for at least one item.");
                }

                $this->updateMaterialRequestStatus($mr);
                $mi->status = $mr->status;
                $mi->save();

                return $mi;
            });

            $mi->load(['department', 'employee', 'materialRequest', 'items.itemMaster']);
            return $this->success($mi, 'Material Issue saved successfully.', 201);
            
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    public function update(Request $request, string $id)
    {
        $materialIssue = MaterialIssue::findOrFail($id);

        $request->validate([
            'issue_date' => 'required|date',
            'department_id' => 'nullable|exists:departments,id',
            'employee_master_id' => 'nullable|exists:employee_masters,id',
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:material_issue_items,id',
            'items.*.issue_qty' => 'required|numeric|min:0.001',
        ]);

        try {
            DB::transaction(function () use ($request, $materialIssue) {
                $materialIssue->update([
                    'issue_date' => $request->issue_date,
                    'department_id' => $request->department_id,
                    'employee_master_id' => $request->employee_master_id,
                    'remarks' => $request->remarks ?? null,
                ]);

                $mr = MaterialRequest::findOrFail($materialIssue->material_request_id);

                foreach ($request->items as $index => $itemData) {
                    $miItem = MaterialIssueItem::findOrFail($itemData['id']);
                    $mrItem = MaterialRequestItem::lockForUpdate()->findOrFail($miItem->material_request_item_id);

                    $qtyDiff = $itemData['issue_qty'] - $miItem->issue_qty;

                    if ($qtyDiff > 0 && $qtyDiff > $mrItem->remaining_qty) {
                        throw new \Exception("Issue quantity cannot exceed remaining quantity for item.");
                    }

                    $itemMaster = \App\Models\MsuItemMaster::lockForUpdate()->find($mrItem->msu_item_master_id);
                    if (!$itemMaster) {
                        throw new \Exception("Item master not found.");
                    }

                    $stockBefore = (float) $itemMaster->current_stock;
                    if ($qtyDiff > 0 && $qtyDiff > $stockBefore) {
                        throw new \Exception("Additional issue quantity cannot exceed current stock ({$stockBefore}) for item {$itemMaster->item_name}.");
                    }

                    $miItem->update([
                        'issue_qty' => $itemData['issue_qty'],
                        'remarks' => $itemData['remarks'] ?? null,
                    ]);

                    $mrItem->issued_qty += $qtyDiff;
                    $mrItem->save();

                    if (abs($qtyDiff) > 0) {
                        $stockAfter = $stockBefore - $qtyDiff;
                        $itemMaster->current_stock = $stockAfter;
                        $itemMaster->save();

                        \App\Models\MsuStockMovement::create([
                            'msu_item_master_id' => $itemMaster->id,
                            'transaction_type' => $qtyDiff > 0 ? 'out' : 'in',
                            'quantity' => abs($qtyDiff),
                            'stock_before' => $stockBefore,
                            'stock_after' => $stockAfter,
                            'reference_type' => get_class($materialIssue),
                            'reference_id' => $materialIssue->id,
                            'remarks' => 'Stock adjustment via Material Issue update ' . $materialIssue->mi_number,
                            'created_by' => Auth::id() ?: 1,
                        ]);
                    }
                }

                $this->updateMaterialRequestStatus($mr);
            });

            $materialIssue->load(['department', 'employee', 'materialRequest', 'items.itemMaster']);
            return $this->success($materialIssue, 'Material Issue updated successfully.');

        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    public function destroy(string $id)
    {
        $materialIssue = MaterialIssue::findOrFail($id);

        if ($materialIssue->status === 'fully_issued') {
            return $this->error('Fully issued materials cannot be deleted.', 403);
        }

        DB::transaction(function () use ($materialIssue) {
            $mr = MaterialRequest::findOrFail($materialIssue->material_request_id);
            
            foreach ($materialIssue->items as $miItem) {
                $mrItem = MaterialRequestItem::lockForUpdate()->findOrFail($miItem->material_request_item_id);
                $mrItem->issued_qty -= $miItem->issue_qty;
                $mrItem->save();

                $itemMaster = \App\Models\MsuItemMaster::lockForUpdate()->find($mrItem->msu_item_master_id);
                if ($itemMaster && $miItem->issue_qty > 0) {
                    $stockBefore = (float) $itemMaster->current_stock;
                    $stockAfter = $stockBefore + (float) $miItem->issue_qty;
                    $itemMaster->current_stock = $stockAfter;
                    $itemMaster->save();

                    \App\Models\MsuStockMovement::create([
                        'msu_item_master_id' => $itemMaster->id,
                        'transaction_type' => 'in',
                        'quantity' => $miItem->issue_qty,
                        'stock_before' => $stockBefore,
                        'stock_after' => $stockAfter,
                        'reference_type' => get_class($materialIssue),
                        'reference_id' => $materialIssue->id,
                        'remarks' => 'Stock returned via Material Issue deletion ' . $materialIssue->mi_number,
                        'created_by' => Auth::id() ?: 1,
                    ]);
                }
            }

            $materialIssue->delete();
            $this->updateMaterialRequestStatus($mr);
        });

        return $this->success(null, 'Material Issue deleted successfully.');
    }

    private function updateMaterialRequestStatus(MaterialRequest $mr)
    {
        $allFullyIssued = true;
        $mrItems = $mr->items()->get();
        foreach ($mrItems as $mrItem) {
            if (round($mrItem->remaining_qty, 3) > 0) {
                $allFullyIssued = false;
                break;
            }
        }

        $mr->status = $allFullyIssued ? 'fully_issued' : 'partially_issued';
        
        $totalIssued = $mrItems->sum('issued_qty');
        if ($totalIssued == 0) {
            $mr->status = 'approved';
        }

        $mr->save();
    }

    public function getMaterialRequestItems($id)
    {
        $mr = MaterialRequest::with(['items.itemMaster.hsn', 'items.unit'])->findOrFail($id);
        
        $items = $mr->items->map(function ($item) {
            return [
                'id' => $item->id,
                'msu_item_master_id' => $item->msu_item_master_id,
                'item_name' => $item->itemMaster->item_name ?? '',
                'unit_name' => $item->unit->unit_name ?? '',
                'hsn' => $item->itemMaster->hsn->hsn_code ?? $item->itemMaster->hsn->code ?? '',
                'requested_qty' => $item->requested_qty,
                'issued_qty' => $item->issued_qty,
                'available_stock' => $item->itemMaster->current_stock ?? 0,
                'remaining_qty' => $item->remaining_qty,
            ];
        });

        return $this->success([
            'department_id' => $mr->department_id,
            'employee_master_id' => $mr->employee_master_id,
            'items' => $items
        ]);
    }

    public function eligibleMRs()
    {
        $materialRequests = MaterialRequest::with(['department', 'employee', 'branch'])
            ->whereIn('status', ['approved', 'partially_issued'])
            ->orderByDesc('id')
            ->get(['id', 'mr_number', 'request_date', 'department_id', 'employee_master_id', 'branch_id', 'status']);
            
        return $this->success($materialRequests);
    }
}
