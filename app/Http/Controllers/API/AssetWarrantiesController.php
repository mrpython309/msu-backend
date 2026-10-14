<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use App\WarrantyType;
use App\Models\AssetWarranty;
use App\Http\Requests\Assets\SaveWarrantyRequest;

class AssetWarrantiesController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if(!$request->asset_id){   return $this->error('Asset Id Not Found');  }

        $warranties = AssetWarranty::where('asset_id', $request->asset_id)->get();
        return $this->success($warranties);
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
    public function store(SaveWarrantyRequest $request)
    {
        $warranty= AssetWarranty::create($request->validated());

        return $this->success($warranty);
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
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SaveWarrantyRequest $request, string $id)
    {
         $warranty= AssetWarranty::find($id);

        if(!$warranty){ 
         
            return $this->error('Asset Warranty not found');
        }

        $warranty->update($request->validated());

        return $this->success($warranty);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
         $warranty= AssetWarranty::find($id);

        if(!$warranty){ 
         
            return $this->error('Asset Warranty not found');
        }

        $warranty->delete();

        return $this->success('Asset Warranty deleted successfully');
    }

    public function warrantyTypes(){

        $warrentytypes=WarrantyType::cases();

        return $this->success($warrentytypes);
    }
}
