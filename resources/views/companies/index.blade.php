@extends('layouts/contentNavbarLayout')

@section('title', 'Companies')

@section('vendor-style')
    {{-- Page CSS plugins --}}
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}">
@endsection

@section('page-style')
    {{-- Page CSS helpers --}}
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}">
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

        /* Full width form containers */
        .form-container {
            background: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1.5rem;
            border: 1px solid #e3e6f0;
            margin-bottom: 1.5rem;
        }
        .form-label-required::after {
            content: " *";
            color: #dc3545;
        }
    </style>
@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
      <div>
        <h4 class="mb-0">Companies</h4>
      </div>
    </div>

    {{-- Search & Filter Container - Full Width --}}
    <div class="card mb-4">
        <div class="card-header pb-0 mb-5">
            <h5 class="mb-0">Quick Filters</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('companies.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Name, code, city, or contact">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Company Name</label>
                    <input type="text" class="form-control" name="name" value="{{ request('name') }}" placeholder="Enter company name">
                </div>
                <div class="col-md-2">
                    <label class="form-label">City</label>
                    <input type="text" class="form-control" name="city" value="{{ request('city') }}" placeholder="e.g., Chennai">
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
                    <label class="form-label">Per Page</label>
                    <select name="per_page" class="form-select" onchange="this.form.submit()">
                        @foreach([10, 25, 50, 100] as $size)
                            <option value="{{ $size }}" @selected((string) request('per_page', 10) === (string) $size)>{{ $size }}</option>
                        @endforeach
                        <option value="all" @selected(request('per_page') === 'all')>All</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary me-2 w-50">
                        <i class="bx bx-search me-1"></i> Filter
                    </button>
                    <a href="{{ route('companies.index') }}" class="btn btn-outline-secondary w-50">
                        <i class="bx bx-refresh me-1"></i> Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Basic DataTable --}}
    <div class="card h-100">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Companies List</h5>
            </div>
            <div class="d-flex gap-2">
                @can('companies:view')
                <a href="{{ route('companies.export', request()->query()) }}" class="btn btn-outline-success btn-sm">
                    <i class="bx bx-export me-1"></i> Excel
                </a>
                <a href="{{ route('companies.export-pdf', request()->query()) }}" class="btn btn-outline-danger btn-sm">
                    <i class="bx bxs-file-pdf me-1"></i> PDF
                </a>
                @endcan
                @can('companies:create')
                <a href="{{ route('companies.create') }}" class="btn btn-primary btn-sm">
                    <i class="bx bx-plus me-1"></i> Add Company
                </a>
                @endcan
            </div>
        </div>
        <div class="table-responsive">
            <table class="table card-table table-striped table-hover" id="companiesTable">
                <thead class="table-light">
                    <tr>
                        <th class="text-nowrap">#</th>
                        <th>Name</th>
                        <th>Code</th>
                        <th>City</th>
                        <th>Contact</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($companies as $company)
                        <tr>
                            <td class="text-nowrap text-muted">
                                {{ ($companies->currentPage() - 1) * $companies->perPage() + $loop->iteration }}
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-2">
                                        <div class="avatar-initial bg-primary rounded-circle">
                                            {{ strtoupper(substr($company->name, 0, 2)) }}
                                        </div>
                                    </div>
                                    {{ $company->name }}
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-label-primary fs-12px">{{ $company->code }}</span>
                            </td>
                            <td>{{ $company->city ?? '-' }}</td>
                            <td>
                                <div class="d-flex align-items-center text-muted">
                                    <i class="bx bx-phone me-1"></i>
                                    {{ $company->contact_number ?? '-' }}
                                </div>
                            </td>
                            <td>
                                @if($company->status === 'active')
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
                                        <li>
                                            <a class="dropdown-item" href="{{ route('companies.show', $company) }}">
                                                <i class="bx bx-show me-1"></i> View
                                            </a>
                                        </li>
                                         @can('companies:edit')
                                        <li>
                                            <a class="dropdown-item" href="{{ route('companies.edit', $company) }}">
                                                <i class="bx bx-edit me-1"></i> Edit
                                            </a>
                                        </li>
                                        @endcan
                                         @can('companies:delete')
                                        <li>
                                            <form method="POST" action="{{ route('companies.destroy', $company) }}" class="d-inline delete-form">
                                                @csrf
                                                @method('DELETE')
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
                            <td colspan="7" class="text-center py-5">
                                <i class="bx bx-buildings fs-1 text-muted mb-3"></i>
                                <p class="mb-0 text-muted">No companies found</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            @if($companies->hasPages())
                <div class="text-muted">
                    Showing {{ $companies->firstItem() ?? 0 }} to {{ $companies->lastItem() ?? 0 }} of {{ $companies->total() }} entries
                </div>
                <div class="d-flex justify-content-end">
                    {{ $companies->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

@section('vendor-script')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
@endsection

@section('page-script')
    <script>

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
            $('#companiesTable').DataTable({
                paging: false,
                searching: false,
                info: false,
                lengthChange: false,
                responsive: true,
                dom: 'rt',
                order: [[0, 'asc']],
                columnDefs: [
                    { orderable: false, targets: -1 }
                ]
            });
        });
    </script>
@endsection
