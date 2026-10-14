<?php

namespace App\Http\Controllers\Assets;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Asset;
use App\Models\AssetReplacement;
use App\AssetCondition;
use App\AssetOperationalStatus;
use App\Http\Requests\Assets\AssetReplacementStoreRequest;

class AssetReplacementController extends Controller
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


        $tab='replacements';

         $asset = Asset::find($request->asset_id);

          $conditions=AssetCondition::cases();

        $operations_status=AssetOperationalStatus::cases();

        return view('assets.replacements.create',compact('tab','asset','conditions','operations_status'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AssetReplacementStoreRequest $request)
    {
        $data=$request->validated();

          $asset = Asset::find($request->asset_id);

          if(!$asset){

              return redirect()->back()->with('error', 'Asset not found');
          }

        $data['old_serial_no']=$asset->serial_no;

        $data['old_condition']=$asset->condition?->value;

        $data['old_operational_status']=$asset->operational_status;

        $data['company_id']=$asset->company_id;

        $data['branch_id']=$asset->branch_id;

        $data['location_id']=$asset->location_id;

        $data['created_by']=auth()->user()->id;

        $data['updated_by']=auth()->user()->id;

        $replacement=AssetReplacement::create($data);

        $asset->update([
            'serial_no'=>$replacement->new_serial_no,
            'condition'=>$replacement->new_condition,
            'operational_status'=>$replacement->new_operational_status,
        ]);

        return redirect_to_asset_show_section($request->asset_id, 'replacements')->with('success', 'Replacement added successfully.');
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
    public function edit(AssetReplacement $asset_replacement)
    {
          $tab='replacements';

         $asset = Asset::find($asset_replacement->asset_id);

          $conditions=AssetCondition::cases();

        $operations_status=AssetOperationalStatus::cases();

        return view('assets.replacements.edit',compact('tab','asset','conditions','operations_status','asset_replacement'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AssetReplacementStoreRequest $request, AssetReplacement $asset_replacement)
    {
        $data=$request->validated();

        $asset_replacement->update($data);

        $asset = Asset::find($asset_replacement->asset_id);

        $asset->update([
            'serial_no'=>$asset_replacement->new_serial_no, 
        ]);

        return redirect_to_asset_show_section($asset_replacement->asset_id, 'replacements')->with('success', 'Replacement updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AssetReplacement $asset_replacement)
    {
        $asset_replacement->delete();
        return redirect_to_asset_show_section($asset_replacement->asset_id, 'replacements')->with('success', 'Replacement deleted successfully.');
    }
}
