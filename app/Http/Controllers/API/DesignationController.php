<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Designation;
use App\Traits\ApiResponse;
use App\Http\Requests\Designation\DesignationRequest;

class DesignationController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         $designations=Designation::get();

        return $this->success($designations);
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
    public function store(DesignationRequest $request)
    {
        $designation=Designation::create($request->all());

        return $this->success($designation);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $designation=Designation::find($id);

        if(!$designation){

            return $this->error('Designation not found');
        }

        return $this->success($designation);
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
    public function update(DesignationRequest $request, string $id)
    {
        $designation=Designation::find($id);

        if(!$designation){

            return $this->error('Designation not found');
        }

        $designation->update($request->all());

        return $this->success($designation);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $designation=Designation::find($id);

        if(!$designation){

            return $this->error('Designation not found');
        }

        $designation->delete();

        return $this->success('Designation deleted successfully');
    }
}
