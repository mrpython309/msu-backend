<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use App\Models\AssetAssignment;
use App\Models\Asset;
use App\Http\Requests\Assets\AssignAssetRequest;
use Illuminate\Support\Facades\Gate;

class AssetAssignmentsController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if(!$request->asset_id){   return $this->error('Asset Id Not Found');  }

        $assignments = AssetAssignment::visibleToUser(auth()->user())->where('asset_id', $request->asset_id)->with('employee:id,employee_name','fromCompany:id,name','fromBranch:id,name','toCompany:id,name','toBranch:id,name','fromBranchLocation:id,location_name','toBranchLocation:id,location_name')->get();
       
        return $this->success($assignments);
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
    public function store(AssignAssetRequest $request)
    {

       $asset = Asset::find($request->asset_id);

        if(!$asset || $asset->status->value != 'available'){

            return $this->error('Asset not found'); 
        }        

        $checkInTransit=AssetAssignment::where('asset_id',$asset->id)->where('transfer_status','in_transit')->first();

        if($checkInTransit){

            return $this->error('Asset already in transit'); 
        }


        // if($request->to_company_id == $asset->company_id && $request->to_branch == $asset->branch_id){

        //      return $this->error('Cannot Assign Same Branch and Company'); 
        // }



        $data = $request->validated();

        $data['from_company_id'] = $asset->company_id;
        $data['from_branch'] = $asset->branch_id;
        $data['from_location']  = $asset->location_id;
        $data['transfer_status'] = 'in_transit';
        $data['status']  = 'transferred';
        $data['assigned_by'] = auth()->user()->id;
        $data['assigned_at'] = now();
        $data['assigned_notes']=$request->notes;
 
        $assignment = $asset->assignments()->create($data);

        return $this->success($assignment);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $assignment = AssetAssignment::with('employee:id,employee_name','fromCompany:id,name','fromBranch:id,name','toCompany:id,name','toBranch:id,name','fromBranchLocation:id,location_name','toBranchLocation:id,location_name')->find($id);

        if(!$assignment){ 
     
            return $this->error('Asset Assignment not found');
        }   

        return $this->success($assignment);
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
    public function update(AssignAssetRequest $request, string $id)
    {
        $assignment=AssetAssignment::find($id);

        if(!$assignment){ 
     
            return $this->error('Asset Assignment not found');
        }

        if($assignment->transfer_status != 'in_transit'){

            return $this->error('Assignment cannot be updated');
        }

        // if($request->to_company_id == $assignment->company_id && $request->to_branch == $assignment->branch_id){

        //     return $this->error('Cannot Assign Same Branch and Company');
        // }

        $assignment->update($request->validated());
        $assignment->syncAssetLocationFromTransfer();

        return $this->success($assignment);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
         $assignment=AssetAssignment::find($id);

        if(!$assignment){ 
     
            return $this->error('Asset Assignment not found');
        }

        $assignment->delete();

        return $this->success('Asset Assignment deleted successfully');
    }

    public function acceptAssignment(Request $request,$id){

        $request->validate([
            'to_location' => 'nullable|exists:branch_locations,id',
        ]);

        $assignment=AssetAssignment::find($id);

        if(!$assignment){ 
     
            return $this->error('Asset Assignment not found');
        }

       if(!Gate::allows('updateStatus', [$assignment, 'accept'])){

            return $this->error('You do not have permission to accept this assignment');
       };

        $assignment->to_location = $request->filled('to_location') ? $request->to_location : null;

        $assignment->accepted_at = now();

        $assignment->accepted_by = auth()->user()->id;

        $assignment->accepted_notes = $request->accepted_notes;

        $assignment->transfer_status = 'accepted';

        $assignment->status = 'assigned';

        $assignment->save();

        return $this->success($assignment);

    }

    public function rejectAssignment(Request $request,$id){

        $assignment=AssetAssignment::find($id);

        if(!$assignment){ 
     
            return $this->error('Asset Assignment not found');
        }

        if(!Gate::allows('updateStatus', [$assignment, 'reject'])){

            return $this->error('You do not have permission to accept this assignment');
       };

        $assignment->rejected_at = now();

        $assignment->rejected_by = auth()->user()->id;

        $assignment->rejected_notes = $request->rejected_notes;

        $assignment->transfer_status = 'rejected';  

        $assignment->status = 'transferred';

        $assignment->save();

        return $this->success($assignment);

    }

    public function returnAssignment(Request $request,$id){

        $request->validate([
            'return_notes' => 'required',
        ]);

         $assignment=AssetAssignment::find($id);

        if(!$assignment){ 
     
            return $this->error('Asset Assignment not found');
        }

        if(!Gate::allows('updateStatus', [$assignment, 'return'])){

            return $this->error('You do not have permission to accept this assignment');
       };

        $assignment->returned_at = now();

        $assignment->returned_by = auth()->user()->id;

        $assignment->return_notes = $request->return_notes;

        $assignment->transfer_status = 'return';  

        $assignment->status = 'transferred';

        $assignment->save();

        return $this->success($assignment);

    }


    public function returnAcceptAssignment(Request $request,$id){

        $request->validate([
            'return_accepted_notes' => 'required',
        ]);

        $assignment=AssetAssignment::find($id);

        if(!$assignment){ 
     
            return $this->error('Asset Assignment not found');
        }

        if(!Gate::allows('updateStatus', [$assignment, 'return_accept'])){

            return $this->error('You do not have permission to accept this assignment');
       };

        $assignment->transfer_status = 'return_accepted';

        $assignment->status = 'returned';

        $assignment->return_accepted_by=auth()->user()->id;

        $assignment->return_accepted_at=now();

        $assignment->return_accepted_notes=$request->return_accepted_notes;

        $assignment->save();

        return $this->success($assignment);


    }


}
