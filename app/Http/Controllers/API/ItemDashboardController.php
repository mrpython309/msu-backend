<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\MsuItemMaster;
use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Traits\ApiResponse;

class ItemDashboardController extends Controller
{
    use ApiResponse;

    public function index(Request $request)
    {
        $fromDate = $request->input('from_date');
        $toDate = $request->input('to_date');

        $categories = ProductCategory::where('status', 'active')->get();

        $latestRates = DB::table('msu_item_rates')
            ->select('msu_item_master_id', 'rate')
            ->whereIn('id', function($q) {
                $q->select(DB::raw('MAX(id)'))
                  ->from('msu_item_rates')
                  ->groupBy('msu_item_master_id');
            });

        $queryBase = DB::table('msu_item_masters')
            ->leftJoinSub($latestRates, 'latest_rates', function($join) {
                $join->on('msu_item_masters.id', '=', 'latest_rates.msu_item_master_id');
            })
            ->whereNull('msu_item_masters.deleted_at')
            ->groupBy('msu_item_masters.product_category_id')
            ->select(
                'msu_item_masters.product_category_id',
                DB::raw('sum(msu_item_masters.opening_stock) as total_qty'),
                DB::raw('sum(msu_item_masters.opening_stock * COALESCE(latest_rates.rate, 0)) as total_val')
            );
        $baseOpenings = $queryBase->get()->keyBy('product_category_id');

        $queryInBefore = DB::table('msu_stock_movements')
            ->join('msu_item_masters', 'msu_stock_movements.msu_item_master_id', '=', 'msu_item_masters.id')
            ->leftJoinSub($latestRates, 'latest_rates', function($join) {
                $join->on('msu_item_masters.id', '=', 'latest_rates.msu_item_master_id');
            })
            ->where('msu_stock_movements.transaction_type', 'in');

        $queryOutBefore = DB::table('msu_stock_movements')
            ->join('msu_item_masters', 'msu_stock_movements.msu_item_master_id', '=', 'msu_item_masters.id')
            ->leftJoinSub($latestRates, 'latest_rates', function($join) {
                $join->on('msu_item_masters.id', '=', 'latest_rates.msu_item_master_id');
            })
            ->where('msu_stock_movements.transaction_type', 'out');

        if ($fromDate) {
            $queryInBefore->whereDate('msu_stock_movements.created_at', '<', $fromDate);
            $queryOutBefore->whereDate('msu_stock_movements.created_at', '<', $fromDate);
        } else {
            $queryInBefore->whereRaw('1 = 0');
            $queryOutBefore->whereRaw('1 = 0');
        }

        $receivedStocksBefore = $queryInBefore->groupBy('msu_item_masters.product_category_id')
            ->select(
                'msu_item_masters.product_category_id', 
                DB::raw('sum(msu_stock_movements.quantity) as total_qty'),
                DB::raw('sum(msu_stock_movements.quantity * COALESCE(latest_rates.rate, 0)) as total_val')
            )
            ->get()->keyBy('product_category_id');

        $issuedStocksBefore = $queryOutBefore->groupBy('msu_item_masters.product_category_id')
            ->select(
                'msu_item_masters.product_category_id', 
                DB::raw('sum(msu_stock_movements.quantity) as total_qty'),
                DB::raw('sum(msu_stock_movements.quantity * COALESCE(latest_rates.rate, 0)) as total_val')
            )
            ->get()->keyBy('product_category_id');

        $queryIn = DB::table('msu_stock_movements')
            ->join('msu_item_masters', 'msu_stock_movements.msu_item_master_id', '=', 'msu_item_masters.id')
            ->leftJoinSub($latestRates, 'latest_rates', function($join) {
                $join->on('msu_item_masters.id', '=', 'latest_rates.msu_item_master_id');
            })
            ->where('msu_stock_movements.transaction_type', 'in');
            
        $queryOut = DB::table('msu_stock_movements')
            ->join('msu_item_masters', 'msu_stock_movements.msu_item_master_id', '=', 'msu_item_masters.id')
            ->leftJoinSub($latestRates, 'latest_rates', function($join) {
                $join->on('msu_item_masters.id', '=', 'latest_rates.msu_item_master_id');
            })
            ->where('msu_stock_movements.transaction_type', 'out');
        
        if ($fromDate) {
            $queryIn->whereDate('msu_stock_movements.created_at', '>=', $fromDate);
            $queryOut->whereDate('msu_stock_movements.created_at', '>=', $fromDate);
        }
        if ($toDate) {
            $queryIn->whereDate('msu_stock_movements.created_at', '<=', $toDate);
            $queryOut->whereDate('msu_stock_movements.created_at', '<=', $toDate);
        }
        
        $receivedStocksPeriod = $queryIn->groupBy('msu_item_masters.product_category_id')
            ->select(
                'msu_item_masters.product_category_id', 
                DB::raw('sum(msu_stock_movements.quantity) as total_qty'),
                DB::raw('sum(msu_stock_movements.quantity * COALESCE(latest_rates.rate, 0)) as total_val')
            )
            ->get()->keyBy('product_category_id');
            
        $issuedStocksPeriod = $queryOut->groupBy('msu_item_masters.product_category_id')
            ->select(
                'msu_item_masters.product_category_id', 
                DB::raw('sum(msu_stock_movements.quantity) as total_qty'),
                DB::raw('sum(msu_stock_movements.quantity * COALESCE(latest_rates.rate, 0)) as total_val')
            )
            ->get()->keyBy('product_category_id');

        $categorySummaries = [];
        
        foreach ($categories as $category) {
            $catId = $category->id;
            $baseOpeningQty = (float) ($baseOpenings[$catId]->total_qty ?? 0);
            $baseOpeningVal = (float) ($baseOpenings[$catId]->total_val ?? 0);
            
            $receivedBeforeQty = (float) ($receivedStocksBefore[$catId]->total_qty ?? 0);
            $receivedBeforeVal = (float) ($receivedStocksBefore[$catId]->total_val ?? 0);
            
            $issuedBeforeQty = (float) ($issuedStocksBefore[$catId]->total_qty ?? 0);
            $issuedBeforeVal = (float) ($issuedStocksBefore[$catId]->total_val ?? 0);
            
            $openingQty = $baseOpeningQty + $receivedBeforeQty - $issuedBeforeQty;
            $openingVal = $baseOpeningVal + $receivedBeforeVal - $issuedBeforeVal;
            
            $receivedQty = (float) ($receivedStocksPeriod[$catId]->total_qty ?? 0);
            $receivedVal = (float) ($receivedStocksPeriod[$catId]->total_val ?? 0);
            
            $issuedQty = (float) ($issuedStocksPeriod[$catId]->total_qty ?? 0);
            $issuedVal = (float) ($issuedStocksPeriod[$catId]->total_val ?? 0);
            
            $closingQty = $openingQty + $receivedQty - $issuedQty;
            $closingVal = $openingVal + $receivedVal - $issuedVal;
            
            $categorySummaries[] = [
                'id' => $catId,
                'name' => $category->category_name,
                'opening_qty' => $openingQty,
                'opening_val' => $openingVal,
                'received_qty' => $receivedQty,
                'received_val' => $receivedVal,
                'issued_qty' => $issuedQty,
                'issued_val' => $issuedVal,
                'closing_qty' => $closingQty,
                'closing_val' => $closingVal,
            ];
        }

        $activities = [];

        $items = MsuItemMaster::withTrashed()->latest('updated_at')->take(10)->get();
        foreach ($items as $item) {
            $action = 'Item Created';
            $date = $item->created_at;

            if ($item->trashed()) {
                $action = 'Item Deleted';
                $date = $item->deleted_at;
            } elseif ($item->updated_at->gt($item->created_at)) {
                $causedByStockMovement = \App\Models\MsuStockMovement::where('msu_item_master_id', $item->id)
                    ->whereBetween('created_at', [
                        $item->updated_at->copy()->subSeconds(2), 
                        $item->updated_at->copy()->addSeconds(2)
                    ])->exists();

                if ($causedByStockMovement) {
                    $action = 'Item Created';
                    $date = $item->created_at;
                } else {
                    $action = 'Item Updated';
                    $date = $item->updated_at;
                }
            }

            $activities[] = [
                'item_name' => $item->item_name,
                'action' => $action,
                'date_time' => $date,
                'reference_id' => $item->id,
                'reference_type' => 'msu_item_masters'
            ];
        }

        $movements = \App\Models\MsuStockMovement::with('itemMaster')->latest('created_at')->take(10)->get();
        foreach ($movements as $mov) {
            $action = $mov->transaction_type === 'in' ? 'Stock Received' : 'Stock Issued';
            if ($mov->reference_type) {
                $refClass = class_basename($mov->reference_type);
                if (str_contains($refClass, 'Adjustment')) $action = 'Stock Adjustment';
            }
            
            $refId = $mov->msu_item_master_id;
            $refType = 'msu_item_masters';

            if ($mov->reference_type && $mov->reference_id) {
                $refClass = class_basename($mov->reference_type);
                if (str_contains($refClass, 'MsuGrn')) {
                    $refId = $mov->reference_id;
                    $refType = 'msu_grns';
                } elseif (str_contains($refClass, 'MaterialIssue')) {
                    $refId = $mov->reference_id;
                    $refType = 'material_issues';
                } elseif (str_contains($refClass, 'StockAdjustment')) {
                    $refId = $mov->reference_id;
                    $refType = 'stock_adjustments';
                }
            }
            
            $activities[] = [
                'item_name' => $mov->itemMaster ? $mov->itemMaster->item_name : 'Unknown Item',
                'action' => $action . ' (' . (float)$mov->quantity . ')',
                'date_time' => $mov->created_at,
                'reference_id' => $refId,
                'reference_type' => $refType,
            ];
        }

        usort($activities, function($a, $b) {
            return $b['date_time'] <=> $a['date_time'];
        });

        $recentActivities = array_slice($activities, 0, 10);

        $lowStockItems = MsuItemMaster::query()
            ->active()
            ->whereStockStatus('low')
            ->orderBy('item_name')
            ->get()
            ->map(fn (MsuItemMaster $item) => [
                'item_name' => $item->item_name,
                'item_code' => $item->item_code,
                'current_stock' => (float) $item->current_stock,
                'threshold_quantity' => (float) $item->threshold_quantity,
                'status' => 'Low Stock',
            ])
            ->values();

        return $this->success([
            'category_summaries' => $categorySummaries,
            'recent_activities' => $recentActivities,
            'low_stock_items' => $lowStockItems,
        ]);
    }
}
