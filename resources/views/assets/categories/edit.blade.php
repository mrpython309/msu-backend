@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Asset Category')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
    Edit Asset Category
</h4>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0">Edit Asset Category</h5>
                <a href="{{ route('asset-category.index') }}" class="btn btn-secondary">
                    Back to list
                </a>
            </div>

            <div class="card-body">
                <form method="POST" action="{{ route('asset-category.update', $asset_category->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="row">

                        <!-- Category Name -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Category Name <span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                class="form-control @error('category_name') is-invalid @enderror"
                                name="category_name"
                                value="{{ old('category_name', $asset_category->category_name) }}"
                                placeholder="Enter category name"
                                required
                            >
                            @error('category_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Parent Category -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Parent Category</label>
                            <select
                                id="parent_id"
                                name="parent_id"
                                class="form-select @error('parent_id') is-invalid @enderror"
                            >
                                <option value="">Select Parent Category</option>
                                @foreach ($categories as $category)
                                    @if ($category->id !== $asset_category->id)
                                        <option value="{{ $category->id }}"
                                            {{ old('parent_id', $asset_category->parent_id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->category_name }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                            @error('parent_id')
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
                                required
                            >
                                <option value="active"
                                    {{ old('status', $asset_category->status) == 'active' ? 'selected' : '' }}>
                                    Active
                                </option>
                                <option value="inactive"
                                    {{ old('status', $asset_category->status) == 'inactive' ? 'selected' : '' }}>
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
                            Update Asset Category
                        </button>
                        <a href="{{ route('asset-category.index') }}" class="btn btn-secondary">
                            Cancel
                        </a>
                    </div>

                    @if (session('success'))
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
