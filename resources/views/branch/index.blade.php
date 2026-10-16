@extends('layouts/contentNavbarLayout')

@section('title', 'Branchs')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}">
@endsection

@section('page-style')
    <style>
        .avatar-initial {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
            color: white;
        }
        .fs-12px { font-size: 0.75rem !important; }
        .form-container {
            background: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1.5rem;
            border: 1px solid #e3e6f0;
            margin-bottom: 1.5rem;
        }
    </style>
@endsection

@section('content')

    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h4 class="mb-0">Branches</h4>
      </div>
    </div>

    {{-- Search & Filter Container --}}
    <div class="card mb-4">
        <div class="card-header pb-0 mb-5">
            <h5 class="mb-0">Quick Filters</h5>
        </div>
        <div class="card-body ">
            <form method="GET" action="{{ route('branch.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Branch Name</label>
                    <input type="text" class="form-control" name="name" value="{{ request('name') }}" placeholder="Enter Branch name">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Code</label>
                    <input type="text" class="form-control" name="code" value="{{ request('code') }}" placeholder="DL001">
                </div>
                <div class="col-md-2">
                    <label class="form-label">City</label>
                     <select class="form-select select2" name="city" id="city">
                        <option value="">All Cities</option>
                        @foreach($cities as $city)
                            <option value="{{ $city }}" {{ request('city') == $city ? 'selected' : '' }}>
                                {{ $city }}
                            </option>
                        @endforeach
                    </select>
                   
                </div>

                <div class="col-md-2">
                    <label class="form-label">Company</label>
                    <select class="form-select select2" name="company_id" id="company_id">
                        <option value="">All Companies</option>
                        @foreach($companies as $company)
                            <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                                {{ $company->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select class="form-select select2" name="status">
                        <option value="">All</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Name, code, city, contact">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Per Page</label>
                    <select name="per_page" class="form-select" onchange="this.form.submit()">
                        @foreach([10, 25, 50, 100] as $size)
                            <option value="{{ $size }}" @selected((string) request('per_page', 10) === (string) $size)>{{ $size }}</option>
                        @endforeach
                        <option value="all" @selected(request('per_page') === 'all')>All</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bx bx-search"></i>
                    </button>
                    <a href="{{ route('branch.index') }}" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    {{-- DataTable --}}
    <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Branches List</h5>
            </div>
            <div class="d-flex gap-2">
                @include('partials.master-export-buttons', ['excelRoute' => 'branch.export', 'pdfRoute' => 'branch.export-pdf', 'permission' => 'branch:view', 'btnClass' => 'btn-sm'])
                @can('branch:create')
                <a href="{{ route('branch.create') }}" class="btn btn-primary btn-sm">
                    <i class="bx bx-plus me-1"></i> Add Branch
                </a>
                @endcan
            </div>
        </div>
        <div class="table-responsive">
            <table class="table card-table table-striped table-hover" id="dealerLocationsTable">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Branch Name</th>
                        <th>Code</th>
                        <th>Company</th>
                        <th>City</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($branches as $branch)
                        <tr>
                            <td class="text-nowrap text-muted">
                                {{ ($branches->currentPage() - 1) * $branches->perPage() + $loop->iteration }}
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-2">
                                        <div class="avatar-initial bg-success rounded-circle">
                                            {{ strtoupper(substr($branch->name, 0, 2)) }}
                                        </div>
                                    </div>
                                    {{ $branch->name }}
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-label-info fs-12px">{{ $branch->code }}</span>
                            </td>
                            <td>
                                <span class="badge bg-label-primary fs-12px">{{ $branch->company->name ?? '-' }}</span>
                            </td>
                            <td>{{ $branch->city ?? '-' }}</td>
                            <td>
                                <div class="d-flex flex-column">
                                    @if($branch->contact_number)
                                        <small class="text-muted">
                                            <i class="bx bx-phone me-1"></i>{{ $branch->contact_number }}
                                        </small>
                                    @endif
                                    @if($branch->email)
                                        <small class="text-muted">{{ $branch->email }}</small>
                                    @endif
                                </div>
                            </td>
                            <td>
                                @if($branch->status === 'active')
                                    <span class="badge bg-label-success">Active</span>
                                @else
                                    <span class="badge bg-label-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn p-0 btn-sm" type="button" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="{{ route('branch.show', $branch) }}">
                                            <i class="bx bx-show me-1"></i> View</a></li>
                                         @can('branch:edit') <li><a class="dropdown-item" href="{{ route('branch.edit', $branch) }}">
                                            <i class="bx bx-edit me-1"></i> Edit</a></li> @endcan
                                              @can('branch:delete')
                                        <li>
                                            <form method="POST" action="{{ route('branch.destroy', $branch) }}" class="d-inline delete-form">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="bx bx-trash me-2"></i>Delete
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
                            <td colspan="8" class="text-center py-5">
                                <i class="bx bx-store-alt fs-1 text-muted mb-3"></i>
                                <p class="mb-0 text-muted">No Branches found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            @if($branches->hasPages())
                <div class="text-muted">Showing {{ $branches->firstItem() ?? 0 }} to {{ $branches->lastItem() ?? 0 }} of {{ $branches->total() }} Branches</div>
                <div>{{ $branches->links() }}</div>
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

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();

            Swal.fire({
                title: 'Are you sure?',
                text: "This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});

$(function() {
    $('#dealerLocationsTable').DataTable({
        paging: false,
        searching: false,
        info: false,
        lengthChange: false,
        responsive: true,
        dom: 'rt',
        order: [[0, 'asc']],
        columnDefs: [{ orderable: false, targets: -1 }]
    });
});
</script>
@endsection
