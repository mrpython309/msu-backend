@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Product Category')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
    Edit Product Category
</h4>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0">Edit Product Category</h5>
                <a href="{{ route('product-categories.index') }}" class="btn btn-secondary">
                    Back to list
                </a>
            </div>

            <div class="card-body">
                <form method="POST" action="{{ route('product-categories.update', $productCategory->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">
                                Category Name <span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                class="form-control @error('category_name') is-invalid @enderror"
                                name="category_name"
                                id="category_name"
                                value="{{ old('category_name', $productCategory->category_name) }}"
                            >
                            @error('category_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">
                                Short Name <span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                class="form-control @error('category_code') is-invalid @enderror"
                                name="category_code"
                                id="category_code"
                                value="{{ old('category_code', $productCategory->category_code) }}"
                                maxlength="3"
                                placeholder="e.g. COM"
                                style="text-transform: uppercase;"
                            >
                            <small class="text-muted">Suggested from category name (first 3 letters). You can edit it. Changing the code updates linked item codes.</small>
                            @error('category_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4 mb-3">
                            <label class="form-label">
                                Status <span class="text-danger">*</span>
                            </label>
                            <select
                                name="status"
                                class="form-select @error('status') is-invalid @enderror"
                            >
                                <option value="active" {{ old('status', $productCategory->status) == 'active' ? 'selected' : '' }}>
                                    Active
                                </option>
                                <option value="inactive" {{ old('status', $productCategory->status) == 'inactive' ? 'selected' : '' }}>
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
                            Update Product Category
                        </button>
                        <a href="{{ route('product-categories.index') }}" class="btn btn-secondary">
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
(function() {
    var $name = $('#category_name');
    var $code = $('#category_code');
    if (!$name.length || !$code.length) return;

    // Existing short name is treated as manual so editing the category name does not overwrite it.
    var manualEdit = !!$.trim($code.val() || '');

    function suggestCode() {
        return (($name.val() || '').replace(/[^A-Za-z]/g, '').substring(0, 3) || '').toUpperCase();
    }

    function syncCode() {
        if (manualEdit) return;
        $code.val(suggestCode());
    }

    $name.on('input', syncCode);
    $code.on('input', function() {
        manualEdit = true;
        this.value = (this.value || '').replace(/[^A-Za-z]/g, '').substring(0, 3).toUpperCase();
        if (!this.value) {
            manualEdit = false;
            syncCode();
        }
    });
})();
</script>
@endsection
