@extends('layouts/contentNavbarLayout')

@section('title', 'Create Miscellaneous Charge')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">Create Miscellaneous Charge</h4>
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">New Miscellaneous Charge</h5>
        <a href="{{ route('miscellaneous-charges.index') }}" class="btn btn-secondary">Back to list</a>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('miscellaneous-charges.store') }}">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Description <span class="text-danger">*</span></label>
                    <input type="text" name="charge_name" class="form-control @error('charge_name') is-invalid @enderror" value="{{ old('charge_name') }}" placeholder="e.g. Freight, Handling">
                    @error('charge_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Short Name <span class="text-danger">*</span></label>
                    <input type="text" name="code" class="form-control @error('code') is-invalid @enderror" value="{{ old('code') }}" placeholder="e.g. FRGT">
                    @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Active <span class="text-danger">*</span></label>
                    <select name="status" class="form-select">
                        <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Create Miscellaneous Charge</button>
            <a href="{{ route('miscellaneous-charges.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection
