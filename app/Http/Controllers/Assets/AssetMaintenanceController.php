<?php

namespace App\Http\Controllers\Assets;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Asset;
use App\Models\AssetMaintenance;
use App\Models\Vendor;
use App\Models\User;
use App\Http\Requests\Assets\StoreAssetMaintenanceRequest;

class AssetMaintenanceController extends Controller
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

        $asset = Asset::find($request->asset_id);

         $tab='maintenance';

         $vendors=Vendor::active()->get();

         $users=User::Where('id','!=',1)->Where('employee_status','active')->get();

        return view('assets.maintenance.create',compact('asset','tab','vendors','users'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAssetMaintenanceRequest $request)
    {
        $data=$request->validated();

        $data['source_type']='manual'; 

        AssetMaintenance::create($data);

        return redirect_to_asset_show_section($request->asset_id, 'maintenance')->with('success', 'Maintenance added successfully.');
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
    public function edit(AssetMaintenance $maintenance)
    {
         if ($maintenance->status === 'completed') {
             return redirect_to_asset_show_section($maintenance->asset_id, 'maintenance')
                 ->with('error', 'Completed maintenance cannot be edited.');
         }

         $asset = Asset::find($maintenance->asset_id);

         $tab='maintenance';

         $vendors=Vendor::active()->get();

         $users=User::Where('employee_status','active')->get();

         return view('assets.maintenance.edit',compact('maintenance','asset','tab','vendors','users'));
        
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreAssetMaintenanceRequest $request, AssetMaintenance $maintenance)
    {
        if ($maintenance->status === 'completed') {
            return redirect_to_asset_show_section($maintenance->asset_id, 'maintenance')
                ->with('error', 'Completed maintenance cannot be edited.');
        }

        $data=$request->validated();

        $maintenance->update($data);

        return redirect_to_asset_show_section($maintenance->asset_id, 'maintenance')->with('success', 'Maintenance updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AssetMaintenance $maintenance)
    {
        $maintenance->delete();
        
        return redirect_to_asset_show_section($maintenance->asset_id, 'maintenance')->with('success', 'Maintenance deleted successfully.');
    }
}
