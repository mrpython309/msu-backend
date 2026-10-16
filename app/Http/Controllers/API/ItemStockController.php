<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MsuItemMaster;
use App\Models\MsuStockMovement;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;

class ItemStockController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $query = MsuItemMaster::with(['unit', 'hsn', 'productCategory', 'productSubCategory', 'rates'])
            ->active()
            ->orderBy('id', 'desc');

        if ($request->filled('item_name')) {
            $query->where('item_name', 'like', '%' . $request->item_name . '%');
        }
        
        if ($request->filled('item_code')) {
            $query->where('item_code', 'like', '%' . $request->item_code . '%');
        }
        
        if ($request->filled('hsn_id')) {
            $query->where('hsn_id', $request->hsn_id);
        }
        
        if ($request->filled('unit_id')) {
            $query->where('unit_id', $request->unit_id);
        }
        
        if ($request->filled('product_category_id')) {
            $query->where('product_category_id', $request->product_category_id);
        }
        
        if ($request->filled('product_sub_category_id')) {
            $query->where('product_sub_category_id', $request->product_sub_category_id);
        }

        $perPage = $request->input('per_page', 10);
        $items = $query->paginate($perPage);

        $filterDate = $request->input('filter_date', now()->toDateString());
        $itemIds = $items->pluck('id');
        
        $receivedStocks = MsuStockMovement::whereIn('msu_item_master_id', $itemIds)
            ->where('transaction_type', 'in')
            ->where('reference_type', \App\Models\MsuGrn::class)
            ->whereDate('created_at', $filterDate)
            ->selectRaw('msu_item_master_id, SUM(quantity) as total_received')
            ->groupBy('msu_item_master_id')
            ->pluck('total_received', 'msu_item_master_id');
            
        $issuedStocks = MsuStockMovement::whereIn('msu_item_master_id', $itemIds)
            ->where('transaction_type', 'out')
            ->where('reference_type', \App\Models\MaterialIssue::class)
            ->whereDate('created_at', $filterDate)
            ->selectRaw('msu_item_master_id, SUM(quantity) as total_issued')
            ->groupBy('msu_item_master_id')
            ->pluck('total_issued', 'msu_item_master_id');

        $latestStocks = \App\Models\MsuDailyItemStock::whereIn('msu_item_master_id', $itemIds)
            ->where('date', '<=', $filterDate)
            ->orderBy('date', 'desc')
            ->get()
            ->unique('msu_item_master_id')
            ->keyBy('msu_item_master_id');

        $items->getCollection()->transform(function ($item) use ($filterDate, $latestStocks, $receivedStocks, $issuedStocks) {
            $latestStock = $latestStocks[$item->id] ?? null;
            if ($latestStock && $latestStock->date->toDateString() === $filterDate) {
                $opening = (float) $latestStock->opening_stock;
                $closing = (float) $latestStock->closing_stock;
            } elseif ($latestStock) {
                $opening = (float) $latestStock->closing_stock;
                $closing = (float) $latestStock->closing_stock;
            } else {
                $opening = (float) $item->opening_stock;
                $closing = (float) $item->opening_stock;
            }

            $item->opening_qty = $opening;
            $item->closing_qty = $closing;
            $item->received_qty = (float)($receivedStocks[$item->id] ?? 0);
            $item->issued_qty = (float)($issuedStocks[$item->id] ?? 0);
            
            return $item;
        });

        // Optional: Aggregate totals for the items on this page (if needed by frontend)
        $totals = [
            'total_opening' => $items->sum('opening_qty'),
            'total_closing' => $items->sum('closing_qty'),
            'total_received' => $items->sum('received_qty'),
            'total_issued' => $items->sum('issued_qty'),
        ];

        return $this->successWithPagination($items, 'Item stocks retrieved', 200, $totals);
    }

    public function show($id)
    {
        $item = MsuItemMaster::with(['unit', 'hsn', 'productCategory', 'productSubCategory'])->findOrFail($id);
        
        $movements = MsuStockMovement::with('createdBy')
            ->where('msu_item_master_id', $id)
            ->orderBy('id', 'desc')
            ->paginate(15);
            
        $totalIn = MsuStockMovement::where('msu_item_master_id', $id)
            ->where('transaction_type', 'in')
            ->sum('quantity');
            
        $totalOut = MsuStockMovement::where('msu_item_master_id', $id)
            ->where('transaction_type', 'out')
            ->sum('quantity');

        $dailyStocks = \App\Models\MsuDailyItemStock::where('msu_item_master_id', $id)
            ->orderBy('date', 'desc')
            ->take(30) // Limit to last 30 days for API efficiency
            ->get();
            
        $todayStock = $dailyStocks->first(function ($stock) {
            return $stock->date->isToday();
        });

        return $this->success([
            'item' => $item,
            'total_in' => (float)$totalIn,
            'total_out' => (float)$totalOut,
            'today_stock' => $todayStock,
            'recent_daily_stocks' => $dailyStocks,
            'movements' => [
                'data' => $movements->items(),
                'meta' => [
                    'current_page' => $movements->currentPage(),
                    'last_page' => $movements->lastPage(),
                    'per_page' => $movements->perPage(),
                    'total' => $movements->total(),
                ]
            ]
        ]);
    }
}
