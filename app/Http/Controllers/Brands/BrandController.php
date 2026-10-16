<?php

namespace App\Http\Controllers\Brands;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Brand;
use App\Http\Requests\Brands\BrandRequest;
use App\Services\MasterListExportService;
use App\Support\MasterListing;
use Illuminate\Database\Eloquent\Builder;

class BrandController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = MasterListing::perPage($request);
        $brands = $this->filteredBrands($request)->orderByDesc('id')->paginate($perPage)->withQueryString();

        return view('brands.index', compact('brands', 'perPage'));
    }

    public function export(Request $request, MasterListExportService $exporter)
    {
        return $exporter->excel($this->brandRows($request), ['S.No', 'Brand Name', 'Status'], 'brands');
    }

    public function exportPdf(Request $request, MasterListExportService $exporter)
    {
        return $exporter->pdf('Brands', ['S.No', 'Brand Name', 'Status'], $this->brandRows($request), 'brands');
    }

    private function filteredBrands(Request $request): Builder
    {
        $query = Brand::query();
        MasterListing::applySearch($query, $request, ['brand_name', 'brand_description']);
        MasterListing::applyStatus($query, $request);

        return $query;
    }

    private function brandRows(Request $request)
    {
        return $this->filteredBrands($request)->orderByDesc('id')->get()->values()->map(
            fn (Brand $brand, int $index) => [$index + 1, $brand->brand_name, ucfirst((string) $brand->status)]
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('brands.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BrandRequest $request)
    {
        $validated = $request->validated();

        Brand::create($validated);

        return redirect()
            ->route('brands.index')
            ->with('success', 'Brand created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Brand $brand)
    {
          return view('brands.edit', compact('brand'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BrandRequest $request, Brand $brand)
    {
        $validated = $request->validated();

        $brand->update($validated);

        return redirect()
            ->route('brands.index')
            ->with('success', 'Brand updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Brand $brand)
    {      

        if ($brand->hasDependencies()) {
            return back()->with('error', 'cannot delete brand because it has mapped with other records');
        }

         $brand->delete();

        return redirect()
            ->route('brands.index')
            ->with('success', 'Brand deleted successfully.');
    }

     public function updateStatus(Request $request)
    {
        $brand = Brand::findOrFail($request->id);

        $brand->status = $request->status;
        $brand->save();

        return response()->json(['success' => true]);
    }

    /**
     * Minimal JSON create for asset form "+" button next to brand.
     */
    public function quickStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'brand_name' => 'required|string|max:255',
        ]);

        $name = trim($validated['brand_name']);
        $normalized = mb_strtolower($name);

        $existing = Brand::query()
            ->whereRaw('LOWER(TRIM(brand_name)) = ?', [$normalized])
            ->first();

        if ($existing) {
            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $existing->id,
                    'brand_name' => $existing->brand_name,
                ],
                'message' => 'Brand already exists.',
            ]);
        }

        $brand = Brand::query()->create([
            'brand_name' => $name,
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $brand->id,
                'brand_name' => $brand->brand_name,
            ],
            'message' => 'Brand created successfully.',
        ], 201);
    }
}
