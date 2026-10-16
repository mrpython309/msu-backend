@extends('layouts/contentNavbarLayout')

@section('title', 'Add Asset')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
  <span class="text-muted fw-light">Master / <a href="{{ route('asset-masters.index') }}">Asset Master</a> /</span> Add Asset
</h4>

<div class="row">
    <div class="col-md-12">
        <div class="card mb-4">
            <h5 class="card-header">New Asset Master</h5>
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form action="{{ route('asset-masters.store') }}" method="POST">
                    @csrf
                    
                    <div class="row">
                        <div class="mb-3 col-md-6">
                            <label for="category_id" class="form-label">Asset Category <span class="text-danger">*</span></label>
                            <select id="category_id" name="category_id" class="form-select select2 @error('category_id') is-invalid @enderror" required>
                                <option value="">Select Category</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->category_name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="asset_type_id" class="form-label">Asset Type <span class="text-danger">*</span></label>
                            <select id="asset_type_id" name="asset_type_id" class="form-select select2 @error('asset_type_id') is-invalid @enderror" required>
                                <option value="">Select Asset Type</option>
                            </select>
                            @error('asset_type_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="item_name" class="form-label">Item Name <span class="text-danger">*</span></label>
                            <input class="form-control @error('item_name') is-invalid @enderror" type="text" id="item_name" name="item_name" value="{{ old('item_name') }}" required />
                            @error('item_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="uom" class="form-label">Unit of Measure</label>
                            <input class="form-control @error('uom') is-invalid @enderror" type="text" id="uom" name="uom" value="{{ old('uom') }}" placeholder="e.g. Nos, Kg, Box" />
                            @error('uom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 col-md-12">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3 col-md-6">
                            <label for="status" class="form-label">Status</label>
                            <select id="status" name="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="mt-2">
                        <button type="submit" class="btn btn-primary me-2">Save</button>
                        <a href="{{ route('asset-masters.index') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
$(document).ready(function() {
    var oldAssetTypeId = "{{ old('asset_type_id') }}";

    if (typeof $.fn.select2 === 'function') {
        $('#category_id, #asset_type_id').select2({
            placeholder: 'Select',
            allowClear: true,
            width: '100%'
        });
    }
    
    function loadAssetTypes(categoryId, selectedTypeId) {
        var $assetTypeSelect = $('#asset_type_id');
        $assetTypeSelect.empty();
        $assetTypeSelect.append('<option value="">Select Asset Type</option>');
        
        if (categoryId) {
            $.ajax({
                url: '{{ route("asset-masters.get-asset-types") }}',
                type: 'GET',
                data: { category_id: categoryId },
                success: function(data) {
                    $.each(data, function(key, value) {
                        var selected = (selectedTypeId == value.id) ? 'selected' : '';
                        $assetTypeSelect.append('<option value="'+ value.id +'" '+selected+'>'+ value.asset_type_name +'</option>');
                    });
                    $assetTypeSelect.val(selectedTypeId || '').trigger('change');
                }
            });
        } else {
            $assetTypeSelect.val('').trigger('change');
        }
    }

    $('#category_id').on('change', function() {
        loadAssetTypes($(this).val(), null);
    });

    var currentCategoryId = $('#category_id').val();
    if (currentCategoryId) {
        loadAssetTypes(currentCategoryId, oldAssetTypeId);
    }
});
</script>
@endsection
