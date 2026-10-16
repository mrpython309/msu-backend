@extends('layouts/contentNavbarLayout')

@section('title', 'All Support Ticket Reports')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}">
@endsection
 

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h4 class="mb-0">All Support Ticket Reports</h4>
      </div>
    </div>

    {{-- Search & Filter Container --}}
    <div class="card mb-4">
        <div class="card-header border-bottom">
            <h5 class="mb-0">Filter</h5>
        </div>
        <div class="card-body pt-2">
            <form method="POST" action="{{ route('reports.assetwisesupportticket') }}" class="row g-3">
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
                    <label class="form-label">Asset</label>
                    <select class="form-select select2" name="asset_id" id="asset_id">
                        <option value="">All Assets</option>
                        @foreach($assets as $asset)
                            <option value="{{ $asset->id }}" {{ request('asset_id') == $asset->id ? 'selected' : '' }}>
                                {{ $asset->asset_name.' | '.$asset->asset_code }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Created By</label>
                    <select class="form-select select2" name="user_id" id="user_id">
                        <option value="">All Reporters</option>
                        @foreach($filterUsers as $filterUser)
                            <option value="{{ $filterUser->id }}" {{ request('user_id') == $filterUser->id ? 'selected' : '' }}>
                                {{ $filterUser->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                @include('reports.partials.collaborator-filter', ['collaboratorSelect2' => true])

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
                    <label class="form-label">Priority</label>
                    <select class="form-select" name="priority">
                        <option value="">All Priorities</option>
                       @foreach ($priorities as $priority)
        <option value="{{ $priority->value }}" {{ request('priority') == $priority->value ? 'selected' : '' }}>
            {{ $priority->label() }}
        </option>
    @endforeach
                    </select>
                </div>

                


                
               
                
                @include('reports.partials.filter-actions', ['clearRoute' => route('reports.assetwisesupportticket')])
            </form>
        </div>
    </div>


    <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">All Support Tickets List</h5>
            </div>
             
        </div>


     @include('reports.partials.tickets',['tickets'=>$tickets,'tableColumns'=>$tableColumns])

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
                branchDropdown.html('<option value="">Select Branch</option>');
                return;
            }

            $.get(
                "{{ route('ajax.getbranch.bycompany', ':id') }}".replace(':id', companyId),
                function (res) {
                    branchDropdown.empty().append('<option value="">Select Branch</option>');

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
            $('#asset_id').html('<option value="">All Assets</option>');
        });

        function loadUsers(branchId, selectedUserId = null, selectedCollaboratorId = null) {
            const userDropdown = $('#user_id');
            const collaboratorDropdown = $('#collaborator_id');

            if (!branchId) {
                return;
            }

            userDropdown.html('<option value="">Loading...</option>');
            collaboratorDropdown.html('<option value="">Loading...</option>');

            $.get(
                "{{ route('ajax.getusers.bybranch', ':id') }}".replace(':id', branchId),
                { for_filter: 1 },
                function (res) {
                    userDropdown.empty().append('<option value="">All Users</option>');
                    collaboratorDropdown.empty().append('<option value="">All Collaborators</option>');

                    if (res.data && res.data.length > 0) {
                        $.each(res.data, function (_, value) {
                            let userSelected = selectedUserId == value.id ? 'selected' : '';
                            let collaboratorSelected = selectedCollaboratorId == value.id ? 'selected' : '';
                            userDropdown.append(
                                `<option value="${value.id}" ${userSelected}>${value.name}</option>`
                            );
                            collaboratorDropdown.append(
                                `<option value="${value.id}" ${collaboratorSelected}>${value.name}</option>`
                            );
                        });
                    }
                    userDropdown.trigger('change.select2');
                    collaboratorDropdown.trigger('change.select2');
                }
            ).fail(function () {
                userDropdown.html('<option value="">Failed to load users</option>');
                collaboratorDropdown.html('<option value="">Failed to load collaborators</option>');
            });
        }

        function loadAssets(branchId, selectedId = null) {
            const assetDropdown = $('#asset_id');
            assetDropdown.html('<option>Loading...</option>');

            if (!branchId) {
                assetDropdown.html('<option value="">Select Asset</option>');
                return;
            }

            $.get(
                "{{ route('ajax.getasset.bybranch', ':id') }}".replace(':id', branchId),
                function (res) {
                    assetDropdown.empty().append('<option value="">Select Asset</option>');

                    if (res.data && res.data.length > 0) {
                        $.each(res.data, function (_, value) {
                            let selected = selectedId == value.id ? 'selected' : '';
                            assetDropdown.append(
                                `<option value="${value.id}" ${selected}>${value.asset_name} | ${value.asset_code}</option>`
                            );
                        });
                    } else {
                        assetDropdown.append('<option value="">No assets found</option>');
                    }
                }
            ).fail(function () {
                assetDropdown.html('<option value="">Failed to load assets</option>');            
            });
        }

        $('#branch_id').on('change', function () {
            const branchId = this.value;
            loadAssets(branchId);
            loadUsers(branchId);
        });

        @if(request('branch_id'))
            loadUsers("{{ request('branch_id') }}", "{{ request('user_id') }}", "{{ request('collaborator_id') }}");
        @endif



</script>

@endsection
 
