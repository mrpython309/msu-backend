@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Asset Custodian')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">Edit Asset Custodian</h4>
<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">Edit Asset Custodian</h5>
        <a href="{{ route('asset-custodians.index') }}" class="btn btn-secondary">Back to list</a>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ route('asset-custodians.update', $assetCustodian) }}">
            @csrf @method('PUT')
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Custodian Name  <span class="text-danger">*</span></label>
                    <input type="text" name="employee_name" class="form-control @error('employee_name') is-invalid @enderror" value="{{ old('employee_name', $assetCustodian->employee_name) }}">
                    @error('employee_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Department</label>
                    <select name="department_id" id="department_id" class="form-select select2 @error('department_id') is-invalid @enderror">
                        <option value="">Select Department</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ old('department_id', $assetCustodian->department_id) == $department->id ? 'selected' : '' }}>
                                {{ $department->department_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('department_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Mobile Number </label>
                    <input type="text" name="mobile_number" class="form-control @error('mobile_number') is-invalid @enderror" value="{{ old('mobile_number', $assetCustodian->mobile_number) }}">
                    @error('mobile_number')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $assetCustodian->email) }}">
                    @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-select">
                        <option value="active" {{ old('status', $assetCustodian->status) == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ old('status', $assetCustodian->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Update Custodian</button>
            <a href="{{ route('asset-custodians.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection

@section('page-script')
<script>
$(function() {
    if (typeof $.fn.select2 === 'function') {
        $('#department_id').select2({ placeholder: 'Select Department', allowClear: true, width: '100%' });
    }
});
</script>
@endsection
