@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Cost Centre')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
    Edit Cost Centre
</h4>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0">Edit Cost Centre</h5>
                <a href="{{ route('cost-centres.index') }}" class="btn btn-secondary">
                    Back to list
                </a>
            </div>

            <div class="card-body">
                <form method="POST" action="{{ route('cost-centres.update', $costCentre->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Cost Centre Name <span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                class="form-control @error('cost_centre_name') is-invalid @enderror"
                                name="cost_centre_name"
                                value="{{ old('cost_centre_name', $costCentre->cost_centre_name) }}"
                            >
                            @error('cost_centre_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Cost Centre Description
                            </label>
                            <textarea class="form-control @error('cost_centre_description') is-invalid @enderror" name="cost_centre_description"
                                placeholder="Enter Cost Centre Description">{{ old('cost_centre_description', $costCentre->cost_centre_description) }}</textarea>
                            @error('cost_centre_description')
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
                                <option value="active" {{ old('status', $costCentre->status) == 'active' ? 'selected' : '' }}>
                                    Active
                                </option>
                                <option value="inactive" {{ old('status', $costCentre->status) == 'inactive' ? 'selected' : '' }}>
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
                            Update Cost Centre
                        </button>
                        <a href="{{ route('cost-centres.index') }}" class="btn btn-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
