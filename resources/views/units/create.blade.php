@extends('layouts/contentNavbarLayout')

@section('title', 'Create Unit')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
    Create Unit
</h4>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0">New Unit</h5>
                <a href="{{ route('units.index') }}" class="btn btn-secondary">Back to list</a>
            </div>

            <div class="card-body">
                <form method="POST" action="{{ route('units.store') }}">
                    @csrf

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Unit Name <span class="text-danger">*</span></label>
                            <input
                                type="text"
                                class="form-control @error('unit_name') is-invalid @enderror"
                                name="unit_name"
                                value="{{ old('unit_name') }}"
                                placeholder="e.g. KGS, NOS"
                            >
                            <small class="text-muted">Capital letters only, minimum 3 (e.g. KGS, NOS).</small>
                            @error('unit_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select @error('status') is-invalid @enderror">
                                <option value="active" {{ old('status', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="pt-3">
                        <button type="submit" class="btn btn-primary">Create Unit</button>
                        <a href="{{ route('units.index') }}" class="btn btn-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
