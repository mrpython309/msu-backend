<?php

namespace App\Http\Controllers;

use App\Exports\ItemDashboardExport;
use App\Models\MsuItemMaster;
use App\Models\MsuPurchaseOrder;
use App\Models\MsuGrn;
use App\Models\MaterialRequest;
use App\Models\MaterialIssue;
use App\Models\MsuStockMovement;
use App\Models\MsuStockAdjustment;
use App\Models\MsuDailyItemStock;
use App\Models\ProductCategory;
use App\Services\ReportPdfExportService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class ItemDashboardController extends Controller
{
    public function index(Request $request)
    {
        return view('item-dashboard.index', $this->dashboardPayload($request));
    }

    public function export(Request $request)
    {
        $payload = $this->dashboardPayload($request);

        return Excel::download(
            new ItemDashboardExport($payload),
            'item-dashboard-'.now()->format('Ymd_His').'.xlsx'
        );
    }

    public function exportPdf(Request $request, ReportPdfExportService $pdfExport)
    {
        $payload = $this->dashboardPayload($request);

        return $pdfExport->download(
            'item-dashboard.export-list',
            $payload,
            'item-dashboard-'.now()->format('Ymd_His').'.pdf',
            'landscape'
        );
    }

    protected function dashboardPayload(Request $request): array
    {
        $fromDate = $request->input('from_date', now()->toDateString());
        $toDate = $request->input('to_date', now()->toDateString());

        // --- 1. Category-wise Stock Summary ---
        $categories = ProductCategory::active()->get();
        
        $latestRates = \Illuminate\Support\Facades\DB::table('msu_item_rates as r1')
            ->select('r1.msu_item_master_id', 'r1.rate')
            ->leftJoin('msu_item_rates as r2', function($join) {
                $join->on('r1.msu_item_master_id', '=', 'r2.msu_item_master_id')
                     ->on('r1.effective_from', '<', 'r2.effective_from');
            })
            ->whereNull('r2.id');

        // Fetch base opening stocks grouped by category
        $baseOpenings = \Illuminate\Support\Facades\DB::table('msu_item_masters')
            ->leftJoinSub($latestRates, 'latest_rates', function($join) {
                $join->on('msu_item_masters.id', '=', 'latest_rates.msu_item_master_id');
            })
            ->where('status', 'active')
            ->groupBy('product_category_id')
            ->select(
                'product_category_id', 
                \Illuminate\Support\Facades\DB::raw('sum(opening_stock) as total_qty'),
                \Illuminate\Support\Facades\DB::raw('sum(opening_stock * COALESCE(latest_rates.rate, 0)) as total_val')
            )
            ->get()->keyBy('product_category_id');
        $receivedStocksBefore = collect();
        $issuedStocksBefore = collect();
        
        if ($fromDate) {
            $receivedStocksBefore = \Illuminate\Support\Facades\DB::table('msu_stock_movements')
                ->join('msu_item_masters', 'msu_stock_movements.msu_item_master_id', '=', 'msu_item_masters.id')
                ->leftJoinSub($latestRates, 'latest_rates', function($join) {
                    $join->on('msu_item_masters.id', '=', 'latest_rates.msu_item_master_id');
                })
                ->where('msu_stock_movements.transaction_type', 'in')
                ->whereDate('msu_stock_movements.created_at', '<', $fromDate)
                ->groupBy('msu_item_masters.product_category_id')
                ->select(
                    'msu_item_masters.product_category_id', 
                    \Illuminate\Support\Facades\DB::raw('sum(msu_stock_movements.quantity) as total_qty'),
                    \Illuminate\Support\Facades\DB::raw('sum(msu_stock_movements.quantity * COALESCE(latest_rates.rate, 0)) as total_val')
                )
                ->get()->keyBy('product_category_id');
                                
            $issuedStocksBefore = \Illuminate\Support\Facades\DB::table('msu_stock_movements')
                ->join('msu_item_masters', 'msu_stock_movements.msu_item_master_id', '=', 'msu_item_masters.id')
                ->leftJoinSub($latestRates, 'latest_rates', function($join) {
                    $join->on('msu_item_masters.id', '=', 'latest_rates.msu_item_master_id');
                })
                ->where('msu_stock_movements.transaction_type', 'out')
                ->whereDate('msu_stock_movements.created_at', '<', $fromDate)
                ->groupBy('msu_item_masters.product_category_id')
                ->select(
                    'msu_item_masters.product_category_id', 
                    \Illuminate\Support\Facades\DB::raw('sum(msu_stock_movements.quantity) as total_qty'),
                    \Illuminate\Support\Facades\DB::raw('sum(msu_stock_movements.quantity * COALESCE(latest_rates.rate, 0)) as total_val')
                )
                ->get()->keyBy('product_category_id');
        }

        $queryIn = \Illuminate\Support\Facades\DB::table('msu_stock_movements')
            ->join('msu_item_masters', 'msu_stock_movements.msu_item_master_id', '=', 'msu_item_masters.id')
            ->leftJoinSub($latestRates, 'latest_rates', function($join) {
                $join->on('msu_item_masters.id', '=', 'latest_rates.msu_item_master_id');
            })
            ->where('msu_stock_movements.transaction_type', 'in');
            
        $queryOut = \Illuminate\Support\Facades\DB::table('msu_stock_movements')
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
                \Illuminate\Support\Facades\DB::raw('sum(msu_stock_movements.quantity) as total_qty'),
                \Illuminate\Support\Facades\DB::raw('sum(msu_stock_movements.quantity * COALESCE(latest_rates.rate, 0)) as total_val')
            )
            ->get()->keyBy('product_category_id');
            
        $issuedStocksPeriod = $queryOut->groupBy('msu_item_masters.product_category_id')
            ->select(
                'msu_item_masters.product_category_id', 
                \Illuminate\Support\Facades\DB::raw('sum(msu_stock_movements.quantity) as total_qty'),
                \Illuminate\Support\Facades\DB::raw('sum(msu_stock_movements.quantity * COALESCE(latest_rates.rate, 0)) as total_val')
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
            
            $categorySummaries[$catId] = [
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

        // Helper to format up to 3 items
        $formatItemNames = function($items) {
            if ($items->isEmpty()) return 'N/A';
            $names = [];
            foreach ($items->take(3) as $item) {
                $names[] = $item->itemMaster ? $item->itemMaster->item_name : ($item->item_name ?? 'Unknown Item');
            }
            $nameStr = implode(', ', $names);
            if ($items->count() > 3) {
                $nameStr .= '.....';
            }
            return $nameStr;
        };

        // --- 2. Recent Activity ---
        $activities = [];

        // 1. Items (Created)
        $items = MsuItemMaster::latest('created_at')->take(10)->get();
        foreach ($items as $item) {
            $activities[] = [
                'item_name' => $item->item_name,
                'action' => 'Item Created',
                'date_time' => $item->created_at,
                'url' => route('item-masters.show', $item->id),
            ];
        }

        // 2. Items (Deleted)
        $deletedItems = MsuItemMaster::onlyTrashed()->latest('deleted_at')->take(10)->get();
        foreach ($deletedItems as $item) {
            $activities[] = [
                'item_name' => $item->item_name,
                'action' => 'Item Deleted',
                'date_time' => $item->deleted_at,
                'url' => null,
            ];
        }

        // 6. Stock Movements (Received/Issued)
        $movements = \App\Models\MsuStockMovement::with('itemMaster')->latest('created_at')->take(10)->get();
        foreach ($movements as $mov) {
            $action = $mov->transaction_type === 'in' ? 'Stock Received' : 'Stock Issued';
            if ($mov->reference_type) {
                $refClass = class_basename($mov->reference_type);
                if (str_contains($refClass, 'Adjustment')) $action = 'Stock Adjustment';
            }
            $url = null;
            if ($mov->reference_type && $mov->reference_id) {
                $refClass = class_basename($mov->reference_type);
                if (str_contains($refClass, 'MsuGrn')) {
                    $url = route('msu-grns.show', $mov->reference_id);
                } elseif (str_contains($refClass, 'MaterialIssue')) {
                    $url = route('material-issues.show', $mov->reference_id);
                } elseif (str_contains($refClass, 'StockAdjustment')) {
                    $url = route('stock-adjustments.show', $mov->reference_id);
                }
            }
            if (!$url && $mov->msu_item_master_id) {
                $url = route('item-masters.show', $mov->msu_item_master_id);
            }
            
            $activities[] = [
                'item_name' => $mov->itemMaster ? $mov->itemMaster->item_name : 'Unknown Item',
                'action' => $action . ' (' . (float)$mov->quantity . ')',
                'date_time' => $mov->created_at,
                'url' => $url,
            ];
        }

        // Sort all activities by date_time descending
        usort($activities, function($a, $b) {
            return $b['date_time'] <=> $a['date_time'];
        });

        // Take only top 10
        $recentActivities = array_slice($activities, 0, 10);

        $lowStockItems = MsuItemMaster::query()
            ->active()
            ->whereStockStatus('low')
            ->orderBy('item_name')
            ->get();

        return compact('categorySummaries', 'recentActivities', 'lowStockItems', 'fromDate', 'toDate');
    }
}
