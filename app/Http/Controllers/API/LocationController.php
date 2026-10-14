<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BranchLocation;
use App\Traits\ApiResponse;
use App\Http\Requests\Branches\LocationRequest;

class LocationController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $locations=BranchLocation::latest()->get();

        return $this->success($locations);
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
    public function store(LocationRequest $request)
    {
        $location= BranchLocation::create($request->validated());

        return $this->success($location);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $locations=BranchLocation::find($id);

        if(!$locations){
            return $this->error('Location not found');
        }

        return $this->success($locations);
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
    public function update(LocationRequest $request, string $id)
    {
        $location= BranchLocation::find($id);

        if(!$location){
            return $this->error('Location not found');
        }

        $location->update($request->validated());

        return $this->success($location);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $location= BranchLocation::find($id);

        if(!$location){
            return $this->error('Location not found');
        }

        if ($location->hasDependencies()) {
            return $this->error('cannot delete location because it has mapped with other records');
        }

        $location->delete();

        return $this->success('Location deleted successfully');
    }
}
