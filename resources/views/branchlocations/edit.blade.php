@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Physical Location')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
    Edit Physical Location
</h4>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0">Edit Physical Location</h5>
                <a href="{{ route('location.index') }}" class="btn btn-secondary">
                    Back to list
                </a>
            </div>

            <div class="card-body">
                <form method="POST" action="{{ route('location.update', $location->id) }}">
                    @csrf
                    @method('PUT')
                    @php
    $area = $location;
    $areaBranchIds = isset($area) ? ($area->branches ? $area->branches->pluck('id')->toArray() : []) : [];
    $selectedBranches = old('branch_id', !empty($areaBranchIds)
        ? $areaBranchIds
        : ($area->_selected_branch_ids ?? [$area->branch_id]));
    $selectedBranches = array_map('intval', array_filter((array) $selectedBranches));
    $isSingleBranch = count($branches) <= 1;
@endphp

                    <div class="row">
                        <!-- Designation Name -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                                Physical Location Name <span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                class="form-control @error('location_name') is-invalid @enderror"
                                name="location_name"
                                value="{{ old('location_name', $location->location_name) }}"
                                
                            >
                            @error('location_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                                    <label class="form-label form-label-required" for="branch_id">Branch</label>
                                    <select class="form-select @error('branch_id') is-invalid @enderror select2" id="branch_id" name="branch_id[]" multiple required>
                                        <option value="">Select Branch</option>
                                        @foreach($branches as $branch)
                                            <option value="{{ $branch->id }}" {{ in_array($branch->id, $selectedBranches) ? 'selected' : '' }}>
                                                {{ $branch->name }} ({{ $branch->code }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('branch_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                                <option value="active" {{ old('status', $location->status) == 'active' ? 'selected' : '' }}>
                                    Active
                                </option>
                                <option value="inactive" {{ old('status', $location->status) == 'inactive' ? 'selected' : '' }}>
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
                            Update Physical Location
                        </button>
                        <a href="{{ route('location.index') }}" class="btn btn-secondary">
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


@section('page-script')
<script>
$(function () {
    var $branchSelect = $('#branch_id');

    $branchSelect.select2({
        width: '100%',
        closeOnSelect: false
    });

    $branchSelect.on('select2:open', function () {
        var isMultiple = $branchSelect.prop('multiple');
        var $dropdown = $('.select2-container--open .select2-dropdown');
        $dropdown.find('.select-actions').remove();

        if (!isMultiple) {
            return;
        }

        $dropdown.prepend(
            '<div class="select-actions p-2 border-bottom d-flex gap-2">' +
                '<button type="button" class="btn btn-sm btn-primary" id="select_all_btn">Select All</button>' +
                '<button type="button" class="btn btn-sm btn-danger" id="clear_all_btn">Clear All</button>' +
            '</div>'
        );
    });

    $(document).on('click', '#select_all_btn', function (e) {
        e.preventDefault();
        e.stopPropagation();

        var allValues = $branchSelect.find('option').map(function () {
            return this.value;
        }).get().filter(function (v) { return v !== ''; });

        $branchSelect.val(allValues).trigger('change');
    });

    // Clear All
    $(document).on('click', '#clear_all_btn', function (e) {
        e.preventDefault();
        e.stopPropagation();

        $branchSelect.val([]).trigger('change');
    });
});
</script>
@endsection
