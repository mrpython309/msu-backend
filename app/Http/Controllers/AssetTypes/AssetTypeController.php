<?php

namespace App\Http\Controllers\AssetTypes;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AssetType;
use App\Http\Requests\Assets\AssetTypeRequest;
use App\Models\AssetCategory;

class AssetTypeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $assetTypes=AssetType::OrderBy('id','DESC')->get();

        return view('assettype.index',compact('assetTypes'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories=AssetCategory::WhereNull('parent_id')->OrderBy('id','DESC')->get();

        return view('assettype.create',compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AssetTypeRequest $request)
    {
        $validated = $request->validated();

        AssetType::create($validated);

        return redirect()
            ->route('asset-types.index')
            ->with('success', 'Asset Type created successfully.');
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
    public function edit(AssetType $asset_type)
    {
         $categories=AssetCategory::WhereNull('parent_id')->OrderBy('id','DESC')->get();

         $subCategories=AssetCategory::Where('parent_id', $asset_type->category_id)->OrderBy('id','DESC')->get();

        return view('assettype.edit', compact('asset_type','categories','subCategories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AssetTypeRequest $request, AssetType $asset_type)
    {
        $validated = $request->validated();

        $asset_type->update($validated);

        return redirect()
            ->route('asset-types.index')
            ->with('success', 'Asset Type updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AssetType $asset_type)
    {
          $asset_type->delete();

        return redirect()
            ->route('asset-types.index')
            ->with('success', 'Asset Type deleted successfully.');
    }

      public function updateStatus(Request $request)
    {
        $assetType = AssetType::findOrFail($request->id);

        $assetType->status = $request->status;
        $assetType->save();

        return response()->json(['success' => true]);
    }
}
