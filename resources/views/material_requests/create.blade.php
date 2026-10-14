@extends('layouts/contentNavbarLayout')

@section('title', 'Create Material Request')

@section('content')
@php
    $oldItems = old('items', [['msu_item_master_id' => '', 'requested_qty' => '', 'unit_id' => '']]);
@endphp
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Create Material Request</h4>
</div>

<div class="card mb-4">
    <form action="{{ route('material-requests.store') }}" method="POST" id="material-request-form" novalidate>
        @csrf
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">MR Number</label>
                    <input type="text" class="form-control" id="mr_number_preview"
                           value="{{ $previewMrNumber ?? 'MSULLP/BRN/SR/MR/----/--/0001' }}"
                           disabled>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Request Date <span class="text-danger">*</span></label>
                    <input type="date" name="request_date" class="form-control" value="{{ old('request_date', date('Y-m-d')) }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Department</label>
                    <select name="department_id" class="form-select select2">
                        <option value="">Select Department</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ (string) old('department_id') === (string) $dept->id ? 'selected' : '' }}>{{ $dept->department_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Employee</label>
                    <select name="employee_master_id" class="form-select select2">
                        <option value="">Select Employee</option>
                        @foreach($employees as $emp)
                            <option value="{{ $emp->id }}" {{ (string) old('employee_master_id') === (string) $emp->id ? 'selected' : '' }}>{{ $emp->employee_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Branch <span class="text-danger">*</span></label>
                    <select name="branch_id" class="form-select select2" required>
                        <option value="">Select Branch</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" data-code="{{ $branch->code ?: 'BRN' }}" {{ (string) old('branch_id') === (string) $branch->id ? 'selected' : '' }}>{{ $branch->code ? $branch->code.' - ' : '' }}{{ $branch->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <hr class="my-4">

            @include('material_requests._items-table')
        </div>
        <div class="card-footer">
            <button type="submit" class="btn btn-primary">Save Request</button>
            <a href="{{ route('material-requests.index') }}" class="btn btn-secondary">Cancel</a>
        </div>
    </form>
</div>
@endsection

@section('page-script')
<script>
    $(document).ready(function() {
        $('.select2').select2({
            width: '100%',
            minimumResultsForSearch: 0
        });

        $('select[name="branch_id"]').on('change', function() {
            let branchCode = $(this).find(':selected').data('code') || 'BRN';
            let preview = $('#mr_number_preview').val();
            let parts = preview.split('/');
            if (parts.length > 2) {
                parts[1] = branchCode;
                $('#mr_number_preview').val(parts.join('/'));
            }
        });

        if ($('select[name="branch_id"]').val()) {
            $('select[name="branch_id"]').trigger('change');
        }
    });
</script>
@endsection
