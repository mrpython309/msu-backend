<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;
use App\Traits\ApiResponse;
use App\Http\Requests\Department\DepartmentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;


class DepartmentController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $departments=Department::latest()->get();

        return $this->success($departments);
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
    public function store(DepartmentRequest $request): JsonResponse
    {
        $department= Department::create($request->validated());

        // $user=auth()->user();

        // $user->departments()->syncWithoutDetaching([$department->id]);

        return $this->success($department);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $department=  Department::findOrFail($id);

         return $this->success($department);
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
    public function update(DepartmentRequest $request, string $id)
    {
        $department= Department::find($id);

        if(!$department){ 
             
            return $this->error('Department not found');
        }

        $department->update($request->validated());

        return $this->success($department);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $department= Department::find($id);

        if(!$department){ 
             
            return $this->error('Department not found');
        }

        $department->delete();

        return $this->success('Department deleted successfully');
    }
}
