@extends('layouts/supportTicketLayout')

@section('title', 'Support Tickets')

@section('vendor-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}">
@endsection

@section('page-style')
<style>
    .fs-12px { font-size: 0.75rem !important; }
</style>
@endsection

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <div class="d-flex align-items-center gap-3">
        <div class="layout-menu-toggle navbar-nav align-items-center d-xl-none">
            <a class="nav-item nav-link px-0" href="javascript:void(0)">
                <i class="icon-base bx bx-menu icon-md"></i>
            </a>
        </div>
        <h4 class="mb-0">Support Tickets</h4>
    </div>
    @include('support-tickets.partials.header-actions')
</div>

{{-- Filters --}}
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('support-ticket.index') }}" class="row g-3">

             <div class="col-md-3">
            <label class="form-label">Company</label>
            <select id="company_id" name="company_id"
                    class="form-select @error('company_id') is-invalid @enderror">
                <option value="">Select Company</option>
                @foreach($companies as $company)
                    <option value="{{ $company->id }}"
                         {{ request('company_id') == $company->id ? 'selected' : '' }}>
                        {{ $company->name }}
                    </option>
                @endforeach
            </select>
            
        </div>

<div class="col-md-3">
            <label class="form-label">Branch </label>
            <select id="branch_id" name="branch_id" 
                    class="form-select select2 @error('branch_id') is-invalid @enderror">
                <option value="">Select Branch</option>  
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}"
                         {{ request('branch_id') == $branch->id ? 'selected' : '' }}>
                        {{ $branch->name }}
                    </option>
                @endforeach               
            </select>
           
        </div>

            <div class="col-md-3">
                <label class="form-label">Department</label>
                <select class="form-select" name="department_id" id="department_id">
                    <option value="">All Department</option>
                    @foreach($department as $item)
                        <option value="{{ $item->id }}" {{ request('department_id')==$item->id?'selected':'' }}>
                            {{ $item->department_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Created By</label>
                <select class="form-select select2" name="created_by" id="created_by">
                    <option value="">All Users</option>
                    @foreach($creatorUsers as $creatorUser)
                        <option value="{{ $creatorUser->id }}" {{ request('created_by') == $creatorUser->id ? 'selected' : '' }}>
                            {{ $creatorUser->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Collaborators</label>
                <select class="form-select select2" name="collaborator_id" id="collaborator_id">
                    <option value="">All Collaborators</option>
                    @foreach($collaboratorUsers as $collaboratorUser)
                        <option value="{{ $collaboratorUser->id }}" {{ request('collaborator_id') == $collaboratorUser->id ? 'selected' : '' }}>
                            {{ $collaboratorUser->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Priority</label>
                <select class="form-select" name="priority">
                    <option value="">All</option>
                    <option value="low" @if(request('priority')=='low') selected @endif>Low</option>
                    <option value="normal" @if(request('priority')=='normal') selected @endif>Normal</option>
                    <option value="high" @if(request('priority')=='high') selected @endif>High</option>
                    <option value="urgent" @if(request('priority')=='urgent') selected @endif>Urgent</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select class="form-select" name="status">
                    <option value="">All</option>
                    <option value="open" {{ request('status') == 'open' ? 'selected' : '' }}>Open</option>
                    <option value="accepted" {{ request('status') == 'accepted' ? 'selected' : '' }}>Accepted</option>
                    <option value="work_in_progress" {{ request('status') == 'work_in_progress' ? 'selected' : '' }}>WIP</option>
                    <option value="hold" {{ request('status') == 'hold' ? 'selected' : '' }}>Hold</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed</option>
                    <option value="in_purchase" {{ request('status') == 'in_purchase' ? 'selected' : '' }}>In Purchase</option>
                    <option value="reopen" {{ request('status') == 'reopen' ? 'selected' : '' }}>Reopen</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">From Date</label>
                <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
            </div>

            <div class="col-md-3">
                <label class="form-label">To Date</label>
                <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
            </div>

            <div class="col-md-3">
                <label class="form-label">Subject</label>
                <div class="input-group">
                    <input type="text" name="subject" class="form-control" value="{{ request('subject') }}">
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-search"></i>
                    </button>
                </div>
            </div>

        </form>
    </div>
</div>

{{-- Tickets Table --}}
<div class="card h-100">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">Tickets List</h5>
        </div>
        <a href="{{ route('support-ticket.create') }}" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i> New Ticket
        </a>
    </div>

    <div class="table-responsive">
        <table class="table card-table" id="ticketTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>ID</th>
                    <th>Subject</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Branch</th>
                    <th>Department</th>
                    <th>Due Date</th>
                    <th>Created By</th>
                    <th>Collaborators</th>
                    <th>Actions</th>
                </tr>
            </thead>

            <tbody>
                @forelse($tickets as $ticket)
                <tr>
                    <td>{{ $tickets->firstItem() + $loop->index }}.</td>
                    <td>#{{ $ticket->id }}</td>

                    <td>
                        <strong>{{ $ticket->subject }}</strong>
                        <div class="text-muted fs-12px">
                            {{ Str::limit($ticket->description, 60) }}
                        </div>
                    </td>

                    <td>{!! $ticket->priority_label !!}</td>

                    <td>{!! $ticket->status_label !!}</td>

                    <td>{{ $ticket->branch?->name ?? '-' }}</td>

                    <td>{{ $ticket->department?->department_name ?? '-' }}</td>

                    <td>{{ optional($ticket->due_date)->format('d M Y') ?? '-' }}</td>

                    <td>{{ $ticket->creator?->name ?? '-' }}</td>

                    <td>
                        @forelse($ticket->activeAssignments as $collaborator)
                            <span class="badge bg-label-info">{{ $collaborator->user->name ?? '-' }}</span>
                        @empty
                            --
                        @endforelse
                    </td>

                    <td>
                        <div class="dropdown">
                            <button class="btn p-0 btn-sm" data-bs-toggle="dropdown">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>

                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="{{ route('support-ticket.show',$ticket->id) }}">
                                        <i class="bx bx-show me-1"></i> View
                                    </a>
                                </li>
                                @can('update', $ticket)
                                
                                <li>
                                    <a class="dropdown-item" href="{{ route('support-ticket.edit',$ticket->id) }}">
                                        <i class="bx bx-edit me-1"></i> Edit
                                    </a>
                                </li>
                             
                                @endcan

                                @can('delete', $ticket)
                                <li>
                                    <form method="POST" action="{{ route('support-ticket.destroy',$ticket->id) }}" class="delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button class="dropdown-item text-danger">
                                            <i class="bx bx-trash me-1"></i> Delete
                                        </button>
                                    </form>
                                </li>
                                @endcan
                            </ul>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="text-center py-5 text-muted">
                        No tickets found
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer d-flex justify-content-between align-items-center">
        @if($tickets->hasPages())
            <div class="text-muted">
                Showing {{ $tickets->firstItem() }} to {{ $tickets->lastItem() }} of {{ $tickets->total() }} tickets
            </div>
            <div>{{ $tickets->links() }}</div>
        @endif
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
                collaboratorDropdown.html('<option value="">All Collaborators</option>');
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
                }
            ).fail(function () {
                collaboratorDropdown.html('<option value="">Failed to load collaborators</option>');
            });
        }

        $('#company_id').on('change', function () {
            loadBranches(this.value);
            $('#collaborator_id').html('<option value="">All Collaborators</option>');
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

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            Swal.fire({
                title: 'Are you sure?',
                text: "This ticket will be permanently deleted!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});

$(function() {
    $('#ticketTable').DataTable({
        pageLength: 10,
        responsive: true,
        order: [[0, 'desc']],
        columnDefs: [{ orderable: false, targets: -1 }]
    });
});
</script>
@endsection
