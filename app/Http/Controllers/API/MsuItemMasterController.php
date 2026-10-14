<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ItemMaster\MsuItemMasterRequest;
use App\Models\MsuItemMaster;
use App\Models\ProductCategory;
use App\Models\Unit;
use App\Services\Procurement\DocumentNumberGenerator;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class MsuItemMasterController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $items = MsuItemMaster::with(['productCategory', 'productSubCategory', 'unit'])->latest()->get();

        return $this->success($items);
    }

    public function store(MsuItemMasterRequest $request, DocumentNumberGenerator $numberGenerator): JsonResponse
    {
        $validated = $request->validated();
        $category = ProductCategory::findOrFail($validated['product_category_id']);
        $prefix = MsuItemMaster::codePrefixForCategory($category);
        $itemCode = $numberGenerator->nextCompact($prefix, MsuItemMaster::class, 'item_code');

        if (! empty($validated['unit_id'])) {
            $validated['uom'] = Unit::find($validated['unit_id'])?->unit_name;
        }

        $item = MsuItemMaster::create([
            ...$validated,
            'item_code' => $itemCode,
        ]);

        return $this->success($item->load(['productCategory', 'productSubCategory', 'unit']));
    }

    public function show(string $id)
    {
        $item = MsuItemMaster::with(['productCategory', 'productSubCategory', 'unit'])->findOrFail($id);

        return $this->success($item);
    }

    public function update(MsuItemMasterRequest $request, string $id, DocumentNumberGenerator $numberGenerator)
    {
        $item = MsuItemMaster::find($id);

        if (!$item) {
            return $this->error('Item not found');
        }

        $validated = $request->validated();
        if (! empty($validated['unit_id'])) {
            $validated['uom'] = Unit::find($validated['unit_id'])?->unit_name;
        } else {
            $validated['uom'] = null;
        }

        $category = ProductCategory::findOrFail($validated['product_category_id']);
        $prefix = MsuItemMaster::codePrefixForCategory($category);
        $currentCode = strtoupper((string) $item->item_code);

        if (
            (int) $item->product_category_id !== (int) $validated['product_category_id']
            || ! str_starts_with($currentCode, $prefix)
        ) {
            $validated['item_code'] = $numberGenerator->nextCompact($prefix, MsuItemMaster::class, 'item_code');
        }

        $item->update($validated);

        return $this->success($item->load(['productCategory', 'productSubCategory', 'unit']));
    }

    public function destroy(string $id)
    {
        $item = MsuItemMaster::find($id);

        if (!$item) {
            return $this->error('Item not found');
        }

        $item->delete();

        return $this->success('Item deactivated successfully');
    }
}
