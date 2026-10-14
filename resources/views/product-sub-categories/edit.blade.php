@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Product Sub Category')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
    Edit Product Sub Category
</h4>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0">Edit Product Sub Category</h5>
                <a href="{{ route('product-sub-categories.index') }}" class="btn btn-secondary">
                    Back to list
                </a>
            </div>

            <div class="card-body">
                <form method="POST" action="{{ route('product-sub-categories.update', $productSubCategory->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Category <span class="text-danger">*</span>
                            </label>
                            <select
                                name="product_category_id"
                                id="product_category_id"
                                class="form-select select2 @error('product_category_id') is-invalid @enderror"
                            >
                                <option value="">Select Category</option>
                                @foreach($productCategories as $productCategory)
                                    <option value="{{ $productCategory->id }}"
                                        {{ old('product_category_id', $productSubCategory->product_category_id) == $productCategory->id ? 'selected' : '' }}>
                                        {{ $productCategory->category_name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('product_category_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Sub Category Name <span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                class="form-control @error('sub_category_name') is-invalid @enderror"
                                name="sub_category_name"
                                value="{{ old('sub_category_name', $productSubCategory->sub_category_name) }}"
                            >
                            @error('sub_category_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Status <span class="text-danger">*</span>
                            </label>
                            <select
                                name="status"
                                class="form-select @error('status') is-invalid @enderror"
                            >
                                <option value="active" {{ old('status', $productSubCategory->status) == 'active' ? 'selected' : '' }}>
                                    Active
                                </option>
                                <option value="inactive" {{ old('status', $productSubCategory->status) == 'inactive' ? 'selected' : '' }}>
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
                            Update Product Sub Category
                        </button>
                        <a href="{{ route('product-sub-categories.index') }}" class="btn btn-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
$(function() {
    if (typeof $.fn.select2 === 'function') {
        $('#product_category_id').select2({ placeholder: 'Select Category', allowClear: true, width: '100%' });
    }
});
</script>
@endsection
