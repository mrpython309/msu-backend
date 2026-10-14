@extends('layouts/contentNavbarLayout')

@section('title', 'App Settings')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
    App Settings
</h4>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0">App Settings</h5>
               
            </div>

            <div class="card-body">
                <form method="POST" action="{{ route('settings.store') }}">
                    @csrf

                    <div class="row">

                         <div class="col-md-6 mb-3">
                        <label class="form-label">Company <span class="text-danger">*</span></label>
                        <select id="warehouse_company_id" name="warehouse_company_id"
                            class="form-select @error('warehouse_company_id') is-invalid @enderror" required>
                            <option value="">Select Company</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ old('warehouse_company_id',(isset($settings['warehouse_company_id']) ? $settings['warehouse_company_id'] : '')) == $company->id ? 'selected' : '' }}>{{ $company->name }}</option>
                            @endforeach
                        </select>
                        @error('warehouse_company_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                        <div class="col-md-6 mb-3">
                        <label class="form-label">Warehouse <span class="text-danger">*</span></label>
                        <select id="warehouse_id" name="warehouse_id"
                            class="form-select @error('warehouse_id') is-invalid @enderror" required>
                            <option value="">Select Branch</option>
                            @foreach($branches as $branch)
                                <option value="{{ $branch->id }}" {{ old('warehouse_id',(isset($settings['warehouse_id']) ? $settings['warehouse_id'] : '')) == $branch->id ? 'selected' : '' }}>{{ $branch->name }}</option>
                            @endforeach
                        </select>
                        @error('warehouse_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                     <div class="col-md-6 mb-3">
                        <label class="form-label">Warehouse Physical Location<span class="text-danger">*</span></label>
                        <select id="warehouse_location" name="warehouse_location"
                            class="form-select @error('warehouse_location') is-invalid @enderror" required>
                            <option value="">Select Physical Location</option> 
                            @foreach ($branchLocations as $branchlocation)
                                <option value="{{$branchlocation->id}}" {{ old('warehouse_location',(isset($settings['warehouse_location']) ? $settings['warehouse_location'] : '')) == $branchlocation->id ? 'selected' : '' }}>{{ $branchlocation->location_name }}</option>
                            @endforeach                           
                        </select>
                        @error('warehouse_location')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                       


                        <!-- Department Name -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">
                               Pagination Size <span class="text-danger">*</span>
                            </label>
                            <input
                                type="number"
                                class="form-control @error('pagination_size') is-invalid @enderror"
                                name="pagination_size"
                                value="{{ old('pagination_size', (isset($settings['pagination_size']) ? $settings['pagination_size'] : '')) }}"
                                placeholder="Enter Records per page"
                                
                            >
                            @error('pagination_size')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                         
                    </div>

                    <div class="pt-3">
                        <button type="submit" class="btn btn-primary">
                          Update
                        </button>
                       
                    </div>

                    
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')

<script>

     const OLD_BRANCH = "{{ old('warehouse_id', (isset($settings['warehouse_id']) ? $settings['warehouse_id'] : '')) }}";
    const OLD_LOCATION = "{{ old('warehouse_location', (isset($settings['warehouse_location']) ? $settings['warehouse_location'] : '')) }}";

    function loadBranches(companyId, selectedId = null) {
            const branchDropdown = $('#warehouse_id');
            branchDropdown.html('<option>Loading...</option>');

            if (!companyId) {
                branchDropdown.html('<option value="">Select Warehouse</option>');
                return;
            }

            $.get(
                "{{ route('ajax.getbranch.bycompany', ':id') }}".replace(':id', companyId),
                function (res) {
                    branchDropdown.empty().append('<option value="">Select Warehouse</option>');

                    if (res.data && res.data.length > 0) {
                        $.each(res.data, function (_, value) {
                            let selected = selectedId == value.id ? 'selected' : '';
                            branchDropdown.append(
                                `<option value="${value.id}" ${selected}>${value.name}</option>`
                            );
                        });
                    } else {
                        branchDropdown.append('<option value="">No Warehouse found</option>');
                    }
                }
            ).fail(function () {
                branchDropdown.html('<option value="">Failed to load Warehouse</option>');
            });
        }

        $('#warehouse_company_id').on('change', function () {
            loadBranches(this.value);
        });

  function loadLocations(branchId, selectedId = null) {
        const locationSelect = $('#warehouse_location');
        locationSelect.html('<option>Loading...</option>');

        if (!branchId) {
            locationSelect.html('<option value="">Select Physical Location</option>');
            return;
        }

        $.get("{{ route('ajax.getlocation.bybranch', ':id') }}".replace(':id', branchId), function (res) {
            locationSelect.empty().append('<option value="">Select Physical Location</option>');

            $.each(res.data, function (_, item) {
                let selected = selectedId == item.id ? 'selected' : '';
                    locationSelect.append(
                        `<option value="${item.id}" ${selected}>${item.location_name}</option>`
                    );
                });
            });
        }

        $('#warehouse_id').on('change', function () {
            loadLocations(this.value);
        });


        $(document).ready(function () {   

             if ($('#company_id').val()) {
        loadBranches($('#company_id').val(), OLD_BRANCH);
    }

     if (OLD_BRANCH) {
        loadLocations(OLD_BRANCH, OLD_LOCATION);
    }

        });

</script>

@endsection
