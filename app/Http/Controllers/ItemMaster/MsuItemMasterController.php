<?php

namespace App\Http\Controllers\ItemMaster;

use App\Helpers\Files;
use App\Http\Controllers\Controller;
use App\Http\Requests\ItemMaster\MsuItemMasterRequest;
use App\Models\Hsn;
use App\Models\MsuItemMaster;
use App\Models\MsuItemRate;
use App\Models\ProductCategory;
use App\Models\ProductSubCategory;
use App\Models\Unit;
use App\Services\Procurement\DocumentNumberGenerator;
use App\Services\ReportPdfExportService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;

class MsuItemMasterController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:item-master:view|item-master:create|item-master:edit', only: ['index', 'show', 'getSubCategories', 'export', 'exportPdf']),
            new Middleware('permission:item-master:create', only: ['create', 'store']),
            new Middleware('permission:item-master:edit', only: ['edit', 'update', 'updateStatus']),
            new Middleware('permission:item-master:delete', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $perPage = $this->resolvePerPage($request);
        $items = $this->filteredItems($request)->orderByDesc('id')->paginate($perPage)->withQueryString();

        $productCategories = ProductCategory::orderBy('category_name')->get();
        $productSubCategories = collect();
        if ($request->filled('product_category_id')) {
            $productSubCategories = ProductSubCategory::where('product_category_id', $request->product_category_id)
                ->orderBy('sub_category_name')
                ->get();
        }

        return view('item-masters.index', compact('items', 'productCategories', 'productSubCategories', 'perPage'));
    }

    public function export(Request $request)
    {
        $rows = $this->filteredItems($request)->orderByDesc('id')->get();

        $export = new class($rows) implements FromCollection, WithHeadings
        {
            public function __construct(private readonly Collection $rows) {}

            public function collection()
            {
                return $this->rows->values()->map(fn (MsuItemMaster $item, int $index) => [
                    $index + 1,
                    $item->item_code,
                    $item->item_name,
                    $item->productCategory?->category_name,
                    $item->productSubCategory?->sub_category_name,
                    $item->unit?->unit_name ?? ($item->uom ?: '--'),
                    $item->hsn?->hsn_code,
                    ucfirst($item->status ?? ''),
                    $item->threshold_quantity !== null ? number_format((float) $item->threshold_quantity, 3) : '',
                    $item->stock_status,
                ]);
            }

            public function headings(): array
            {
                return [
                    'S.No',
                    'Item Code',
                    'Item Name',
                    'Product Category',
                    'Product Sub Category',
                    'Unit',
                    'HSN',
                    'Status',
                    'Threshold Quantity',
                    'Stock Status',
                ];
            }
        };

        return Excel::download($export, 'item-masters-'.now()->format('Ymd_His').'.xlsx');
    }

    public function exportPdf(Request $request, ReportPdfExportService $pdfExport)
    {
        $items = $this->filteredItems($request)->orderByDesc('id')->get();
        $filters = $request->only(['search', 'product_category_id', 'product_sub_category_id', 'status']);

        return $pdfExport->download(
            'item-masters.export-list',
            compact('items', 'filters'),
            'item-masters-'.now()->format('Ymd_His').'.pdf',
            'landscape'
        );
    }

    public function create()
    {
        return view('item-masters.create', $this->formLookups());
    }

    public function store(MsuItemMasterRequest $request, DocumentNumberGenerator $numberGenerator)
    {
        $validated = $request->validated();
        $category = ProductCategory::findOrFail($validated['product_category_id']);
        $prefix = MsuItemMaster::codePrefixForCategory($category);
        $itemCode = $numberGenerator->nextCompact($prefix, MsuItemMaster::class, 'item_code');

        if (! empty($validated['unit_id'])) {
            $validated['uom'] = Unit::find($validated['unit_id'])?->unit_name;
        }

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $validated['image'] = Files::uploadLocalOrS3($request->file('image'), MsuItemMaster::FILE_PATH);
        } else {
            unset($validated['image']);
        }

        $rates = $validated['rates'] ?? [];
        unset($validated['rates'], $validated['deleted_rate_ids']);

        DB::transaction(function () use ($validated, $itemCode, $rates) {
            $item = MsuItemMaster::create([
                ...$validated,
                'item_code' => $itemCode,
            ]);
            $this->syncRates($item, $rates, []);
        });

        return redirect()
            ->route('item-masters.index')
            ->with('success', 'Item created successfully.');
    }

    public function show(MsuItemMaster $msuItemMaster)
    {
        $msuItemMaster->load(['productCategory', 'productSubCategory', 'unit', 'hsn', 'rates']);

        return view('item-masters.show', compact('msuItemMaster'));
    }

    public function edit(MsuItemMaster $msuItemMaster)
    {
        $msuItemMaster->load('rates');

        $data = $this->formLookups($msuItemMaster);
        $data['msuItemMaster'] = $msuItemMaster;
        $data['productSubCategories'] = ProductSubCategory::query()
            ->where('product_category_id', $msuItemMaster->product_category_id)
            ->orderBy('sub_category_name')
            ->get();

        return view('item-masters.edit', $data);
    }

    public function update(MsuItemMasterRequest $request, MsuItemMaster $msuItemMaster, DocumentNumberGenerator $numberGenerator)
    {
        $validated = $request->validated();

        if (! empty($validated['unit_id'])) {
            $validated['uom'] = Unit::find($validated['unit_id'])?->unit_name;
        } else {
            $validated['uom'] = null;
        }

        $category = ProductCategory::findOrFail($validated['product_category_id']);
        $prefix = MsuItemMaster::codePrefixForCategory($category);
        $currentCode = strtoupper((string) $msuItemMaster->item_code);

        if (
            (int) $msuItemMaster->product_category_id !== (int) $validated['product_category_id']
            || ! str_starts_with($currentCode, $prefix)
        ) {
            $validated['item_code'] = $numberGenerator->nextCompact($prefix, MsuItemMaster::class, 'item_code');
        }

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            if ($msuItemMaster->image) {
                Files::deleteFile($msuItemMaster->image, MsuItemMaster::FILE_PATH);
            }
            $validated['image'] = Files::uploadLocalOrS3($request->file('image'), MsuItemMaster::FILE_PATH);
        } else {
            unset($validated['image']);
        }

        $rates = $validated['rates'] ?? [];
        $deletedRateIds = $validated['deleted_rate_ids'] ?? [];
        unset($validated['rates'], $validated['deleted_rate_ids']);

        DB::transaction(function () use ($msuItemMaster, $validated, $rates, $deletedRateIds) {
            $msuItemMaster->update($validated);
            $this->syncRates($msuItemMaster, $rates, $deletedRateIds);
        });

        return redirect()
            ->route('item-masters.index')
            ->with('success', 'Item updated successfully.');
    }

    public function destroy(MsuItemMaster $msuItemMaster)
    {
        $msuItemMaster->delete();

        return redirect()
            ->route('item-masters.index')
            ->with('success', 'Item deactivated successfully.');
    }

    public function updateStatus(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:msu_item_masters,id',
            'status' => 'required|in:active,inactive',
        ]);

        $item = MsuItemMaster::findOrFail($request->id);
        $item->status = $request->status;
        $item->save();

        return response()->json(['success' => true]);
    }

    public function getSubCategories(Request $request)
    {
        $categoryId = $request->query('product_category_id') ?: $request->query('category_id');
        if (! $categoryId) {
            return response()->json([]);
        }

        $subCategories = ProductSubCategory::query()
            ->where('product_category_id', $categoryId)
            ->when(
                $request->has('active_only') && $request->boolean('active_only'),
                fn ($q) => $q->where('status', 'active')
            )
            ->orderBy('sub_category_name')
            ->get(['id', 'sub_category_name', 'status']);

        return response()->json($subCategories);
    }

    private function resolvePerPage(Request $request): int
    {
        $perPage = $request->input('per_page', 10);

        if ($perPage === 'all') {
            return 1000000;
        }

        $perPage = (int) $perPage;

        return in_array($perPage, [10, 25, 50, 100, 1000000], true) ? $perPage : 10;
    }

    private function filteredItems(Request $request): Builder
    {
        $query = MsuItemMaster::with(['productCategory', 'productSubCategory', 'unit', 'hsn']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('item_code', 'like', "%{$search}%")
                    ->orWhere('item_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('product_category_id')) {
            $query->where('product_category_id', $request->product_category_id);
        }

        if ($request->filled('product_sub_category_id')) {
            $query->where('product_sub_category_id', $request->product_sub_category_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return $query;
    }

    private function formLookups(?MsuItemMaster $item = null): array
    {
        $productCategories = ProductCategory::query()
            ->when($item, function ($query) use ($item) {
                $query->where(function ($q) use ($item) {
                    $q->active()->orWhere('id', $item->product_category_id);
                });
            }, fn ($query) => $query->active())
            ->orderBy('category_name')
            ->get();

        $units = Unit::query()
            ->when($item, function ($query) use ($item) {
                $query->where(function ($q) use ($item) {
                    $q->active()->orWhere('id', $item->unit_id);
                });
            }, fn ($query) => $query->active())
            ->orderBy('unit_name')
            ->get();

        $hsns = Hsn::query()
            ->when($item, function ($query) use ($item) {
                $query->where(function ($q) use ($item) {
                    $q->active()->orWhere('id', $item->hsn_id);
                });
            }, fn ($query) => $query->active())
            ->orderBy('hsn_code')
            ->get();

        $productSubCategories = collect();
        if (old('product_category_id')) {
            $productSubCategories = ProductSubCategory::query()
                ->where('product_category_id', old('product_category_id'))
                ->orderBy('sub_category_name')
                ->get();
        }

        return compact('productCategories', 'units', 'hsns', 'productSubCategories');
    }

    private function syncRates(MsuItemMaster $item, array $rates, array $deletedRateIds): void
    {
        if (! empty($deletedRateIds)) {
            MsuItemRate::query()
                ->where('msu_item_master_id', $item->id)
                ->whereIn('id', $deletedRateIds)
                ->delete();
        }

        foreach ($rates as $row) {
            if (! isset($row['rate']) || $row['rate'] === '' || $row['rate'] === null) {
                continue;
            }
            if (empty($row['effective_from'])) {
                continue;
            }

            $payload = [
                'rate' => (float) $row['rate'],
                'tax' => (float) ($row['tax'] ?? 0),
                'effective_from' => $row['effective_from'],
            ];

            if (! empty($row['id'])) {
                MsuItemRate::query()
                    ->where('msu_item_master_id', $item->id)
                    ->whereKey($row['id'])
                    ->update($payload);
            } else {
                $item->rates()->create($payload);
            }
        }
    }
}
