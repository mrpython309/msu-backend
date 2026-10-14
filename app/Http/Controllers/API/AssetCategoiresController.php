<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AssetCategory;
use App\Traits\ApiResponse;
use App\Http\Requests\Assets\SaveAssetCategoryRequest;

class AssetCategoiresController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         $categories=AssetCategory::get();

        return $this->success($categories);
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
    public function store(SaveAssetCategoryRequest $request)
    {
          $category=AssetCategory::create($request->validated());

        return $this->success($category);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
          $category= AssetCategory::find($id);

        if(!$category){

            return $this->error('Asset Category not found');
        }

        return $this->success($category);
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
    public function update(SaveAssetCategoryRequest $request, string $id)
    {
           $category= AssetCategory::find($id);

        if(!$category){

            return $this->error('Asset Category not found');
        }

          $category->update($request->validated());

        return $this->success($category);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
           $category= AssetCategory::find($id);

        if(!$category){

            return $this->error('Asset Category not found');
        }

        $category->delete();

        return $this->success('Asset Category deleted successfully');
    }
}
