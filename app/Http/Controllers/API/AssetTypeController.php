<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AssetType;
use App\Traits\ApiResponse;
use App\Http\Requests\Assets\AssetTypeRequest;

class AssetTypeController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         $assetTypes=AssetType::with('category:id,category_name','subCategory:id,category_name')->get();

        return $this->success($assetTypes);
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
    public function store(AssetTypeRequest $request)
    {
        $assetType=AssetType::create($request->validated());

        return $this->success($assetType);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $assetType= AssetType::with('category:id,category_name','subCategory:id,category_name')->find($id);

        if(!$assetType){

            return $this->error('Asset Type not found');
        }

        return $this->success($assetType);
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
    public function update(AssetTypeRequest $request, string $id)
    {
        $assetType= AssetType::find($id);

        if(!$assetType){ 
     
            return $this->error('Asset Type not found');
        }

        $assetType->update($request->validated());

        return $this->success($assetType);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $assetType= AssetType::find($id);

        if(!$assetType){ 
     
            return $this->error('Asset Type not found');
        }

        $assetType->delete();

        return $this->success('Asset Type deleted successfully');
    }
}
