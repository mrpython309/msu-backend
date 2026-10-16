<?php

namespace App\Http\Controllers\Assets;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AssetDocument;
use App\Models\Asset;
use App\Http\Requests\Assets\SaveAssetDocumentRequest;
use App\Helpers\Files;

class AssetDocumentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        if(!isset($request->asset_id)){
           
           return redirect()->back()->with('error', 'Asset not found');
       }

        $tab='documents';

         $asset = Asset::find($request->asset_id);

        return view('assets.documents.create',compact('asset','tab'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SaveAssetDocumentRequest $request)
    {

          $data = $request->validated();

        if($request->hasFile('filename') && $request->file('filename')->isValid()){    

            $data['filename'] = Files::uploadLocalOrS3($request->filename,  AssetDocument::FILE_PATH);
        }    

        AssetDocument::create($data);

        return redirect_to_asset_show_section($request->asset_id, 'documents')->with('success', 'Document created successfully.');
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
    public function edit(AssetDocument $document)
    {
         $tab='documents';

         $asset = Asset::find($document->asset_id);

        return view('assets.documents.edit',compact('asset','tab','document'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SaveAssetDocumentRequest $request, AssetDocument $document)
    {

        $data = $request->validated();

        if($request->hasFile('filename') && $request->file('filename')->isValid()){

            $data['filename'] = Files::uploadLocalOrS3($request->filename,  AssetDocument::FILE_PATH);
        }

        $document->update($data);

        return redirect_to_asset_show_section($document->asset_id, 'documents')->with('success', 'Document updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(AssetDocument $document)
    {
        $document->delete();
        return redirect_to_asset_show_section($document->asset_id, 'documents')->with('success', 'Document deleted successfully.');
    }
}
