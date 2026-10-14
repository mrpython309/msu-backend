<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Models\Vendor;
use App\Models\Brand;
use App\Http\Requests\Vendor\VendorRequest;
use App\Services\MasterListExportService;
use App\Support\MasterListing;
use Illuminate\Database\Eloquent\Builder;

class VendorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = MasterListing::perPage($request);
        $vendors = $this->filteredVendors($request)->paginate($perPage)->withQueryString();
        $brands = Brand::orderBy('brand_name')->get();

        return view('vendors.index', compact('vendors', 'brands', 'perPage'));
    }

    public function export(Request $request, MasterListExportService $exporter)
    {
        return $exporter->excel(
            $this->vendorRows($request),
            ['S.No', 'Vendor Code', 'Vendor Name', 'Contact Person', 'Phone', 'Email', 'GST Number', 'Address', 'Status', 'City', 'State', 'Country'],
            'vendors'
        );
    }

    public function exportPdf(Request $request, MasterListExportService $exporter)
    {
        return $exporter->pdf(
            'Vendors',
            ['S.No', 'Vendor Code', 'Vendor Name', 'Contact Person', 'Phone', 'Email', 'GST Number', 'Address', 'Status', 'City', 'State', 'Country'],
            $this->vendorRows($request),
            'vendors'
        );
    }

    private function filteredVendors(Request $request): Builder
    {
        $query = Vendor::query()
            ->when($request->name, fn ($q) => $q->where('name', 'like', '%'.$request->name.'%'))
            ->when($request->phone_number, fn ($q) => $q->where('phone_number', 'like', '%'.$request->phone_number.'%'))
            ->when($request->email, fn ($q) => $q->where('email', 'like', '%'.$request->email.'%'))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->brands, function ($q) use ($request) {
                $brandId = $request->brands;
                $q->where(function ($inner) use ($brandId) {
                    $inner->whereJsonContains('brands', $brandId)
                        ->orWhereJsonContains('brands', (int) $brandId)
                        ->orWhereJsonContains('brands', (string) $brandId);
                });
            });

        MasterListing::applySearch($query, $request, [
            'company_code',
            'name',
            'phone_number',
            'contact_person',
            'email',
            'gst_number',
            'address',
            'city',
            'state',
            'country',
        ]);

        return $query;
    }

    private function vendorRows(Request $request)
    {
        return $this->filteredVendors($request)->get()->values()->map(
            fn (Vendor $vendor, int $index) => [
                $index + 1,
                $vendor->company_code,
                $vendor->name,
                $vendor->contact_person,
                $vendor->phone_number,
                $vendor->email,
                $vendor->gst_number,
                $vendor->address,
                ucfirst((string) $vendor->status),
                $vendor->city,
                $vendor->state,
                $vendor->country,
            ]
        );
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
          $brands=Brand::active()->get();  

        return view('vendors.create',compact('brands'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(VendorRequest $request)
    {

       $validated = $request->validated();

if (isset($validated['brands'])) {
    $validated['brands'] = json_encode($validated['brands']); // convert array to JSON
}

        $validated['company_code'] = Vendor::generateCompanyCode($validated['name']);

        Vendor::create($validated);

        return redirect()
            ->route('vendor.index')
            ->with('success', 'Vendor created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Vendor $vendor)
    {
        return view('vendors.show', compact('vendor'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Vendor $vendor)
    {
         $brands=Brand::active()->get();  

        return view('vendors.create',compact('vendor','brands'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(VendorRequest $request, Vendor $vendor)
    {

        $vendor->update($request->validated());

        return redirect()
            ->route('vendor.index')
            ->with('success', 'Vendor updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Vendor $vendor)
    {
        $vendor->delete();
        return redirect()
            ->route('vendor.index')
            ->with('success', 'Vendor deleted successfully.');
    }

    /**
     * Minimal JSON create for asset form "+" (same flow as quick asset name).
     */
    public function quickStore(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone_number' => 'nullable|string|max:20',
        ]);

        $name = trim($validated['name']);
        $normalized = mb_strtolower($name);

        $existing = Vendor::query()
            ->whereRaw('LOWER(TRIM(name)) = ?', [$normalized])
            ->first();

        if ($existing) {
            return response()->json([
                'success' => true,
                'data' => $existing->only(['id', 'name']),
                'message' => 'Supplier already exists.',
            ]);
        }

        $vendor = Vendor::query()->create([
            'company_code' => Vendor::generateCompanyCode($name),
            'name' => $name,
            'phone_number' => $validated['phone_number'] ?? null,
            'status' => 'active',
        ]);

        return response()->json([
            'success' => true,
            'data' => $vendor->only(['id', 'name', 'company_code']),
            'message' => 'Supplier created successfully.',
        ], 201);
    }
}
