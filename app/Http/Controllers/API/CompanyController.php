<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Company;
use App\Http\Requests\Companies\CompanyRequest;
use App\Traits\ApiResponse;

class CompanyController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $compaines=Company::latest()->get();

        return $this->success($compaines);
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
    public function store(CompanyRequest $request)
    {
        $company= Company::create($request->validated());

        // $user=auth()->user();

       //  $user->companies()->syncWithoutDetaching([$company->id]);

        return $this->success($company);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $company= Company::find($id);

        if(!$company){
            return $this->error('Company not found');
        }

        return $this->success($company);
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
    public function update(CompanyRequest $request, string $id)
    {
        $company= Company::find($id);

        if(!$company){
            return $this->error('Company not found');
        }

        $company->update($request->validated());

        return $this->success($company);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $company= Company::find($id);

        if(!$company){
            return $this->error('Company not found');
        }

        if ($company->hasDependencies()) {
           
            return $this->error('cannot delete company because it has mapped with other records');
                     
        }

        $company->delete();

        return $this->success('Company deleted successfully');
    }
}
