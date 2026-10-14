<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Traits\ApiResponse;
use App\Http\Requests\Assets\SaveAssetDocumentRequest;
use App\Models\AssetDocument;
use App\Helpers\Files;

class AssetDocumentsController extends Controller
{
    use ApiResponse;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
         if(!$request->asset_id){   return $this->error('Asset Id Not Found');  }

        $documents = AssetDocument::where('asset_id', $request->asset_id)->get();
        return $this->success($documents);  
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
    public function store(SaveAssetDocumentRequest $request)
    {
         $data = $request->validated();

         if($request->hasFile('filename') && $request->file('filename')->isValid()){    

            $data['filename'] = Files::uploadLocalOrS3($request->filename,  AssetDocument::FILE_PATH);
        }  
        
        $document=AssetDocument::create($data);

        return $this->success($document);

    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $document = AssetDocument::find($id);

        if(!$document){
            return $this->error('Document not found');
        }

        return $this->success($document);
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
    public function update(SaveAssetDocumentRequest $request, string $id)
    {
        $document=AssetDocument::find($id);

        if(!$document){
            return $this->error('Document not found');
        }

        $data = $request->validated();

        if($request->hasFile('filename') && $request->file('filename')->isValid()){

            $data['filename'] = Files::uploadLocalOrS3($request->filename,  AssetDocument::FILE_PATH);
        }

        $document->update($data);

        return $this->success($document);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $document=AssetDocument::find($id);

        if(!$document){
            return $this->error('Document not found');
        }

        $document->delete();
        return $this->success($document);
    }
}
