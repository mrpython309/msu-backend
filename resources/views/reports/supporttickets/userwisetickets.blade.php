@extends('layouts/contentNavbarLayout')

@section('title', 'User Wise Support Tickets')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}">
@endsection
 

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h4 class="mb-0">User Wise Support Tickets</h4>
      </div>
    </div>

    {{-- Search & Filter Container --}}
    <div class="card mb-4">
        <div class="card-header border-bottom">
            <h5 class="mb-0">Filter</h5>
        </div>
        <div class="card-body pt-2">
            <form method="POST" action="{{ route('reports.userwisesupportticket') }}" class="row g-3">
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
                    <label class="form-label">Users</label>
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

                
                


                
               
                
                @include('reports.partials.filter-actions', ['clearRoute' => route('reports.userwisesupportticket')])
            </form>
        </div>
    </div>


    <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">User Wise Support Tickets</h5>
            </div>
             
        </div>


    
        <div class="table-responsive">
        <table class="table card-table" id="ticketTable">
            <thead>
                <tr>
                    <th class="text-nowrap">#</th>
                    <th>Emp Code </th>
                    <th>Name</th>
                    <th>Designation</th>
                    <th>Department </th>
                    <th>Company </th>
                    <th>Branch</th>
                    <th><span class="badge bg-label-info">Accepted</span></th>
                    <th><span class="badge bg-label-warning">WIP </span></th>
                    <th><span class="badge bg-label-primary">On hold</span></th>
                    <th><span class="badge bg-label-success">Completed</span></th>
                    <th><span class="badge bg-label-secondary">Closed</span></th> 
                                     <th>Total</th> 
                </tr>
            </thead>

            <tbody>
                @forelse($users as $user)
                <tr>
                   <td class="text-nowrap text-muted">{{ $loop->iteration }}</td>
                   <td>{{$user->emp_code}}</td>
                   <td>{{$user->name}}</td>
                   <td>{{$user->designation->designation_name ?? '--'}}</td>
                   <td>
                    @php
                        $departments = $user->departments->pluck('department_name');
                        $visible = $departments->take(4);
                        $remaining = $departments->slice(4);
                    @endphp

                    @if($departments->count())
                        {{ $visible->implode(', ') }}

                        @if($remaining->count())
                            <a href="#" data-bs-toggle="modal" data-bs-target="#deptModal_{{ $user->id }}">
                                +{{ $remaining->count() }} more
                            </a>
                        @endif
                    @else
                        -
                    @endif

                    @include('reports.partials.department')
                   </td>
                   <td>{{$user->companies->pluck('name')->implode(', ') ?: '-'}}</td>
                   <td>
                    @php
                        $branches = $user->branches->pluck('name');
                        $visibleBranches = $branches->take(2);
                        $remainingBranches = $branches->slice(2);
                    @endphp

                    @if($branches->count())
                        {{ $visibleBranches->implode(', ') }}

                        @if($remainingBranches->count())
                            <a href="#" data-bs-toggle="modal" data-bs-target="#branchModal_{{ $user->id }}">
                                +{{ $remainingBranches->count() }} more
                            </a>
                        @endif
                    @else
                        -
                    @endif

                    @include('reports.partials.branch')

                   </td>
                   <td align="center">{{$user->accepted_count ?? 0 }}</td>  
                   <td align="center">{{$user->work_in_progress_count ?? 0 }}</td>  
                   <td align="center">{{$user->hold_count ?? 0}}</td>   
                   <td align="center">{{$user->completed_count ?? 0}}</td>   
                   <td align="center">{{$user->closed_count ?? 0}}</td>   
                   <td align="center">{{$user->total_count ?? 0}}</td>                      
                </tr>
                @empty
                <tr>
                    <td colspan="12" class="text-center py-5 text-muted">
                        No Users found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

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
        });



function loadEmployees(branchId, selectedUserId = null, selectedCollaboratorId = null) {
    const employeeDropdown = $('#user_id');
    const collaboratorDropdown = $('#collaborator_id');

    if (!branchId) {
        return;
    }

    employeeDropdown.html('<option value="">Loading...</option>');
    collaboratorDropdown.html('<option value="">Loading...</option>');

    $.getJSON(
        "{{ route('ajax.getusers.bybranch', ':id') }}".replace(':id', branchId),
        { for_filter: 1, department_id: $('#department_id').val() || '' }
    )
    .done(res => {
        employeeDropdown.empty().append('<option value="">All Users</option>');
        collaboratorDropdown.empty().append('<option value="">All Collaborators</option>');

        if (res.data && res.data.length > 0) {
            $.each(res.data, function (_, user) {
                let userSelected = selectedUserId == user.id ? 'selected' : '';
                let collaboratorSelected = selectedCollaboratorId == user.id ? 'selected' : '';
                employeeDropdown.append(
                    `<option value="${user.id}" ${userSelected}>${user.name}</option>`
                );
                collaboratorDropdown.append(
                    `<option value="${user.id}" ${collaboratorSelected}>${user.name}</option>`
                );
            });
        }
        employeeDropdown.trigger('change.select2');
        collaboratorDropdown.trigger('change.select2');
    })
    .fail(() => {
        employeeDropdown.html('<option value="">Failed to load users</option>');
        collaboratorDropdown.html('<option value="">Failed to load collaborators</option>');
    });
}

        $('#branch_id').on('change', function () {
            loadEmployees(this.value);
        });

        $('#department_id').on('change', function () {
            if ($('#branch_id').val()) {
                loadEmployees($('#branch_id').val(), "{{ request('user_id') }}", "{{ request('collaborator_id') }}");
            }
        });

        @if(request('branch_id'))
            loadEmployees("{{ request('branch_id') }}", "{{ request('user_id') }}", "{{ request('collaborator_id') }}");
        @endif



</script>

@endsection
 
