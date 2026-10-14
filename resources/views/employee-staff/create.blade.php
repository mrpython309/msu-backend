@extends('layouts/contentNavbarLayout')

@section('title', 'Create Employee')

@section('page-style')
<style>
    .form-container {
        background: #f8f9fa;
        border-radius: 0.5rem;
        padding: 2rem;
        border: 1px solid #e3e6f0;
        margin-bottom: 1.5rem;
    }
    /* .form-label-required::after {
        content: " *";
        color: #dc3545;
    } */
    .form-section-title {
        color: #495057;
        font-weight: 600;
        margin-bottom: 1.5rem;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid #e9ecef;
    }

    
</style>
@endsection

@section('content')

<h4 class="py-3 breadcrumb-wrapper mb-4">
    <span class="text-muted fw-light">Create Employee</span>
</h4>

<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">New Employee</h5>
        <a href="{{ route('employee-staff.index') }}" class="btn btn-secondary">Back to list</a>
    </div>

    <div class="card-body">
        <div class="form-container">
            <form action="{{ route('employee-staff.store') }}" method="POST">
                @csrf

                {{-- BASIC DETAILS --}}
                <div class="form-section-title">Basic Details</div>
                <div class="row g-3">

                    <div class="col-md-4 mb-3">
                        <label class="form-label form-label-required">Employee Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" placeholder="Enter Employee Name" required>
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label form-label-required">Emp Code <span class="text-danger">*</span> </label>
                        <input type="text" name="emp_code" class="form-control @error('emp_code') is-invalid @enderror" value="{{ old('emp_code') }}" placeholder="Enter Emp Code" required>
                        @error('emp_code') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>                    

                    <div class="col-md-4 mb-3">
                        <label class="form-label form-label-required">Date of Joining <span class="text-danger">*</span></label>
                        <input type="date" name="date_of_joining" class="form-control @error('date_of_joining') is-invalid @enderror" value="{{ old('date_of_joining',date('Y-m-d')) }}" required>
                        @error('date_of_joining') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label form-label-required"> Mobile Number <span class="text-danger">*</span> </label>
                        <input type="text" name="mobile_number" class="form-control @error('mobile_number') is-invalid @enderror" value="{{ old('mobile_number') }}" placeholder="Enter Mobile Number" required>
                        @error('mobile_number') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label form-label-required"> Email ID <span class="text-danger">*</span> </label>
                        <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" placeholder="Enter Email" required>
                        @error('mobile_number')   <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>                    

                    <div class="col-md-4 mb-3">
                        <label class="form-label">  Designation <span class="text-danger">*</span> </label>
                        <select name="designation_id" id="designation_id" class="form-select @error('designation_id') is-invalid @enderror" required>
                            <option value="">Select Designation</option>
                            @foreach($designations as $designation)
                                <option value="{{ $designation->id }}" {{ old('designation_id') == 'active' ? 'selected' : '' }}>{{ $designation->designation_name }}</option>
                            @endforeach
                        </select>
                        @error('designation_id') <div class="invalid-feedback">{{ $message }}</div>  @enderror
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Employee Status <span class="text-danger">*</span></label>
                        <select name="employee_status" id="employee_status" class="form-select @error('employee_status') is-invalid @enderror">
                            <option value="active" {{ old('employee_status') == 'active' ? 'selected' : '' }}> Active </option>
                            <option value="inactive" {{ old('employee_status') == 'inactive' ? 'selected' : '' }}> Inactive </option>
                        </select>
                        @error('employee_status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                </div>

                {{-- ORGANIZATION DETAILS --}}
                <div class="form-section-title mt-4 " id="organization_title">Organization Details </div>
                <div class="row g-3" id="organization">
                   
                    <div class="col-md-4 mb-3">
                        <label class="form-label"> Company <span class="text-danger">*</span> </label>
                        <select name="company_id[]" id="company_id" class="form-select select2 @error('company_id') is-invalid @enderror" multiple required>
                            <option value="">Select Company</option>
                            @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ old('company_id') == 'active' ? 'selected' : '' }}>  {{ $company->name }}  </option>
                            @endforeach
                        </select>
                        @error('company_id') <div class="invalid-feedback">{{ $message }}</div>  @enderror
                    </div>                    

                    <div class="col-md-4 mb-3">
                        <label class="form-label">  Branch <span class="text-danger">*</span> </label>
                        <select name="branch_id[]" id="branch_id" multiple class="form-select select2 @error('branch_id') is-invalid @enderror" required>
                            <option value="">Select Branch</option>                            
                        </select>
                        @error('branch_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="col-md-4 mb-3">
                        <label class="form-label">  Departments <span class="text-danger">*</span> </label>
                        <select name="department_id[]" id="department_id" multiple class="form-select select2 @error('department_id') is-invalid @enderror" required>
                            <option value="">Select Department</option>    
                            @foreach ($departments as $department)
                              <option value="{{$department->id}}">{{$department->department_name}}</option>  
                            @endforeach                        
                        </select>
                        @error('department_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    
                </div>

                {{-- ACTIONS --}}
                <div class="mt-4 text-end">
                    <button class="btn btn-primary">Save Employee</button>
                    <a href="{{route('employee-staff.index')}}" class="btn btn-outline-secondary">Cancel</a>
                </div>

            </form>
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
    dropdownParent: $('.form-container'),
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

<script>
    $('#company_id').on('change', function () {

            console.log('hello');


           let selectedCompanies = $(this).val(); // This will be an array

           console.log(selectedCompanies);

           console.log(selectedCompanies);
    let branchDropdown = $('#branch_id');

    branchDropdown.html('<option value="">Loading...</option>');

    if (selectedCompanies && selectedCompanies.length > 0) {
        // If multiple companies selected, get branches from all
        $.ajax({
            url: "{{ route('ajax.getbranch.withoutscope.bycompanies') }}",
            type: "POST",
            dataType: "json",
            data: {
                company_ids: selectedCompanies,
                _token: "{{ csrf_token() }}"
            },
            success: function (res) {
                branchDropdown.empty();
                branchDropdown.append('<option value="">Select Branch</option>');

                if (res.data.length > 0) {
                    $.each(res.data, function (key, value) {
                        branchDropdown.append(
                            `<option value="${value.id}" data-company-id="${value.company_id}">${value.name} (${value.company_name})</option>`
                        );
                    });
                } else {
                    branchDropdown.append('<option value="">No branches found</option>');
                }
                
                // Reinitialize Select2 if branch dropdown is also Select2
                if (branchDropdown.hasClass('select2')) {
                    branchDropdown.trigger('change.select2');
                }
            },
            error: function () {
                branchDropdown.html('<option value="">Failed to load branches</option>');
            }
        });
    } else {
        branchDropdown.html('<option value="">Select Branch</option>');
        branchDropdown.val('').trigger('change');
    }
    });
</script>



@endsection
