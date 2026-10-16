@extends('layouts/contentNavbarLayout')

@section('title', 'Create Asset Type')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
    Create Asset Type
</h4>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0">New Asset Type</h5>
                <a href="{{ route('asset-types.index') }}" class="btn btn-secondary">
                    Back to list
                </a>
            </div>

            <div class="card-body">
                <form method="POST" action="{{ route('asset-types.store') }}">
                    @csrf

                    <div class="row">

                        <div class="col-md-6 mb-3">
                        <label class="form-label">Parent Category <span class="text-danger">*</span></label>
                        <select id="category_id" name="category_id"
                            class="form-select @error('category_id') is-invalid @enderror" required>
                            <option value="">Select Parent Category</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}">{{ $category->category_name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                      <div class="col-md-6 mb-3">
                        <label class="form-label">Category <span class="text-danger">*</span></label>
                        <select id="sub_category_id" name="sub_category_id"
                            class="form-select @error('sub_category_id') is-invalid @enderror" required>
                            <option value="">Select Category</option>                            
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
                                value="{{ old('asset_type_name') }}"
                                placeholder="Enter Asset Type"
                            >
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
                                placeholder="Enter Asset Type Description" >{{ old('asset_type_description') }}</textarea>
                           
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
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>
                                    Active
                                </option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>
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
                            Create Asset Type
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

</script>

@endsection
