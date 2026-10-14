<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MsuItemMaster;
use App\Models\MsuStockAdjustment;
use App\Models\MsuStockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Traits\ApiResponse;

class MsuStockAdjustmentController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = MsuStockAdjustment::with(['itemMaster', 'employee', 'department', 'createdBy'])
            ->orderBy('id', 'desc');

        if ($request->filled('item_name')) {
            $query->whereHas('itemMaster', function ($q) use ($request) {
                $q->where('item_name', 'like', '%' . $request->item_name . '%')
                  ->orWhere('item_code', 'like', '%' . $request->item_name . '%');
            });
        }

        if ($request->filled('from_date')) {
            $query->whereDate('adjustment_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('adjustment_date', '<=', $request->to_date);
        }

        $perPage = $request->input('per_page', 15);
        $adjustments = $query->paginate($perPage);

        return $this->successWithPagination($adjustments);
    }

    public function show(string $id)
    {
        $stockAdjustment = MsuStockAdjustment::with(['itemMaster', 'employee', 'department', 'createdBy'])->findOrFail($id);
        return $this->success($stockAdjustment);
    }

    public function store(Request $request)
    {
        $request->validate([
            'adjustment_date' => 'required|date',
            'msu_item_master_id' => 'required|exists:msu_item_masters,id',
            'adjustment_type' => 'required|in:increment,decrement',
            'quantity' => 'required|numeric|min:0.001',
            'department_id' => 'nullable|exists:departments,id',
            'employee_master_id' => 'nullable|exists:employee_masters,id',
            'remarks' => 'nullable|string|max:1000',
        ]);

        $item = MsuItemMaster::with(['unit', 'hsn'])->findOrFail($request->msu_item_master_id);

        if ($request->adjustment_type === 'decrement') {
            if ($item->current_stock < $request->quantity) {
                return $this->error('Insufficient stock for decrement. Current stock is ' . number_format($item->current_stock, 3), 422);
            }
        }

        try {
            $adjustment = DB::transaction(function () use ($request, $item) {
                $pricing = $item->commercialSnapshot($request->quantity, $request->adjustment_date);

                $adjustment = MsuStockAdjustment::create([
                    'adjustment_date' => $request->adjustment_date,
                    'adjustment_type' => $request->adjustment_type,
                    'msu_item_master_id' => $request->msu_item_master_id,
                    'quantity' => $request->quantity,
                    'unit' => $pricing['unit'],
                    'price' => $pricing['price'],
                    'total' => $pricing['total'],
                    'gst_percentage' => $pricing['gst_percentage'],
                    'gst_amount' => $pricing['gst_amount'],
                    'grand_total' => $pricing['grand_total'],
                    'department_id' => $request->department_id,
                    'employee_master_id' => $request->employee_master_id,
                    'remarks' => $request->remarks,
                    'created_by' => Auth::id() ?: 1,
                ]);

                // Update Item Master Stock directly since they don't seem to have a separate sync function for this
                $stockBefore = $item->current_stock;
                $stockAfter = $request->adjustment_type === 'increment' 
                    ? $stockBefore + $request->quantity 
                    : $stockBefore - $request->quantity;
                    
                $item->current_stock = $stockAfter;
                $item->save();

                // Create stock movement
                MsuStockMovement::create([
                    'msu_item_master_id' => $item->id,
                    'transaction_type' => $request->adjustment_type === 'increment' ? 'in' : 'out',
                    'quantity' => $request->quantity,
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'reference_type' => MsuStockAdjustment::class,
                    'reference_id' => $adjustment->id,
                    'remarks' => 'Stock ' . ucfirst($request->adjustment_type) . ' (' . $adjustment->adjustment_number . ')',
                    'created_by' => Auth::id() ?: 1,
                ]);

                return $adjustment;
            });
            
            $adjustment->load(['itemMaster', 'employee', 'department', 'createdBy']);
            return $this->success($adjustment, 'Stock adjustment created successfully.', 201);
            
        } catch (\Exception $e) {
            return $this->error('Error creating stock adjustment: ' . $e->getMessage(), 422);
        }
    }
}
