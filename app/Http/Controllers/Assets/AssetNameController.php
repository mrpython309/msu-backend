<?php

namespace App\Http\Controllers\Assets;

use App\Http\Controllers\Controller;
use App\Http\Requests\Assets\AssetNameRequest;
use App\Models\AssetName;
use App\Models\ItemMaster;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssetNameController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->query('q', ''));
        $status = trim((string) $request->query('status', ''));

        $assetNames = AssetName::query()
            ->when($q !== '', function ($query) use ($q): void {
                $query->where('name', 'like', "%{$q}%");
            })
            ->when(in_array($status, ['active', 'inactive'], true), function ($query) use ($status): void {
                $query->where('status', $status);
            })
            ->orderBy('name')
            ->paginate(50)
            ->appends($request->query());

        return view('assets.names.index', compact('assetNames', 'q', 'status'));
    }

    public function create()
    {
        return view('assets.names.create');
    }

    public function store(AssetNameRequest $request)
    {
        AssetName::query()->create([
            'name' => $request->validated()['name'],
            'status' => $request->validated()['status'] ?? 'active',
        ]);

        return redirect()->route('asset-names.index')->with('success', 'Asset Name created successfully.');
    }

    public function edit(AssetName $asset_name)
    {
        return view('assets.names.edit', compact('asset_name'));
    }

    public function update(AssetNameRequest $request, AssetName $asset_name)
    {
        $asset_name->update([
            'name' => $request->validated()['name'],
            'status' => $request->validated()['status'] ?? $asset_name->status,
        ]);

        return redirect()->route('asset-names.index')->with('success', 'Asset Name updated successfully.');
    }

    public function destroy(AssetName $asset_name)
    {
        $asset_name->delete();

        return redirect()->route('asset-names.index')->with('success', 'Asset Name deleted successfully.');
    }

    public function updateStatus(Request $request): JsonResponse
    {
        $assetName = AssetName::query()->findOrFail((int) $request->input('id'));
        $status = (string) $request->input('status', 'inactive');
        if (! in_array($status, ['active', 'inactive'], true)) {
            return response()->json(['success' => false], 422);
        }

        $assetName->status = $status;
        $assetName->save();

        return response()->json(['success' => true]);
    }

    public function suggest(Request $request): JsonResponse
    {
        $q = trim((string) $request->query('q', ''));
        $limit = min(max((int) $request->query('limit', 25), 1), 100);

        $rows = ItemMaster::query()
            ->with('category')
            ->active()
            ->when($q !== '', function ($query) use ($q): void {
                $query->where('item_name', 'like', "%{$q}%")
                    ->orWhere('item_code', 'like', "%{$q}%");
            })
            ->orderBy('item_name')
            ->limit($limit)
            ->get()
            ->map(function ($item) {
                $parentCategoryId = $item->category ? ($item->category->parent_id ?: $item->category_id) : $item->category_id;
                $subCategoryId = ($item->category && $item->category->parent_id) ? $item->category_id : $item->category_id;

                return [
                    'id' => $item->id,
                    'name' => $item->item_name,
                    'category_id' => $parentCategoryId,
                    'sub_category_id' => $subCategoryId,
                    'asset_type_id' => $item->asset_type_id,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $rows,
        ]);
    }

    public function quickStore(AssetNameRequest $request): JsonResponse
    {
        $name = (string) ($request->validated()['name'] ?? '');
        $normalized = strtolower(trim($name));

        $existing = AssetName::query()->where('name_normalized', $normalized)->first();
        if ($existing) {
            if ($existing->status !== 'active') {
                $existing->status = 'active';
                $existing->save();
            }

            return response()->json([
                'success' => true,
                'data' => $existing->only(['id', 'name', 'status']),
                'message' => 'Asset Name already exists.',
            ]);
        }

        $assetName = AssetName::query()->create([
            'name' => $name,
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'data' => $assetName->only(['id', 'name', 'status']),
            'message' => 'Asset Name created successfully.',
        ], 201);
    }
}

