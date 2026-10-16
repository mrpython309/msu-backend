<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductCategory\ProductSubCategoryRequest;
use App\Models\ProductSubCategory;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;

class ProductSubCategoryController extends Controller
{
    use ApiResponse;

    public function index()
    {
        $productSubCategories = ProductSubCategory::with('productCategory')->latest()->get();

        return $this->success($productSubCategories);
    }

    public function store(ProductSubCategoryRequest $request): JsonResponse
    {
        $productSubCategory = ProductSubCategory::create($request->validated());

        return $this->success($productSubCategory->load('productCategory'));
    }

    public function show(string $id)
    {
        $productSubCategory = ProductSubCategory::with('productCategory')->findOrFail($id);

        return $this->success($productSubCategory);
    }

    public function update(ProductSubCategoryRequest $request, string $id)
    {
        $productSubCategory = ProductSubCategory::find($id);

        if (!$productSubCategory) {
            return $this->error('Product sub category not found');
        }

        $productSubCategory->update($request->validated());

        return $this->success($productSubCategory->load('productCategory'));
    }

    public function destroy(string $id)
    {
        $productSubCategory = ProductSubCategory::find($id);

        if (!$productSubCategory) {
            return $this->error('Product sub category not found');
        }

        $productSubCategory->delete();

        return $this->success('Product sub category deleted successfully');
    }
}
