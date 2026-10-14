@extends('layouts/contentNavbarLayout')

@section('title', 'Departmentwise Support Tickets')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}">
@endsection
 

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h4 class="mb-0">Departmentwise Support Tickets</h4>
      </div>
    </div>

    {{-- Search & Filter Container --}}
    <div class="card mb-4">
        <div class="card-header border-bottom">
            <h5 class="mb-0">Filter</h5>
        </div>
        <div class="card-body pt-2">
            <form method="POST" action="{{ route('reports.departmentwisesupportticket') }}" class="row g-3">
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
                    <label class="form-label">Department</label>
                    <select class="form-select" name="department_id" id="department_id">
                        <option value="">All Departments</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                                {{ $department->department_name }}
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

                @include('reports.partials.collaborator-filter', ['collaboratorSelect2' => true])

                @include('reports.partials.filter-actions', ['clearRoute' => route('reports.departmentwisesupportticket')])
            </form>
        </div>
    </div>


    <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Departmentwise Support Tickets List</h5>
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

        function loadCollaborators(branchId, selectedId = null) {
            const collaboratorDropdown = $('#collaborator_id');

            if (!branchId) {
                collaboratorDropdown.html('<option value="">All Collaborators</option>').trigger('change.select2');
                return;
            }

            collaboratorDropdown.html('<option value="">Loading...</option>');

            $.get(
                "{{ route('ajax.getusers.bybranch', ':id') }}".replace(':id', branchId),
                { for_filter: 1, department_id: $('#department_id').val() || '' },
                function (res) {
                    collaboratorDropdown.empty().append('<option value="">All Collaborators</option>');

                    if (res.data && res.data.length > 0) {
                        $.each(res.data, function (_, value) {
                            let selected = selectedId == value.id ? 'selected' : '';
                            collaboratorDropdown.append(
                                `<option value="${value.id}" ${selected}>${value.name}</option>`
                            );
                        });
                    }
                    collaboratorDropdown.trigger('change.select2');
                }
            ).fail(function () {
                collaboratorDropdown.html('<option value="">Failed to load collaborators</option>');
            });
        }

        $('#company_id').on('change', function () {
            loadBranches(this.value);
            $('#collaborator_id').html('<option value="">All Collaborators</option>').trigger('change.select2');
        });

        $('#branch_id').on('change', function () {
            loadCollaborators(this.value);
        });

        $('#department_id').on('change', function () {
            if ($('#branch_id').val()) {
                loadCollaborators($('#branch_id').val(), "{{ request('collaborator_id') }}");
            }
        });

        @if(request('branch_id'))
            loadCollaborators("{{ request('branch_id') }}", "{{ request('collaborator_id') }}");
        @endif

</script>

@endsection
 
