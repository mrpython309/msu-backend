<?php

namespace App\Http\Controllers\Assets;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AssetCategory;
use App\Http\Requests\Assets\SaveAssetCategoryRequest; 

class AssetCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories=AssetCategory::WhereNULL('parent_id')->OrderBy('id','DESC')->get();

        return view('assets.categories.index',compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
          $categories=AssetCategory::active()->WhereNULL('parent_id')->OrderBy('id','DESC')->get();

        return view('assets.categories.create',compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SaveAssetCategoryRequest $request)
    {
        $category=new AssetCategory();
        $category->category_name=$request->category_name;
        $category->parent_id=$request->parent_id;
        $category->status=$request->status;
        $category->save();
        return redirect()->route('asset-category.index')->with('success','Category Saved Successfully');
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
    public function edit(AssetCategory $asset_category)
    {
        $categories=AssetCategory::active()->WhereNULL('parent_id')->OrderBy('id','DESC')->get();

        return view('assets.categories.edit',compact('categories','asset_category'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SaveAssetCategoryRequest $request, AssetCategory $asset_category)
    {
        $asset_category->category_name=$request->category_name;
        $asset_category->parent_id=$request->parent_id;
        $asset_category->status=$request->status;
        $asset_category->save();
        return redirect()->route('asset-category.index')->with('success','Category Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AssetCategory $asset_category)
    {
 
        if($asset_category->subCategories->count() > 0){

            return redirect()->route('asset-category.index')->with('error','Category Has Sub Categories');
        }

        $asset_category->delete();
        return redirect()->route('asset-category.index')->with('success','Category Deleted Successfully');
    }

    public function updateStatus(Request $request){

        $category = AssetCategory::findOrFail($request->id);
        $category->status = $request->status;
        $category->save(); 

        return response()->json(['success' => true]);
    }
}
