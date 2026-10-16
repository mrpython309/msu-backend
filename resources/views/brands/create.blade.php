@extends('layouts/contentNavbarLayout')

@section('title', 'Create Brand')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
    Create Brand
</h4>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0">New Brand</h5>
                <a href="{{ route('brands.index') }}" class="btn btn-secondary">
                    Back to list
                </a>
            </div>

            <div class="card-body">
                <form method="POST" action="{{ route('brands.store') }}">
                    @csrf

                    <div class="row">
                        <!-- Department Name -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Brand Name <span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                class="form-control @error('brand_name') is-invalid @enderror"
                                name="brand_name"
                                value="{{ old('brand_name') }}"
                                placeholder="Enter Brand name"
                                
                            >
                            @error('brand_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Brand Description -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Brand Description 
                            </label>
                            <textarea class="form-control @error('brand_description') is-invalid @enderror" name="brand_description"
                                placeholder="Enter Brand Description" >{{ old('brand_description') }}</textarea>
                           
                            @error('brand_description')
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
                            Create Brand
                        </button>
                        <a href="{{ route('brands.index') }}" class="btn btn-secondary">
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
