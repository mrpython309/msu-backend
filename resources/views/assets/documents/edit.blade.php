@extends('assets.asset-layout')
 

@section('asset_section')

<div class="card card-action mb-6">
      <div class="card-header align-items-center py-6">
        <h5 class="card-action-title mb-0">Update Document</h5>
        <div class="card-action-element">
          <a class="btn btn-sm btn-primary text-white" href="{{asset_show_section_url($asset, 'documents') }}">Back to Documents List</a>
        </div>
      </div>
      <div class="card-body">

        <form action="{{ route('asset.document.update', $document) }}" method="POST" enctype="multipart/form-data">

        <div class="row g-3">

          <div class="col-4">
            <label class="form-label form-label-required" for="document_name">Document Title<span class="text-danger">*</span></label>
            <input type="text" name="document_name" required class="form-control @error('document_name') is-invalid @enderror" value="{{ old('document_name',$document->document_name) }}">
            @error('document_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
         </div>          

          <div class="col-4">
            <label class="form-label">Document File </label>
            <input class="form-control @error('filename') is-invalid @enderror" type="file" id="filename" name="filename" accept=".jpg,.jpeg,.png,.webp,.pdf,.doc,.docx,.xls,.xlsx">
            @error('filename')<div class="invalid-feedback">{{ $message }}</div>@enderror

              @if ($document->doc_url)
                            <div class="d-flex align-items-center gap-2 mt-2">
                                @if ($document->icon == 'images')
                                    <a href="{{ $document->doc_url }}" data-lightbox="asset-document-edit-{{ $document->id }}" data-title="{{ $document->document_name }}">
                                        <img src="{{ $document->doc_url }}" alt="{{ $document->document_name }}" class="img-fluid rounded" style="max-height: 48px;">
                                    </a>
                                @else
                                    <a href="{{ $document->doc_url }}" target="_blank" rel="noopener" class="d-inline-flex align-items-center gap-2 text-body">
                                        <i class="bx {{ $document->fileIconClass() }}" style="font-size: 1.5rem;"></i>
                                        <span>{{ $document->filename }}</span>
                                    </a>
                                @endif
                                <a href="{{ $document->doc_url }}" download="{{ $document->filename }}" class="btn btn-sm btn-label-primary">Download</a>
                            </div>
                            @endif

          </div>

             <div class="col-4">
            <label class="form-label" for="document_notes">Notes</label>
            <textarea class="form-control @error('document_notes') is-invalid @enderror" id="document_notes" name="document_notes" rows="2">{{ old('document_notes',$document->document_notes) }}</textarea>
            @error('document_notes') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>

           

        </div>

         <div class="pt-5 float-end">
            @csrf
            @method('PUT')
            <input type="hidden" name="asset_id" id="asset_id" value="{{$asset->id}}">
                        <button type="submit" class="btn btn-primary">
                            Update
                        </button>
                        <a href="{{asset_show_section_url($asset, 'documents') }}" class="btn btn-secondary">
                            Cancel
                        </a>
                    </div>

        </form>
       
       

      </div>
    </div> 
 

 

@endsection

