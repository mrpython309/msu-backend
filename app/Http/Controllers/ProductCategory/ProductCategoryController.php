<?php

namespace App\Http\Controllers\ProductCategory;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductCategory\ProductCategoryRequest;
use App\Models\MsuItemMaster;
use App\Models\ProductCategory;
use App\Services\MasterListExportService;
use App\Services\Procurement\DocumentNumberGenerator;
use App\Support\MasterListing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductCategoryController extends Controller
{
    public function index(Request $request)
    {
        $perPage = MasterListing::perPage($request);
        $productCategories = $this->filteredProductCategories($request)->orderByDesc('id')->paginate($perPage)->withQueryString();

        return view('product-categories.index', compact('productCategories', 'perPage'));
    }

    public function export(Request $request, MasterListExportService $exporter)
    {
        return $exporter->excel($this->productCategoryRows($request), ['S.No', 'Category Name', 'Short Name', 'Status'], 'product-categories');
    }

    public function exportPdf(Request $request, MasterListExportService $exporter)
    {
        return $exporter->pdf('Product Categories', ['S.No', 'Category Name', 'Short Name', 'Status'], $this->productCategoryRows($request), 'product-categories');
    }

    private function filteredProductCategories(Request $request): Builder
    {
        $query = ProductCategory::query();
        MasterListing::applySearch($query, $request, ['category_name', 'category_code']);
        MasterListing::applyStatus($query, $request);

        return $query;
    }

    private function productCategoryRows(Request $request)
    {
        return $this->filteredProductCategories($request)->orderByDesc('id')->get()->values()->map(
            fn (ProductCategory $category, int $index) => [
                $index + 1,
                $category->category_name,
                $category->category_code,
                ucfirst((string) $category->status),
            ]
        );
    }

    public function create()
    {
        return view('product-categories.create');
    }

    public function store(ProductCategoryRequest $request)
    {
        ProductCategory::create($request->validated());

        return redirect()
            ->route('product-categories.index')
            ->with('success', 'Product category created successfully.');
    }

    public function edit(ProductCategory $productCategory)
    {
        return view('product-categories.edit', compact('productCategory'));
    }

    public function update(ProductCategoryRequest $request, ProductCategory $productCategory, DocumentNumberGenerator $numberGenerator)
    {
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

        return redirect()
            ->route('product-categories.index')
            ->with('success', 'Product category updated successfully.');
    }

    public function destroy(ProductCategory $productCategory)
    {
        if ($productCategory->subCategories()->exists()) {
            return redirect()
                ->route('product-categories.index')
                ->with('error', 'Cannot delete category with linked sub categories.');
        }

        $productCategory->delete();

        return redirect()
            ->route('product-categories.index')
            ->with('success', 'Product category deleted successfully.');
    }

    public function updateStatus(Request $request)
    {
        $productCategory = ProductCategory::findOrFail($request->id);
        $productCategory->status = $request->status;
        $productCategory->save();

        return response()->json(['success' => true]);
    }
}
