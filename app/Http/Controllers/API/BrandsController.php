<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Brand;
use App\Traits\ApiResponse;
use App\Http\Requests\Brands\BrandRequest;

class BrandsController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         $brands=Brand::latest()->get();

        return $this->success($brands);
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
    public function store(BrandRequest $request)
    {
        $brand= Brand::create($request->validated());

        return $this->success($brand);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $brand= Brand::find($id);

        if(!$brand){
            return $this->error('Brand not found');
        }

        return $this->success($brand);
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
    public function update(BrandRequest $request, string $id)
    {
        $brand= Brand::find($id);

        if(!$brand){
            return $this->error('Brand not found');
        }

        $brand->update($request->validated());

        return $this->success($brand);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $brand= Brand::find($id);

        if(!$brand){
            return $this->error('Brand not found');
        }

        if ($brand->hasDependencies()) {
            return $this->error('cannot delete brand because it has mapped with other records');
        }

        $brand->delete();

        return $this->success('Brand deleted successfully');
    }
}
