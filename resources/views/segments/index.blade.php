@extends('layouts/contentNavbarLayout')

@section('title', 'Vehicle Segments')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
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
        .segment-badge {
            font-size: 0.875rem;
            padding: 0.375rem 0.75rem;
        }
    </style>
@endsection

@section('content')
    <h4 class="py-3 breadcrumb-wrapper mb-4">
        <span class="text-muted fw-light">Vehicle Segments</span>
    </h4>

    {{-- Stats Cards --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="mb-1"><i class="bx bx-check-circle text-success bx-sm"></i></div>
                    <h3 class="text-success mb-1">{{ $activeSegments }}</h3>
                    <small class="text-muted">Active Segments</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="mb-1"><i class="bx bx-x-circle text-danger bx-sm"></i></div>
                    <h3 class="text-danger mb-1">{{ $inactiveSegments }}</h3>
                    <small class="text-muted">Inactive Segments</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <div class="mb-1"><i class="bx bx-grid-alt bx-sm"></i></div>
                    <h3 class="mb-1">{{ $segments->total() }}</h3>
                    <small class="text-muted">Total Segments</small>
                </div>
            </div>
        </div>
    </div>

    {{-- DataTable --}}
    <div class="card h-100">
        <div class="card-header d-flex justify-content-between">
            <h5 class="mb-0">Segments List</h5>
            <a href="{{ route('segments.create') }}" class="btn btn-primary btn-sm">
                <i class="bx bx-plus me-1"></i> Add Segment
            </a>
        </div>
        <div class="table-responsive">
            <table class="table card-table table-striped table-hover" id="segmentsTable">
                <thead class="table-light">
                    <tr>
                        <th>Code</th>
                        <th>Name</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($segments as $segment)
                        <tr>
                            <td>
                                <span class="badge bg-label-primary segment-badge">{{ $segment->code }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm me-2">
                                        <div class="avatar-initial bg-warning rounded-circle">
                                            {{ strtoupper(substr($segment->name, 0, 2)) }}
                                        </div>
                                    </div>
                                    <div>
                                        <div class="fw-semibold">{{ $segment->name }}</div>
                                        <small class="text-muted">ID: {{ $segment->id }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($segment->status === 'active')
                                    <span class="badge bg-label-success">Active</span>
                                @else
                                    <span class="badge bg-label-danger">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="dropdown">
                                    <button class="btn p-0 btn-sm" data-bs-toggle="dropdown">
                                        <i class="bx bx-dots-vertical-rounded"></i>
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end">
                                        <li><a class="dropdown-item" href="{{ route('segments.show', $segment) }}">
                                            <i class="bx bx-show me-1"></i> View</a></li>
                                        <li><a class="dropdown-item" href="{{ route('segments.edit', $segment) }}">
                                            <i class="bx bx-edit me-1"></i> Edit</a></li>
                                        <li>
                                            <form method="POST" action="{{ route('segments.destroy', $segment) }}" class="d-inline delete-form">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="bx bx-trash me-2"></i>Delete
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center py-5">
                                <i class="bx bx-car bx-sm-1 text-muted mb-3"></i>
                                <p class="text-muted mb-0">No segments found</p>
                                <a href="{{ route('segments.create') }}" class="btn btn-primary btn-sm mt-2">Add First Segment</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $segments->links() }}
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
    $('#segmentsTable').DataTable({
        pageLength: 15,
        responsive: true,
        order: [[1, 'asc']],
        columnDefs: [{ orderable: false, targets: -1 }],
        language: { lengthMenu: "_MENU_ per page" }
    });
});
</script>
@endsection
