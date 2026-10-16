<?php

namespace App\Http\Controllers;

use App\Models\AssetCategory;
use App\Models\AssetType;
use App\Models\ItemMaster;
use App\Services\MasterListExportService;
use App\Services\Procurement\DocumentNumberGenerator;
use App\Support\MasterListing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ItemMasterController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:asset-master.view|item.view', only: ['index', 'show', 'getAssetTypes', 'export', 'exportPdf']),
            new Middleware('permission:asset-master.create|item.create', only: ['create', 'store']),
            new Middleware('permission:asset-master.edit|item.edit', only: ['edit', 'update']),
            new Middleware('permission:asset-master.delete|item.delete', only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        $perPage = MasterListing::perPage($request);
        $items = $this->filteredAssets($request)->orderByDesc('id')->paginate($perPage)->withQueryString();

        $categories = AssetCategory::active()->whereNull('parent_id')->orderBy('category_name')->get();
        $assetTypes = collect();
        if ($request->filled('category_id')) {
            $assetTypes = AssetType::active()->where('category_id', $request->category_id)->orderBy('asset_type_name')->get();
        }

        return view('asset-masters.index', compact('items', 'categories', 'assetTypes', 'perPage'));
    }

    public function export(Request $request, MasterListExportService $exporter)
    {
        return $exporter->excel(
            $this->assetRows($request),
            ['S.No', 'Item Code', 'Item Name', 'Category', 'Asset Type', 'UOM', 'Status'],
            'asset-masters'
        );
    }

    public function exportPdf(Request $request, MasterListExportService $exporter)
    {
        return $exporter->pdf(
            'Asset Master',
            ['S.No', 'Item Code', 'Item Name', 'Category', 'Asset Type', 'UOM', 'Status'],
            $this->assetRows($request),
            'asset-masters'
        );
    }

    private function filteredAssets(Request $request): Builder
    {
        $query = ItemMaster::with(['category', 'assetType']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('item_code', 'like', "%{$search}%")
                    ->orWhere('item_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('asset_type_id')) {
            $query->where('asset_type_id', $request->asset_type_id);
        }

        return $query;
    }

    private function assetRows(Request $request)
    {
        return $this->filteredAssets($request)->orderByDesc('id')->get()->values()->map(
            fn (ItemMaster $item, int $index) => [
                $index + 1,
                $item->item_code,
                $item->item_name,
                $item->category?->category_name,
                $item->assetType?->asset_type_name,
                $item->uom ?: '--',
                ucfirst((string) $item->status),
            ]
        );
    }

    public function create()
    {
        $categories = AssetCategory::active()->whereNull('parent_id')->orderBy('category_name')->get();
        return view('asset-masters.create', compact('categories'));
    }

    public function store(Request $request, DocumentNumberGenerator $numberGenerator)
    {
        $request->validate([
            'category_id' => 'required|exists:asset_categories,id',
            'asset_type_id' => 'required|exists:asset_types,id',
            'item_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('item_masters')->where(function ($query) use ($request) {
                    return $query->where('category_id', $request->category_id)
                                 ->where('asset_type_id', $request->asset_type_id);
                }),
            ],
            'description' => 'nullable|string',
            'uom' => 'nullable|string|max:50',
            'status' => 'required|in:active,inactive',
        ], [
            'item_name.unique' => 'An item with this name already exists in the selected Category and Asset Type.',
        ]);

        $itemCode = $numberGenerator->next('ITM', ItemMaster::class, 'item_code');

        ItemMaster::create([
            'category_id' => $request->category_id,
            'asset_type_id' => $request->asset_type_id,
            'item_code' => $itemCode,
            'item_name' => $request->item_name,
            'description' => $request->description,
            'uom' => $request->uom,
            'status' => $request->status,
        ]);

        return redirect()->route('asset-masters.index')->with('success', 'Asset created successfully.');
    }

    public function edit(ItemMaster $itemMaster)
    {
        $categories = AssetCategory::active()->whereNull('parent_id')->orderBy('category_name')->get();
        $assetTypes = AssetType::active()->where('category_id', $itemMaster->category_id)->orderBy('asset_type_name')->get();
        
        return view('asset-masters.edit', compact('itemMaster', 'categories', 'assetTypes'));
    }

    public function update(Request $request, ItemMaster $itemMaster)
    {
        $request->validate([
            'category_id' => 'required|exists:asset_categories,id',
            'asset_type_id' => 'required|exists:asset_types,id',
            'item_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('item_masters')->where(function ($query) use ($request) {
                    return $query->where('category_id', $request->category_id)
                                 ->where('asset_type_id', $request->asset_type_id);
                })->ignore($itemMaster->id),
            ],
            'description' => 'nullable|string',
            'uom' => 'nullable|string|max:50',
            'status' => 'required|in:active,inactive',
        ], [
            'item_name.unique' => 'An item with this name already exists in the selected Category and Asset Type.',
        ]);

        $itemMaster->update($request->only([
            'category_id',
            'asset_type_id',
            'item_name',
            'description',
            'uom',
            'status'
        ]));

        return redirect()->route('asset-masters.index')->with('success', 'Asset updated successfully.');
    }

    public function destroy(ItemMaster $itemMaster)
    {
        $itemMaster->delete();
        return redirect()->route('asset-masters.index')->with('success', 'Asset deleted successfully.');
    }

    public function getAssetTypes(Request $request)
    {
        $categoryId = $request->query('category_id');
        if (!$categoryId) {
            return response()->json([]);
        }

        $assetTypes = AssetType::active()
            ->where('category_id', $categoryId)
            ->orderBy('asset_type_name')
            ->get(['id', 'asset_type_name']);

        return response()->json($assetTypes);
    }
}
