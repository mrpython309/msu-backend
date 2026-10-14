@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Department')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
    Edit Department
</h4>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0">Edit Department</h5>
                <a href="{{ route('department.index') }}" class="btn btn-secondary">
                    Back to list
                </a>
            </div>

            <div class="card-body">
                <form method="POST" action="{{ route('department.update', $department->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        <!-- Department Name -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Department Name <span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                class="form-control @error('department_name') is-invalid @enderror"
                                name="department_name"
                                value="{{ old('department_name', $department->department_name) }}"
                                
                            >
                            @error('department_name')
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
                                <option value="active" {{ old('status', $department->status) == 'active' ? 'selected' : '' }}>
                                    Active
                                </option>
                                <option value="inactive" {{ old('status', $department->status) == 'inactive' ? 'selected' : '' }}>
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
                            Update Department
                        </button>
                        <a href="{{ route('department.index') }}" class="btn btn-secondary">
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
