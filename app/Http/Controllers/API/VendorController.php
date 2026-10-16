<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vendor;
use App\Traits\ApiResponse;
use App\Http\Requests\Vendor\VendorRequest;

class VendorController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         $vendors=Vendor::latest()->paginate(20);

        return $this->successWithPagination($vendors);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(VendorRequest $request)
    {
        $validated = $request->validated();
        $validated['company_code'] = Vendor::generateCompanyCode();
        $vendor = Vendor::create($validated);

        return $this->success($vendor);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $vendor= Vendor::find($id);

        if(!$vendor){
            return $this->error('Vendor not found');
        }

        return $this->success($vendor);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(VendorRequest $request, string $id)
    {
        $vendor= Vendor::find($id);

        if(!$vendor){
            return $this->error('Vendor not found');
        }

        $vendor->update($request->validated());

        return $this->success($vendor);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $vendor= Vendor::find($id);

        if(!$vendor){
            return $this->error('Vendor not found');
        }

        $vendor->delete();

        return $this->success('Vendor deleted successfully');
    }
}
