<?php

namespace App\Http\Controllers\Assets;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Asset;
use App\Models\Branch;
use App\Models\User;
use App\Models\BranchLocation;
use App\Http\Requests\Assets\AssignAssetRequest;
use App\Models\AssetAssignment;
use App\Models\Company;
use App\Models\AssetCustodian;
use App\Models\Department;

class AssetAssignmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    public function getTargets($type){

    if ($type === 'branch') {
        return Branch::visibleToUser(auth()->user())->select('id', 'name')->get();
    }

    if ($type === 'user') {
        return User::select('id', 'name')->get();
    }

    return response()->json([], 400);

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

       if($asset->status->value != 'available'){

            return redirect()->back()->with('error', 'Asset not available');

       }

       $tab='assignments';

       $companies=Company::active()->select('id','name')->get();

       $custodians = $this->assignmentCustodians($asset->custodian_id);

       $scrollToTabSection = 'assignments';

       return view('assets.assignments.create', array_merge(
           compact('asset', 'tab', 'companies', 'custodians', 'scrollToTabSection'),
           $this->assignmentFormLists($asset->department_id)
       ));


    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AssignAssetRequest $request)
    {
        $asset = Asset::find($request->asset_id);

        if(!$asset || $asset->status->value != 'available'){
            return redirect()->back()->with('error', 'Asset not found');
        }        

        $checkInTransit=AssetAssignment::where('asset_id',$asset->id)->where('transfer_status','in_transit')->first();

        if($checkInTransit){
            return redirect()->back()->with('error', 'Asset already in transit');
        }


        // if($request->to_company_id == $asset->company_id && $request->to_branch == $asset->branch_id){

        //      return redirect()->back()->with('error','Cannot Assign Same Branch and Company');
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
        $assignment->syncAssetLocationFromTransfer();

        return redirect_to_asset_show_section($asset->fresh(['branch', 'location', 'company']), 'assignments')
            ->with('success', 'Asset assigned successfully.');

    }    

    public function acceptassignment(AssetAssignment $assignment){


        if($assignment->transfer_status != 'in_transit'){

            return redirect()->back()->with('error', 'Assignment cannot be accepted');
        }

          $tab='assignments';

       $asset = Asset::find($assignment->asset_id);

       $locations=BranchLocation::Where('branch_id', $assignment->to_branch)->where('status', 'active')->select('id','location_name')->get();

       $employees = User::query()
           ->activeEmployeesForBranch((int) $assignment->to_branch)
           ->get();


       $scrollToTabSection = 'assignments';

       return view('assets.assignments.accept',compact('asset','tab','assignment','locations','employees','scrollToTabSection'));

    }

    public function acceptassetassignment(Request $request,AssetAssignment $assignment){

        $request->validate([
            'to_location' => 'nullable|exists:branch_locations,id',
        ]);

        $assignment->to_location = $request->filled('to_location') ? $request->to_location : null;

        $assignment->accepted_at = now();

        $assignment->accepted_by = auth()->user()->id;

        $assignment->accepted_notes = $request->accepted_notes;

        $assignment->transfer_status = 'accepted';

        $assignment->status = 'assigned';

        $assignment->save();

        // Ensure company/branch/location are written even if observers miss a edge case.
        $assignment->syncAssetLocationFromTransfer();

        return redirect_to_asset_show_section($assignment->asset_id, 'assignments')
            ->with('success', 'Asset accepted successfully.'); 
    }

    public function rejectassignment(AssetAssignment $assignment){

        if($assignment->transfer_status != 'in_transit'){

            return redirect()->back()->with('error', 'Assignment cannot be rejected');
        }

        $tab='assignments';

        $asset = Asset::find($assignment->asset_id);      

        $scrollToTabSection = 'assignments';

        return view('assets.assignments.reject',compact('assignment','tab','asset','scrollToTabSection'));
    }

    public function rejectassetassignment(Request $request,AssetAssignment $assignment){   
        
         if($assignment->transfer_status != 'in_transit'){

            return redirect()->back()->with('error', 'Assignment cannot be rejected');
        }

        $assignment->rejected_at = now();

        $assignment->rejected_by = auth()->user()->id;

        $assignment->rejected_notes = $request->rejected_notes;

        $assignment->transfer_status = 'rejected';  

        $assignment->status = 'transferred';

        $assignment->save();



        return redirect_to_asset_show_section($assignment->asset_id, 'assignments')
            ->with('success', 'Asset rejected successfully.'); 
    }

    public function returnassignment(AssetAssignment $assignment){

        if($assignment->transfer_status != 'accepted'){

            return redirect()->back()->with('error', 'Assignment cannot be returned');
        }

        $tab='assignments';

       $asset = Asset::find($assignment->asset_id);

       $scrollToTabSection = 'assignments';

       return view('assets.assignments.return',compact('asset','tab','assignment','scrollToTabSection'));
        
    }

    public function returnassetassignment(Request $request,AssetAssignment $assignment){

        $request->validate([
            'return_notes' => 'required',
        ]);


        if($assignment->transfer_status != 'accepted'){

            return redirect()->back()->with('error', 'Assignment cannot be returned');
        }

        $assignment->returned_at = now();

        $assignment->returned_by = auth()->user()->id;

        $assignment->return_notes = $request->return_notes;

        $assignment->transfer_status = 'return';  

        $assignment->status = 'transferred';

        $assignment->save();


        return redirect_to_asset_show_section($assignment->asset_id, 'assignments')
            ->with('success', 'Asset returned successfully.');


    }


    public function acceptReturn(AssetAssignment $assignment){


       if($assignment->transfer_status != 'rejected' && $assignment->transfer_status != 'return'){

            return redirect()->back()->with('error', 'Assignment cannot be accepted');
        }

          $tab='assignments';

       $asset = Asset::find($assignment->asset_id);

       $scrollToTabSection = 'assignments';

       return view('assets.assignments.accept-return',compact('asset','tab','assignment','scrollToTabSection'));


    }

    public function acceptReturnUpdate(Request $request,AssetAssignment $assignment){

        if($assignment->transfer_status != 'rejected' && $assignment->transfer_status != 'return'){

            return redirect()->back()->with('error', 'Assignment cannot be accepted');
        }

        $assignment->transfer_status = 'return_accepted';

        $assignment->status = 'returned';

        $assignment->return_accepted_by=auth()->user()->id;

        $assignment->return_accepted_at=now();

        $assignment->return_accepted_notes=$request->return_accepted_notes;

        $assignment->save();

        return redirect_to_asset_show_section($assignment->asset_id, 'assignments')
            ->with('success', 'Asset - Return accepted successfully.');


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
    public function edit(AssetAssignment $assignment)
    {
       $tab='assignments';

       $asset = Asset::find($assignment->asset_id);

       if($assignment->transfer_status != 'in_transit'){

        return redirect()->back()->with('error', 'Assignment cannot be edited');

       }

       $companies=Company::active()->select('id','name')->get();

       $custodians = $this->assignmentCustodians($assignment->assiged_emoloyee ?: $asset->custodian_id);

       $branches=Branch::Where('company_id',$assignment->to_company_id)->select('id','name')->get();

       $locations=BranchLocation::Where('branch_id', $assignment->to_branch)->where('status', 'active')->select('id','location_name')->get();

      // print_r($branches); exit;


       $scrollToTabSection = 'assignments';

       return view('assets.assignments.edit', array_merge(
           compact('asset', 'tab', 'assignment', 'branches', 'locations', 'companies', 'custodians', 'scrollToTabSection'),
           $this->assignmentFormLists($assignment->department_id ?: $asset->department_id)
       ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AssignAssetRequest $request, AssetAssignment $assignment)
    {

        if($assignment->transfer_status != 'in_transit'){

            return redirect()->back()->with('error', 'Assignment cannot be updated');
        }

        // if($request->to_company_id == $assignment->company_id && $request->to_branch == $assignment->branch_id){

        //     return redirect()->back()->with('error', 'Cannot Assign Same Branch and Company');
        // }

        $assignment->update($request->validated());
        $assignment->syncAssetLocationFromTransfer();

        return redirect_to_asset_show_section($assignment->asset_id, 'assignments')
            ->with('success', 'Assignment updated successfully.');
    }

    /**
     * Assigned and transferred records can change the assigned date only.
     */
    public function updateAssignDate(Request $request, AssetAssignment $assignment)
    {
        if (! $assignment->canEditAssignDate($request->user())) {
            return redirect()->back()->with('error', 'Assigned date cannot be changed for this assignment.');
        }

        $data = $request->validate([
            'assignment_id' => ['required', 'integer'],
            'assign_date' => ['required', 'date'],
        ]);

        if ((int) $data['assignment_id'] !== (int) $assignment->id) {
            return redirect()->back()->with('error', 'Assigned date cannot be changed for this assignment.');
        }

        $assignment->update([
            'assign_date' => $data['assign_date'],
        ]);

        return redirect_to_asset_show_section($assignment->asset_id, 'assignments')
            ->with('success', 'Assigned date updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AssetAssignment $assignment)
    {
        $asset_id=$assignment->asset_id;

        $assignment->delete();

        return redirect_to_asset_show_section($asset_id, 'assignments')
            ->with('success', 'Assignment deleted successfully.');
    }


    private function assignmentCustodians($selectedId = null)
    {
        $custodians = AssetCustodian::active()
            ->orderBy('employee_name')
            ->get(['id', 'employee_name', 'department_id']);

        if ($selectedId && ! $custodians->contains('id', $selectedId)) {
            $selected = AssetCustodian::query()->find($selectedId, ['id', 'employee_name', 'department_id']);
            if ($selected) {
                $custodians->push($selected);
            }
        }

        return $custodians->sortBy('employee_name')->values();
    }

    private function assignmentFormLists($selectedDepartmentId = null): array
    {
        $departments = Department::query()
            ->where(function ($query) use ($selectedDepartmentId) {
                $query->where('status', 'active');
                if ($selectedDepartmentId) {
                    $query->orWhere('id', $selectedDepartmentId);
                }
            })
            ->orderBy('department_name')
            ->get(['id', 'department_name']);

        return compact('departments');
    }

    public function assignBulk(){

    $assets = Asset::where('status', 'available')
    ->doesntHave('assignments')
    ->get();

     $companies=Company::active()->select('id','name')->get();


    return view('assets.assignments.bulk-assign',compact('assets','companies'));
    }

    public function assignBulkProcess(Request $request){

        $request->validate([
            'assets' => 'required|array|min:1',
            'to_company_id' => 'required',
            'to_branch' => 'required', 
        ]);


        foreach($request->assets as $asset_id){

            $asset = Asset::find($asset_id);

            if($asset->status->value != 'available'){
                continue;
            }

            $checkInTransit=AssetAssignment::where('asset_id',$asset->id)->where('transfer_status','in_transit')->first(); 
            
            if($checkInTransit){

                continue;
            }

            $data['assign_date'] = now(); 
            $data['asset_id'] = $asset->id; 
            $data['from_company_id'] = $asset->company_id; 
            $data['from_branch'] = $asset->branch_id; 
            $data['from_location'] = $asset->location_id; 
            $data['transfer_status'] = 'in_transit'; 
            $data['status'] = 'transferred'; 
            $data['assigned_by'] = auth()->user()->id; 
            $data['assigned_at'] = now();  
            $data['to_company_id'] = $request->to_company_id; 
            $data['to_branch'] = $request->to_branch;
            $data['to_location'] = $request->to_location;

            $assignment = $asset->assignments()->create($data); 

            if($assignment->transfer_status == 'in_transit'){ 
                $asset->status = 'transferred'; 
                $asset->save(); 
            }



        }


        return redirect()
        ->route('asset.index')
        ->with('success', 'Assets assigned successfully.');
       
        
    }


}
