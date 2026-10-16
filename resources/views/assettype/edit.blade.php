@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Asset Type')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
    Edit Asset Type
</h4>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0">Edit Asset Type</h5>
                <a href="{{ route('asset-types.index') }}" class="btn btn-secondary">
                    Back to list
                </a>
            </div>

            <div class="card-body">
                <form method="POST" action="{{ route('asset-types.update', $asset_type->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="row">

                        <div class="col-md-6 mb-3">
                        <label class="form-label">Parent Category <span class="text-danger">*</span></label>
                        <select id="category_id" name="category_id"
                            class="form-select @error('category_id') is-invalid @enderror" required>
                            <option value="">Select Parent Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id', $asset_type->category_id) == $category->id ? 'selected' : '' }}>{{ $category->category_name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                      <div class="col-md-6 mb-3">
                        <label class="form-label">Category <span class="text-danger">*</span></label>
                        <select id="sub_category_id" name="sub_category_id"
                            class="form-select @error('sub_category_id') is-invalid @enderror" required>
                            <option value="">Select Category</option>  
                            @foreach($subCategories as $sub_category)
                                <option value="{{ $sub_category->id }}" {{ old('sub_category_id', $asset_type->sub_category_id) == $sub_category->id ? 'selected' : '' }}>{{ $sub_category->category_name }}</option>
                            @endforeach                          
                        </select>
                        @error('sub_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>



                        <!-- Department Name -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Asset Type Name <span class="text-danger">*</span>
                            </label>
                                <input
                                    type="text"
                                    class="form-control @error('asset_type_name') is-invalid @enderror"
                                    name="asset_type_name"
                                    id="asset_type_name"
                                    value="{{ old('asset_type_name', $asset_type->asset_type_name) }}"
                                    list="asset-name-options"
                                    autocomplete="off"
                                >
                            <datalist id="asset-name-options"></datalist>
                            @error('asset_type_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                         <!-- Brand Description -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Asset Type Description 
                            </label>
                            <textarea class="form-control @error('asset_type_description') is-invalid @enderror" name="asset_type_description"
                                placeholder="Enter Asset Type Description" >{{ old('asset_type_description', $asset_type->asset_type_description) }}</textarea>
                           
                            @error('asset_type_description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Status -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Status <span class="text-danger">*</span>
                            </label>
                            <select
                                name="status"
                                class="form-select @error('status') is-invalid @enderror"
                                
                            >
                                <option value="active" {{ old('status', $asset_type->status) == 'active' ? 'selected' : '' }}>
                                    Active
                                </option>
                                <option value="inactive" {{ old('status', $asset_type->status) == 'inactive' ? 'selected' : '' }}>
                                    Inactive
                                </option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="pt-3">
                        <button type="submit" class="btn btn-primary">
                            Update Asset Type
                        </button>
                        <a href="{{ route('asset-types.index') }}" class="btn btn-secondary">
                            Cancel
                        </a>
                    </div>

                    @if(session('success'))
                        <div class="alert alert-success mt-3">
                            {{ session('success') }}
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>
@endsection


@section('page-script')

<script>

  $('#category_id').on('change', function () {
        let categoryId = $(this).val();
        let subCategoryDropDown = $('#sub_category_id');

        subCategoryDropDown.html('<option value="">Loading...</option>');

        if (categoryId) {
            $.ajax({
                url: "{{ route('ajax.getsubcategory.bycategory', ':id') }}".replace(':id', categoryId),
                type: "GET",
                dataType: "json",
                success: function (res) {
                    subCategoryDropDown.empty();
                    subCategoryDropDown.append('<option value="">Select Category</option>');

                    if (res.data.length > 0) {
                        $.each(res.data, function (key, value) {
                            subCategoryDropDown.append(
                                `<option value="${value.id}">${value.category_name}</option>`
                            );
                        });
                    } else {
                        subCategoryDropDown.append('<option value="">No Category found</option>');
                    }
                },
                error: function () {
                    subCategoryDropDown.html('<option value="">Failed to load Category</option>');
                }
            });
        } else {
            subCategoryDropDown.html('<option value="">Select Category</option>');
        }
    }); 

const $assetTypeName = $('#asset_type_name');
const $assetNameOptions = $('#asset-name-options');

function renderAssetNameOptions(items) {
    $assetNameOptions.empty();
    items.forEach(function(item) {
        const value = typeof item === 'string' ? item : item.name;
        $assetNameOptions.append(`<option value="${$('<div>').text(value).html()}"></option>`);
    });
}

function fetchAssetNameSuggestions(term) {
    $.ajax({
        url: "{{ route('asset-names.suggest') }}",
        type: "GET",
        dataType: "json",
        data: { q: term, limit: 50 },
        success: function(res) {
            renderAssetNameOptions((res && res.data) ? res.data : []);
        }
    });
}

$assetTypeName.on('focus input', function() {
    fetchAssetNameSuggestions($(this).val() || '');
});
</script>

@endsection
