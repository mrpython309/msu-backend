@extends('layouts/contentNavbarLayout')

@section('title', 'Branchwise Assets')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}">
@endsection
 

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h4 class="mb-0">Branchwise Assets</h4>
      </div>
    </div>

    {{-- Search & Filter Container --}}
    <div class="card mb-4">
        <div class="card-header border-bottom">
            <h5 class="mb-0">Filter</h5>
        </div>
        <div class="card-body pt-2">
            <form method="POST" action="{{ route('reports.branchwiseassets') }}" class="row g-3">
                  @csrf
                 <div class="col-md-3">
                    <label class="form-label">Company</label>
                    <select class="form-select" name="company_id" id="company_id">
                        <option value="">All Companies</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Branch</label>
                    <select class="form-select select2" name="branch_id" id="branch_id">
                        <option value="">All Branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}" {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                                {{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Vendor</label>
                    <select class="form-select select2" name="vendor_id">
                        <option value="">All Vendors</option>
                        @foreach($vendors as $vendorItem)
                            <option value="{{ $vendorItem->id }}" {{ request('vendor_id') == $vendorItem->id ? 'selected' : '' }}>
                                {{ $vendorItem->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Physical Location</label>
                    <select class="form-select select2" name="location_id" id="location_id">
                        <option value="">All Physical Locations</option>
                        @foreach($locations as $location)
                            <option value="{{ $location->id }}" {{ request('location_id') == $location->id ? 'selected' : '' }}>
                                {{ $location->location_name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                 <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status">
                        <option value="">All Status</option>
                        @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" {{ request('status') == $status->value ? 'selected' : '' }}>{{ $status->label() }}</option>
                        @endforeach
                         
                    </select>
                </div>

                 <div class="col-md-3">
                    <label class="form-label">Conditions</label>
                    <select class="form-select" name="condition">
                        <option value="">All Conditions</option>
                       @foreach ($conditions as $condition)
        <option value="{{ $condition->value }}" {{ request('condition') == $condition->value ? 'selected' : '' }}>
            {{ $condition->label() }}
        </option>
    @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Operational Status</label>
                    <select class="form-select" name="operational_status">
                        <option value="">All Operational Status</option>
                       @foreach ($operations_status as $operationStatus)
        <option value="{{ $operationStatus->value }}" {{ request('operational_status') == $operationStatus->value ? 'selected' : '' }}>
            {{ $operationStatus->label() }}
        </option>
    @endforeach
                    </select>
                </div>


                
               
                
                @include('reports.partials.filter-actions', ['clearRoute' => route('reports.branchwiseassets')])
            </form>
        </div>
    </div>


    <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Branchwise Assets List</h5>
            </div>
             
        </div>


     @include('reports.partials.assets',['assets'=>$assets,'tableColumns'=>$tableColumns])

    </div>

    
    </div>
@endsection

@section('vendor-script')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
@endsection

@section('page-script')

<script>

    $('.select2').select2({
    placeholder: "Select",
    allowClear: true,
    closeOnSelect: true, 
    width: '100%'
});

  function loadBranches(companyId, selectedId = null) {
            const branchDropdown = $('#branch_id');
            branchDropdown.html('<option>Loading...</option>');

            if (!companyId) {
                branchDropdown.html('<option value="">All Branches</option>');
                return;
            }

            $.get(
                "{{ route('ajax.getbranch.bycompany', ':id') }}".replace(':id', companyId),
                function (res) {
                    branchDropdown.empty().append('<option value="">All Branches</option>');

                    if (res.data && res.data.length > 0) {
                        $.each(res.data, function (_, value) {
                            let selected = selectedId == value.id ? 'selected' : '';
                            branchDropdown.append(
                                `<option value="${value.id}" ${selected}>${value.name}</option>`
                            );
                        });
                    } else {
                        branchDropdown.append('<option value="">No branches found</option>');
                    }
                }
            ).fail(function () {
                branchDropdown.html('<option value="">Failed to load branches</option>');
            });
        }

        $('#company_id').on('change', function () {
            loadBranches(this.value);
            $('#location_id').html('<option value="">All Physical Locations</option>');
        });

        function loadLocations(branchId, selectedId = null) {
            const locationDropdown = $('#location_id');
            locationDropdown.html('<option>Loading...</option>');

            if (!branchId) {
                locationDropdown.html('<option value="">All Physical Locations</option>');
                return;
            }

            $.get(
                "{{ route('ajax.getlocation.bybranch', ':id') }}".replace(':id', branchId),
                function (res) {
                    locationDropdown.empty().append('<option value="">All Physical Locations</option>');

                    if (res.data && res.data.length > 0) {
                        $.each(res.data, function (_, value) {
                            let selected = selectedId == value.id ? 'selected' : '';
                            locationDropdown.append(
                                `<option value="${value.id}" ${selected}>${value.location_name}</option>`
                            );
                        });
                    }
                }
            ).fail(function () {
                locationDropdown.html('<option value="">Failed to load areas</option>');
            });
        }

        $('#branch_id').on('change', function () {
            loadLocations(this.value);
        });


</script>

@endsection
 
