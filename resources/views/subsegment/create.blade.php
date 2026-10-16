@extends('layouts/contentNavbarLayout')

@section('title', 'Create Sub Segment')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
    Create Sub Segment
</h4>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0">New Sub Segment</h5>
                <a href="{{ route('subsegment.index') }}" class="btn btn-secondary">
                    Back to list
                </a>
            </div>

            <div class="card-body">
                <form method="POST" action="{{ route('subsegment.store') }}">
                    @csrf

                    <div class="row">

                        <!-- Segment Dropdown -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Segment <span class="text-danger">*</span>
                            </label>
                            <select
                                name="segment_id"
                                class="form-select @error('segment_id') is-invalid @enderror"
                                
                            >
                                <option value="">Select Segment</option>
                                @foreach($segments as $segment)
                                    <option value="{{ $segment->id }}"
                                        {{ old('segment_id') == $segment->id ? 'selected' : '' }}>
                                        {{ $segment->name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('segment_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Sub Segment Name -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Sub Segment Name <span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                class="form-control @error('subsegment_name') is-invalid @enderror"
                                name="subsegment_name"
                                value="{{ old('subsegment_name') }}"
                                placeholder="Enter sub segment name"
                                
                            >
                            @error('subsegment_name')
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
                            Create Sub Segment
                        </button>
                        <a href="{{ route('subsegment.index') }}" class="btn btn-secondary">
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
