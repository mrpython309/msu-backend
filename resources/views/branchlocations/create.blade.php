@extends('layouts/contentNavbarLayout')

@section('title', 'Create Physical Location')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
    Create Physical Location
</h4>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0">New Physical Location</h5>
                <a href="{{ route('location.index') }}" class="btn btn-secondary">
                    Back to list
                </a>
            </div>

            <div class="card-body">
                <form method="POST" action="{{ route('location.store') }}" class="branch-form">
                    @csrf

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
                                value="{{ old('location_name') }}"
                                placeholder="Enter Location name"
                                
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
                                            <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
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
                            Create Physical Location
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

// INIT SELECT2
$('.select2').select2({
    placeholder: "Select",
    allowClear: true,
    closeOnSelect: true,
    dropdownParent: $('.branch-form'),
    width: '100%'
});


// ADD HEADER WHEN DROPDOWN OPENS
$(document).on('select2:open', function (e) {

    let selectId = e.target.id;

    setTimeout(function () {

        let dropdown = $('.select2-container--open');

        // Remove old header
        dropdown.find('.select2-custom-header').remove();

        let header =
        '<div class="select2-custom-header" style="padding:8px;border-bottom:1px solid #ddd;background:#f8f9fa;">' +
        '<button type="button" class="btn btn-sm btn-primary me-2 select-all-btn" data-id="'+selectId+'">Select All</button>' +
        '<button type="button" class="btn btn-sm btn-danger clear-all-btn" data-id="'+selectId+'">Clear</button>' +
        '</div>';

        dropdown.find('.select2-dropdown').prepend(header);

    }, 10);

});


// SELECT ALL
$(document).on('click', '.select-all-btn', function (e) {

    e.preventDefault();
    e.stopPropagation();

    let selectId = $(this).data('id');
    let $select = $('#' + selectId);

    console.log(selectId);

    let values = [];

    $select.find('option').each(function () {

        if (this.value !== "" && !this.disabled) {
            values.push(this.value);
        }

    });
    
     

   setTimeout(function(){
    $select.val(values).trigger('change');
    // refresh dropdown
    $select.select2('close');
  //  $select.select2('open');
    },100);

        

    });


    // CLEAR ALL
    $(document).on('click', '.clear-all-btn', function (e) {

        e.preventDefault();
        e.stopPropagation();

        let selectId = $(this).data('id');

        $('#' + selectId).val(null).trigger('change.select2');

    });

</script>

@endsection
