<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Branch;
use App\Traits\ApiResponse;
use App\Http\Requests\Branches\BranchRequest;

class BranchController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         $branches=Branch::with('company:id,name')->latest()->paginate(20);

        return $this->successWithPagination($branches);
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
    public function store(BranchRequest $request)
    {
        $branch= Branch::create($request->validated());

       // $user=auth()->user();

      //  $user->branches()->syncWithoutDetaching([$branch->id]);

        return $this->success($branch);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $branch= Branch::find($id);

        if(!$branch){
            return $this->error('Branch not found');
        }

        return $this->success($branch);
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
    public function update(BranchRequest $request, string $id)
    {
        $branch= Branch::find($id);

        if(!$branch){
            return $this->error('Branch not found');
        }

        $branch->update($request->validated());

        return $this->success($branch);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $branch= Branch::find($id);

        if(!$branch){
            return $this->error('Branch not found');
        }

        if($branch->hasDependencies()){

            return $this->error('cannot delete branch because it has mapped with other records');
            
        }

        $branch->delete();

        return $this->success('Branch deleted successfully');
    }
}
