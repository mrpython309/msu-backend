@extends('layouts/contentNavbarLayout')

@section('title', 'Create HSN')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">Create HSN</h4>

<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">New HSN</h5>
        <a href="{{ route('hsns.index') }}" class="btn btn-secondary">Back to list</a>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('hsns.store') }}">
            @csrf
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">HSN Code <span class="text-danger">*</span></label>
                    <input type="text" name="hsn_code" class="form-control @error('hsn_code') is-invalid @enderror" value="{{ old('hsn_code') }}" placeholder="e.g. 8471" required minlength="4" maxlength="4">
                    @error('hsn_code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-5 mb-3">
                    <label class="form-label">Description <span class="text-danger">*</span></label>
                    <input type="text" name="description" class="form-control @error('description') is-invalid @enderror" value="{{ old('description') }}" placeholder="Enter description">
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Tax Rate (%) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" max="100" name="tax_rate" class="form-control @error('tax_rate') is-invalid @enderror" value="{{ old('tax_rate', '0') }}">
                    @error('tax_rate')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select @error('status') is-invalid @enderror">
                        <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Create HSN</button>
            <a href="{{ route('hsns.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection
