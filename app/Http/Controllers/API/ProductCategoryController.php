<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductCategory\ProductCategoryRequest;
use App\Models\MsuItemMaster;
use App\Models\ProductCategory;
use App\Services\Procurement\DocumentNumberGenerator;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class ProductCategoryController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $productCategories = ProductCategory::latest()->get();

        return $this->success($productCategories);
    }

    public function store(ProductCategoryRequest $request): JsonResponse
    {
        $productCategory = ProductCategory::create($request->validated());

        return $this->success($productCategory);
    }

    public function show(string $id)
    {
        $productCategory = ProductCategory::findOrFail($id);

        return $this->success($productCategory);
    }

    public function update(ProductCategoryRequest $request, string $id, DocumentNumberGenerator $numberGenerator)
    {
        $productCategory = ProductCategory::find($id);

        if (!$productCategory) {
            return $this->error('Product category not found');
        }

        $oldCode = strtoupper((string) $productCategory->category_code);

        DB::transaction(function () use ($request, $productCategory, $numberGenerator, $oldCode) {
            $productCategory->update($request->validated());
            $newCode = strtoupper((string) $productCategory->category_code);

            if ($oldCode !== '' && $newCode !== '' && $oldCode !== $newCode) {
                $items = MsuItemMaster::withTrashed()
                    ->where('product_category_id', $productCategory->id)
                    ->orderBy('id')
                    ->get();

                foreach ($items as $item) {
                    $item->item_code = $numberGenerator->nextCompact($newCode, MsuItemMaster::class, 'item_code');
                    $item->save();
                }
            }
        });

        return $this->success($productCategory->fresh());
    }

    public function destroy(string $id)
    {
        $productCategory = ProductCategory::find($id);

        if (!$productCategory) {
            return $this->error('Product category not found');
        }

        if ($productCategory->subCategories()->exists()) {
            return $this->error('Cannot delete category with linked sub categories.');
        }

        $productCategory->delete();

        return $this->success('Product category deleted successfully');
    }
}
