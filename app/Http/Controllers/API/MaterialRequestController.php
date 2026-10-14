<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MaterialRequest;
use App\Models\MaterialRequestItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Traits\ApiResponse;

class MaterialRequestController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $requests = MaterialRequest::with(['department', 'employee', 'company', 'branch', 'items.itemMaster'])->latest()->paginate(20);
        return $this->successWithPagination($requests);
    }

    public function show(string $id)
    {
        $materialRequest = MaterialRequest::with(['department', 'employee', 'company', 'branch', 'items.itemMaster'])->findOrFail($id);
        return $this->success($materialRequest);
    }

    public function store(Request $request)
    {
        $request->validate([
            'request_date' => 'required|date',
            'department_id' => 'nullable|exists:departments,id',
            'employee_master_id' => 'nullable|exists:employee_masters,id',
            'branch_id' => 'nullable|exists:branches,id',
            'items' => 'required|array|min:1',
            'items.*.msu_item_master_id' => 'required|exists:msu_item_masters,id',
            'items.*.requested_qty' => 'required|numeric|min:0.001',
            'items.*.unit_id' => 'nullable|exists:units,id',
        ]);

        foreach ($request->items as $index => $itemData) {
            $master = \App\Models\MsuItemMaster::find($itemData['msu_item_master_id']);
            if ($master && $itemData['requested_qty'] > $master->current_stock) {
                return $this->error("Requested quantity for '{$master->item_name}' cannot exceed available stock ({$master->current_stock}).", 422);
            }
        }

        $mr = DB::transaction(function () use ($request) {
            $branch = \App\Models\Branch::find($request->branch_id);
            $branchCode = $branch ? ($branch->code ?: $branch->name) : 'BRN';
            $companyCode = $branch?->company?->code ?: 'MSULLP';
            $mrNumber = app(\App\Services\Procurement\DocumentNumberGenerator::class)->nextMrNumber($companyCode, $branchCode, $request->request_date);

            $mr = MaterialRequest::create([
                'mr_number' => $mrNumber,
                'request_date' => $request->request_date,
                'department_id' => $request->department_id,
                'employee_master_id' => $request->employee_master_id,
                'branch_id' => $request->branch_id,
                'company_id' => $branch ? $branch->company_id : null,
                'status' => 'created',
                'created_by' => Auth::id() ?: 1, // fallback to 1 if auth fails in testing
            ]);

            foreach ($request->items as $item) {
                MaterialRequestItem::create([
                    'material_request_id' => $mr->id,
                    'msu_item_master_id' => $item['msu_item_master_id'],
                    'requested_qty' => $item['requested_qty'],
                    'unit_id' => $item['unit_id'] ?? null,
                    'remarks' => $item['remarks'] ?? null,
                ]);
            }

            return $mr;
        });

        $mr->load(['department', 'employee', 'company', 'branch', 'items.itemMaster']);
        return $this->success($mr, 'Material Request created successfully', 201);
    }

    public function update(Request $request, string $id)
    {
        $materialRequest = MaterialRequest::findOrFail($id);
        
        if ($materialRequest->status !== 'created') {
            return $this->error('Only created requests can be edited.', 403);
        }

        $request->validate([
            'request_date' => 'required|date',
            'department_id' => 'nullable|exists:departments,id',
            'employee_master_id' => 'nullable|exists:employee_masters,id',
            'branch_id' => 'nullable|exists:branches,id',
            'items' => 'required|array|min:1',
            'items.*.msu_item_master_id' => 'required|exists:msu_item_masters,id',
            'items.*.requested_qty' => 'required|numeric|min:0.001',
            'items.*.unit_id' => 'nullable|exists:units,id',
        ]);

        foreach ($request->items as $index => $itemData) {
            $master = \App\Models\MsuItemMaster::find($itemData['msu_item_master_id']);
            if ($master && $itemData['requested_qty'] > $master->current_stock) {
                return $this->error("Requested quantity for '{$master->item_name}' cannot exceed available stock ({$master->current_stock}).", 422);
            }
        }

        DB::transaction(function () use ($request, $materialRequest) {
            $branch = \App\Models\Branch::find($request->branch_id);
            $materialRequest->update([
                'request_date' => $request->request_date,
                'department_id' => $request->department_id,
                'employee_master_id' => $request->employee_master_id,
                'branch_id' => $request->branch_id,
                'company_id' => $branch ? $branch->company_id : null,
            ]);

            $materialRequest->items()->delete();
            
            foreach ($request->items as $item) {
                MaterialRequestItem::create([
                    'material_request_id' => $materialRequest->id,
                    'msu_item_master_id' => $item['msu_item_master_id'],
                    'requested_qty' => $item['requested_qty'],
                    'unit_id' => $item['unit_id'] ?? null,
                    'remarks' => $item['remarks'] ?? null,
                ]);
            }
        });

        $materialRequest->load(['department', 'employee', 'company', 'branch', 'items.itemMaster']);
        return $this->success($materialRequest, 'Material Request updated successfully');
    }

    public function destroy(string $id)
    {
        $materialRequest = MaterialRequest::findOrFail($id);

        if ($materialRequest->status !== 'created') {
            return $this->error('Only created requests can be deleted.', 403);
        }

        $materialRequest->delete();
        return $this->success(null, 'Material Request deleted successfully');
    }

    public function approve(string $id)
    {
        $materialRequest = MaterialRequest::findOrFail($id);
        
        if ($materialRequest->status !== 'created') {
            return $this->error('Only created requests can be approved.', 403);
        }

        $materialRequest->status = 'approved';
        $materialRequest->save();

        return $this->success($materialRequest, 'Material Request approved successfully.');
    }

    public function cancel(Request $request, string $id)
    {
        $materialRequest = MaterialRequest::findOrFail($id);
        
        if ($materialRequest->status !== 'created') {
            return $this->error('This request cannot be cancelled.', 403);
        }

        $materialRequest->status = 'cancelled';
        $materialRequest->cancel_reason = $request->cancel_reason;
        $materialRequest->save();

        return $this->success($materialRequest, 'Material Request cancelled successfully.');
    }
}
