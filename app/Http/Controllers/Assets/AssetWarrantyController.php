<?php

namespace App\Http\Controllers\Assets;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AssetWarranty;
use App\Models\Asset;
use App\WarrantyType;
use App\Http\Requests\Assets\SaveWarrantyRequest;

class AssetWarrantyController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
     if(!isset($request->asset_id)){
           
           return redirect()->back()->with('error', 'Asset not found');
       }

        $tab='warranties';

         $asset = Asset::find($request->asset_id);

          $warrentytypes=WarrantyType::cases();

       return view('assets.warranties.create',compact('asset','tab','warrentytypes'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SaveWarrantyRequest $request)
    {
        AssetWarranty::create($request->validated());

        return redirect_to_asset_show_section($request->asset_id, 'warranties')->with('success', 'Warranty created successfully.');
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
    public function edit(AssetWarranty $warranty)
    {
          $tab='warranties';

         $asset = Asset::find($warranty->asset_id);

          $warrentytypes=WarrantyType::cases();

        return view('assets.warranties.edit',compact('warranty','tab','asset','warrentytypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SaveWarrantyRequest $request, AssetWarranty $warranty)
    {
        $warranty->update($request->validated());

        return redirect_to_asset_show_section($warranty->asset_id, 'warranties')->with('success', 'Warranty updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AssetWarranty $warranty)
    {
        $assetId=$warranty->asset_id;

        $warranty->delete();

        return redirect_to_asset_show_section($assetId, 'warranties')->with('success', 'Warranty deleted successfully.');
    }
}
