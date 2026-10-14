<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use App\Models\AssetMaintenance;
use App\Models\User;
use App\Http\Requests\Assets\StoreAssetMaintenanceRequest;

class AssetMaintenanceController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
         if(!$request->asset_id){   return $this->error('Asset Id Not Found');  }

        $maintenances = AssetMaintenance::with('vendor:id,name','reportedBy:id,name')->where('asset_id', $request->asset_id)->get();

        return $this->success($maintenances);


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
    public function store(StoreAssetMaintenanceRequest $request)
    {
        $data=$request->validated();

        $data['source_type']='manual';

        $data['reported_by_id']=auth()->user()->id;

        $maintenance=AssetMaintenance::create($data);

        return $this->success($maintenance);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {

         $maintenances = AssetMaintenance::with('vendor:id,name','reportedBy:id,name')->where('id', $id)->first();

        if(!$maintenances){
            return $this->error('Maintenance not found');
        }

        return $this->success($maintenances);
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
    public function update(StoreAssetMaintenanceRequest $request, string $id)
    {
        $maintenance=AssetMaintenance::find($id);

        if(!$maintenance){
            return $this->error('Maintenance not found');
        }

        $data=$request->validated();

        $maintenance->update($data);

        return $this->success($maintenance);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $maintenance=AssetMaintenance::find($id);

        if(!$maintenance){
            return $this->error('Maintenance not found');
        }

        $maintenance->delete();

        return $this->success('Maintenance deleted successfully');
    }

    public function assetMaintenceReportedBy(){

        $users=User::Where('id','!=',1)->Where('employee_status','active')->get();

         return $this->success($users);

    }
}
