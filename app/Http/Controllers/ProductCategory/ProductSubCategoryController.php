<?php

namespace App\Http\Controllers\ProductCategory;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductCategory\ProductSubCategoryRequest;
use App\Models\ProductCategory;
use App\Models\ProductSubCategory;
use App\Services\MasterListExportService;
use App\Support\MasterListing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ProductSubCategoryController extends Controller
{
    public function index(Request $request)
    {
        $perPage = MasterListing::perPage($request);
        $productSubCategories = $this->filteredProductSubCategories($request)
            ->orderByDesc('id')
            ->paginate($perPage)
            ->withQueryString();
        $productCategories = ProductCategory::orderBy('category_name')->get();

        return view('product-sub-categories.index', compact('productSubCategories', 'productCategories', 'perPage'));
    }

    public function export(Request $request, MasterListExportService $exporter)
    {
        return $exporter->excel(
            $this->productSubCategoryRows($request),
            ['S.No', 'Category', 'Sub Category Name', 'Status'],
            'product-sub-categories'
        );
    }

    public function exportPdf(Request $request, MasterListExportService $exporter)
    {
        return $exporter->pdf(
            'Product Sub Categories',
            ['S.No', 'Category', 'Sub Category Name', 'Status'],
            $this->productSubCategoryRows($request),
            'product-sub-categories'
        );
    }

    private function filteredProductSubCategories(Request $request): Builder
    {
        $query = ProductSubCategory::with('productCategory');
        MasterListing::applySearch($query, $request, ['sub_category_name', 'productCategory.category_name']);
        MasterListing::applyStatus($query, $request);

        if ($request->filled('product_category_id')) {
            $query->where('product_category_id', $request->product_category_id);
        }

        return $query;
    }

    private function productSubCategoryRows(Request $request)
    {
        return $this->filteredProductSubCategories($request)->orderByDesc('id')->get()->values()->map(
            fn (ProductSubCategory $subCategory, int $index) => [
                $index + 1,
                $subCategory->productCategory?->category_name,
                $subCategory->sub_category_name,
                ucfirst((string) $subCategory->status),
            ]
        );
    }

    public function create()
    {
        $productCategories = ProductCategory::active()->orderBy('category_name')->get();

        return view('product-sub-categories.create', compact('productCategories'));
    }

    public function store(ProductSubCategoryRequest $request)
    {
        ProductSubCategory::create($request->validated());

        return redirect()
            ->route('product-sub-categories.index')
            ->with('success', 'Product sub category created successfully.');
    }

    public function edit(ProductSubCategory $productSubCategory)
    {
        $productCategories = ProductCategory::query()
            ->where(function ($query) use ($productSubCategory) {
                $query->active()
                    ->orWhere('id', $productSubCategory->product_category_id);
            })
            ->orderBy('category_name')
            ->get();

        return view('product-sub-categories.edit', compact('productSubCategory', 'productCategories'));
    }

    public function update(ProductSubCategoryRequest $request, ProductSubCategory $productSubCategory)
    {
        $productSubCategory->update($request->validated());

        return redirect()
            ->route('product-sub-categories.index')
            ->with('success', 'Product sub category updated successfully.');
    }

    public function destroy(ProductSubCategory $productSubCategory)
    {
        $productSubCategory->delete();

        return redirect()
            ->route('product-sub-categories.index')
            ->with('success', 'Product sub category deleted successfully.');
    }

    public function updateStatus(Request $request)
    {
        $productSubCategory = ProductSubCategory::findOrFail($request->id);
        $productSubCategory->status = $request->status;
        $productSubCategory->save();

        return response()->json(['success' => true]);
    }
}
