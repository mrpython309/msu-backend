<?php

namespace App\Http\Controllers;

use App\Models\MsuItemMaster;
use App\Models\MsuStockMovement;
use App\Models\Company;
use App\Models\Branch;
use App\Services\ReportPdfExportService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;

class ItemStockController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('permission:item-stocks:view', only: ['index', 'show', 'export', 'exportPdf']),
        ];
    }

    public function index(Request $request)
    {
        $query = $this->filteredItemsQuery($request);

        // Clone before paginate so summary cards use all filtered items (not current page only).
        $summaryQuery = clone $query;

        $perPage = $request->input('per_page', 10);
        $items = $query->paginate($perPage)->withQueryString();

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

        $adjustedStocks = MsuStockMovement::whereIn('msu_item_master_id', $itemIds)
            ->where('reference_type', \App\Models\MsuStockAdjustment::class)
            ->whereDate('created_at', $filterDate)
            ->selectRaw("msu_item_master_id, SUM(CASE WHEN transaction_type = 'in' THEN quantity ELSE -quantity END) as total_adjusted")
            ->groupBy('msu_item_master_id')
            ->pluck('total_adjusted', 'msu_item_master_id');

        $allFilteredItems = $summaryQuery->with(['rates' => function ($q) {
            $q->orderBy('effective_from', 'desc');
        }])->get(['id', 'opening_stock']);
        
        $allFilteredItemIds = $allFilteredItems->pluck('id');
        
        $latestStocks = \App\Models\MsuDailyItemStock::whereIn('msu_item_master_id', $allFilteredItemIds)
            ->where('date', '<=', $filterDate)
            ->orderBy('date', 'desc')
            ->get()
            ->unique('msu_item_master_id')
            ->keyBy('msu_item_master_id');
            
        $allReceived = MsuStockMovement::whereIn('msu_item_master_id', $allFilteredItemIds)
            ->where('transaction_type', 'in')
            ->where('reference_type', \App\Models\MsuGrn::class)
            ->whereDate('created_at', $filterDate)
            ->selectRaw('msu_item_master_id, SUM(quantity) as qty')
            ->groupBy('msu_item_master_id')
            ->pluck('qty', 'msu_item_master_id');
            
        $allIssued = MsuStockMovement::whereIn('msu_item_master_id', $allFilteredItemIds)
            ->where('transaction_type', 'out')
            ->where('reference_type', \App\Models\MaterialIssue::class)
            ->whereDate('created_at', $filterDate)
            ->selectRaw('msu_item_master_id, SUM(quantity) as qty')
            ->groupBy('msu_item_master_id')
            ->pluck('qty', 'msu_item_master_id');

        $allAdjusted = MsuStockMovement::whereIn('msu_item_master_id', $allFilteredItemIds)
            ->where('reference_type', \App\Models\MsuStockAdjustment::class)
            ->whereDate('created_at', $filterDate)
            ->selectRaw("msu_item_master_id, SUM(CASE WHEN transaction_type = 'in' THEN quantity ELSE -quantity END) as qty")
            ->groupBy('msu_item_master_id')
            ->pluck('qty', 'msu_item_master_id');

        $totalOpening = 0;
        $totalClosing = 0;
        $totalReceived = 0;
        $totalIssued = 0;
        
        foreach ($allFilteredItems as $item) {
            $latestStock = $latestStocks[$item->id] ?? null;
            $received = (float)($allReceived[$item->id] ?? 0);
            $issued = (float)($allIssued[$item->id] ?? 0);
            $adjusted = (float)($allAdjusted[$item->id] ?? 0);

            if ($latestStock && $latestStock->date->toDateString() === $filterDate) {
                $opening = (float) $latestStock->opening_stock;
                $closing = (float) $latestStock->closing_stock;
            } elseif ($latestStock) {
                $opening = (float) $latestStock->closing_stock;
                $closing = $opening + $received - $issued + $adjusted;
            } else {
                $opening = (float) $item->opening_stock;
                $closing = $opening + $received - $issued + $adjusted;
            }
            
            $totalOpening += $opening;
            $totalClosing += $closing;
            $totalReceived += $received;
            $totalIssued += $issued;
        }

        $companies = Company::visibleToUser(auth()->user())->where('status', 'active')->get();
        $branches = Branch::visibleToUser(auth()->user())
            ->where('status', 'active')
            ->when(session()->has('global_company_id'), function ($q) {
                $q->where('company_id', session('global_company_id'));
            })->get();

        $units = \App\Models\Unit::where('status', 'active')->get();
        $hsns = \App\Models\Hsn::where('status', 'active')->get();
        $productCategories = \App\Models\ProductCategory::where('status', 'active')->get();
        $productSubCategories = \App\Models\ProductSubCategory::where('status', 'active')->get();

        $allItems = MsuItemMaster::select('id', 'item_code', 'item_name')->active()->get();

        return view('item_stocks.index', compact(
            'items', 'receivedStocks', 'issuedStocks', 'adjustedStocks', 'perPage', 'companies', 'branches',
            'totalOpening', 'totalClosing', 'totalReceived', 'totalIssued', 'latestStocks', 'filterDate',
            'units', 'hsns', 'productCategories', 'productSubCategories', 'allItems'
        ));
    }

    public function show($id)
    {
        $item = MsuItemMaster::with('unit')->findOrFail($id);
        
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
            ->get();
            
        $todayStock = $dailyStocks->first(function ($stock) {
            return $stock->date->isToday();
        });

        return view('item_stocks.show', compact('item', 'movements', 'totalIn', 'totalOut', 'dailyStocks', 'todayStock'));
    }

    public function export(Request $request)
    {
        $rows = $this->buildStockRows($request);

        $export = new class($rows) implements FromCollection, WithHeadings
        {
            public function __construct(private readonly Collection $rows) {}

            public function collection()
            {
                return $this->rows->map(fn ($row) => [
                    $row->sno,
                    $row->item_code,
                    $row->item_name,
                    $row->hsn,
                    $row->unit,
                    $row->price,
                    $row->category,
                    $row->sub_category,
                    $row->opening,
                    $row->adjusted,
                    $row->received,
                    $row->issued,
                    $row->closing,
                    $row->stock_status,
                ]);
            }

            public function headings(): array
            {
                return [
                    'S.No',
                    'Item Code',
                    'Item Name',
                    'HSN',
                    'Unit',
                    'Price',
                    'Product Category',
                    'Product Sub Category',
                    'Opening Stock',
                    'Adjusted Stocks',
                    'Received Stocks',
                    'Issued Stock',
                    'Closing Stock',
                    'Stock Status',
                ];
            }
        };

        return Excel::download($export, 'item-stocks-'.now()->format('Ymd_His').'.xlsx');
    }

    public function exportPdf(Request $request, ReportPdfExportService $pdfExport)
    {
        $rows = $this->buildStockRows($request);
        $filterDate = $request->input('filter_date', now()->toDateString());

        return $pdfExport->download(
            'item_stocks.export-list',
            compact('rows', 'filterDate'),
            'item-stocks-'.now()->format('Ymd_His').'.pdf',
            'landscape'
        );
    }

    protected function filteredItemsQuery(Request $request): Builder
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

        if ($request->filled('stock_status')) {
            $query->whereStockStatus($request->input('stock_status'));
        }

        return $query;
    }

    protected function buildStockRows(Request $request): Collection
    {
        $filterDate = $request->input('filter_date', now()->toDateString());
        $items = $this->filteredItemsQuery($request)->get();
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

        $adjustedStocks = MsuStockMovement::whereIn('msu_item_master_id', $itemIds)
            ->where('reference_type', \App\Models\MsuStockAdjustment::class)
            ->whereDate('created_at', $filterDate)
            ->selectRaw("msu_item_master_id, SUM(CASE WHEN transaction_type = 'in' THEN quantity ELSE -quantity END) as total_adjusted")
            ->groupBy('msu_item_master_id')
            ->pluck('total_adjusted', 'msu_item_master_id');

        $latestStocks = \App\Models\MsuDailyItemStock::whereIn('msu_item_master_id', $itemIds)
            ->where('date', '<=', $filterDate)
            ->orderBy('date', 'desc')
            ->get()
            ->unique('msu_item_master_id')
            ->keyBy('msu_item_master_id');

        return $items->values()->map(function ($item, $index) use ($latestStocks, $receivedStocks, $issuedStocks, $adjustedStocks, $filterDate) {
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

            $received = (float) ($receivedStocks[$item->id] ?? 0);
            $issued = (float) ($issuedStocks[$item->id] ?? 0);
            $adjusted = (float) ($adjustedStocks[$item->id] ?? 0);

            if (! $latestStock || $latestStock->date->toDateString() !== $filterDate) {
                $closing = $opening + $received - $issued + $adjusted;
            }

            return (object) [
                'sno' => $index + 1,
                'item_code' => $item->item_code,
                'item_name' => $item->item_name,
                'hsn' => $item->hsn?->hsn_code ?? '--',
                'unit' => $item->unit?->unit_name ?? $item->uom,
                'price' => (float) ($item->rates->first()?->rate ?? 0),
                'category' => $item->productCategory?->category_name ?? '--',
                'sub_category' => $item->productSubCategory?->sub_category_name ?? '--',
                'opening' => $opening,
                'adjusted' => $adjusted,
                'received' => $received,
                'issued' => $issued,
                'closing' => $closing,
                'stock_status' => $item->stock_status,
            ];
        });
    }
}
