<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\EmployeeMaster;
use App\Models\MsuItemMaster;
use App\Models\MsuStockAdjustment;
use App\Models\MsuStockMovement;
use App\Models\Company;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;

class MsuStockAdjustmentController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('auth'),
            new Middleware('permission:stock-adjustments:view', only: ['index', 'show', 'export', 'exportPdf']),
            new Middleware('permission:stock-adjustments:create', only: ['create', 'store']),
        ];
    }

    public function index(Request $request)
    {
        $perPage = $request->input('per_page', 15);
        $adjustments = $this->filteredAdjustments($request)->paginate($perPage)->withQueryString();
            
        $companies = Company::visibleToUser(auth()->user())->where('status', 'active')->get();
        $branches = Branch::visibleToUser(auth()->user())
            ->where('status', 'active')
            ->when(session()->has('global_company_id'), function ($q) {
                $q->where('company_id', session('global_company_id'));
            })->get();

        $allItems = MsuItemMaster::select('id', 'item_code', 'item_name')->active()->get();

        return view('stock-adjustments.index', compact('adjustments', 'companies', 'branches', 'allItems'));
    }

    public function create()
    {
        $items = MsuItemMaster::active()
            ->with(['unit', 'hsn', 'rates'])
            ->orderBy('item_name')
            ->get();
        $itemPricing = $items->mapWithKeys(fn (MsuItemMaster $item) => [
            $item->id => [
                'unit' => $item->unit?->unit_name ?: ($item->uom ?: ''),
                'hsn_tax' => $item->hsn?->tax_rate !== null ? (float) $item->hsn->tax_rate : null,
                'rates' => $item->rates->map(fn ($rate) => [
                    'id' => $rate->id,
                    'effective_from' => optional($rate->effective_from)->toDateString(),
                    'rate' => (float) $rate->rate,
                    'tax' => $rate->tax !== null ? (float) $rate->tax : null,
                ])->values(),
            ],
        ]);
        $departments = Department::orderBy('department_name')->get();
        $employees = EmployeeMaster::orderBy('employee_name')->get();

        return view('stock-adjustments.create', compact('items', 'departments', 'employees', 'itemPricing'));
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
                return back()->withInput()->withErrors(['quantity' => 'Insufficient stock for decrement. Current stock is ' . number_format($item->current_stock, 3)]);
            }
        }

        try {
            DB::beginTransaction();

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
                'created_by' => Auth::id(),
            ]);

            // Create stock movement
            MsuStockMovement::create([
                'msu_item_master_id' => $item->id,
                'transaction_type' => $request->adjustment_type === 'increment' ? 'in' : 'out',
                'quantity' => $request->quantity,
                'stock_before' => $item->current_stock,
                'stock_after' => $request->adjustment_type === 'increment' 
                    ? $item->current_stock + $request->quantity 
                    : $item->current_stock - $request->quantity,
                'reference_type' => MsuStockAdjustment::class,
                'reference_id' => $adjustment->id,
                'remarks' => 'Stock ' . ucfirst($request->adjustment_type) . ' (' . $adjustment->adjustment_number . ')',
                'created_by' => Auth::id(),
            ]);

            DB::commit();

            return redirect()->route('stock-adjustments.index')->with('success', 'Stock adjustment created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Error creating stock adjustment: ' . $e->getMessage());
        }
    }

    public function show(MsuStockAdjustment $stockAdjustment)
    {
        $stockAdjustment->load(['itemMaster', 'employee', 'department', 'createdBy']);
        return view('stock-adjustments.show', compact('stockAdjustment'));
    }

    public function export(Request $request)
    {
        $rows = $this->filteredAdjustments($request)->get();

        $export = new class($rows) implements FromCollection, WithHeadings
        {
            public function __construct(private readonly Collection $rows) {}

            public function collection()
            {
                return $this->rows->values()->map(fn (MsuStockAdjustment $adjustment, int $index) => [
                    $index + 1,
                    $adjustment->adjustment_number,
                    $adjustment->adjustment_date?->format('d-m-Y'),
                    ucfirst($adjustment->adjustment_type),
                    $adjustment->itemMaster?->item_code,
                    $adjustment->itemMaster?->item_name,
                    $adjustment->unit,
                    number_format((float) $adjustment->quantity, 3, '.', ''),
                    number_format((float) $adjustment->price, 2, '.', ''),
                    number_format((float) $adjustment->total, 2, '.', ''),
                    number_format((float) $adjustment->gst_percentage, 2, '.', ''),
                    number_format((float) $adjustment->gst_amount, 2, '.', ''),
                    number_format((float) $adjustment->grand_total, 2, '.', ''),
                    $adjustment->employee?->employee_name,
                    $adjustment->department?->department_name,
                    $adjustment->remarks,
                ]);
            }

            public function headings(): array
            {
                return [
                    'S.No',
                    'Adjustment Number',
                    'Date',
                    'Type',
                    'Item Code',
                    'Item Name',
                    'Unit',
                    'Quantity',
                    'Price',
                    'Total',
                    'GST %',
                    'GST Amount',
                    'Grand Total',
                    'Employee',
                    'Department',
                    'Remarks',
                ];
            }
        };

        return Excel::download($export, 'stock-adjustments-'.now()->format('Ymd_His').'.xlsx');
    }

    public function exportPdf(Request $request, \App\Services\ReportPdfExportService $pdfExport)
    {
        $adjustments = $this->filteredAdjustments($request)->get();
        $filters = $request->only(['item_name', 'from_date', 'to_date']);

        return $pdfExport->download(
            'stock-adjustments.export-list',
            compact('adjustments', 'filters'),
            'stock-adjustments-'.now()->format('Ymd_His').'.pdf',
            'landscape'
        );
    }

    private function filteredAdjustments(Request $request)
    {
        $query = MsuStockAdjustment::with(['itemMaster', 'employee', 'department', 'createdBy'])
            ->orderBy('id', 'desc');

        if ($request->filled('id')) {
            $query->where('id', $request->id);
        }

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

        return $query;
    }
}
