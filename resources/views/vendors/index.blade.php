@extends('layouts/contentNavbarLayout')

@section('title', 'Vendors')

@section('vendor-style')
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}">
    <!-- Select2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
@endsection

@section('page-style')
    <style>
        .form-container {
            background: #f8f9fa;
            border-radius: 0.5rem;
            padding: 1.5rem;
            border: 1px solid #e3e6f0;
            margin-bottom: 1.5rem;
        }
        .fs-12px { font-size: 0.75rem !important; }
    </style>
@endsection

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Vendors</h4>

   @can('vendor:create')
    <a href="{{ route('vendor.create') }}" class="btn btn-primary btn-sm">
        <i class="bx bx-plus me-1"></i> Add Vendor
    </a>
    @endcan
</div>

{{-- Quick Filters --}}
<div class="card mb-4">
    <div class="card-header pb-0">
        <h5 class="mb-0">Quick Filters</h5>
    </div>
    <div class="card-body form-container pt-2">
        <form method="GET" action="{{ route('vendor.index') }}" class="row g-3">

            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Code, name, phone, GST, city">
            </div>

            <div class="col-md-3">
                <label class="form-label">Brand</label>
                <select class="form-select select2" name="brands" id="brands">
                    <option value="">All Brands</option>
                    @foreach($brands as $brand)
                        <option value="{{ $brand->id }}" @selected((string) request('brands') === (string) $brand->id)>{{ $brand->brand_name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select class="form-select select2" name="status">
                    <option value="">All Status</option>
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

            <div class="col-md-3">
                <label class="form-label">Vendor Name</label>
                <input type="text" class="form-control" name="name" value="{{ request('name') }}">
            </div>

            <div class="col-md-3">
                <label class="form-label">Phone</label>
                <input type="text" class="form-control" name="phone_number" value="{{ request('phone_number') }}">
            </div>

            <div class="col-md-3">
                <label class="form-label">Email</label>
                <input type="text" class="form-control" name="email" value="{{ request('email') }}">
            </div>

            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bx bx-search"></i>
                </button>
            </div>

        </form>
    </div>
</div>

{{-- Vendors DataTable --}}
<div class="card h-100">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">Vendors List</h5>
        </div>
        <div class="d-flex gap-2">
            @include('partials.master-export-buttons', ['excelRoute' => 'vendor.export', 'pdfRoute' => 'vendor.export-pdf', 'permission' => 'vendor:view', 'btnClass' => 'btn-sm'])
             @can('vendor:create')
            <a href="{{ route('vendor.create') }}" class="btn btn-primary btn-sm">
                <i class="bx bx-plus me-1"></i> Add Vendor
            </a>
            @endcan
        </div>
    </div>

    <div class="table-responsive">
        <table class="table card-table table-striped table-hover" id="vendorsTable">
            <thead class="table-light">
                <tr>
                    <th class="text-nowrap">#</th>
                    <th>Vendor Code</th>
                    <th>Vendor Name</th>
                    <th>Contact Person</th>
                    <th>Phone</th>
                    <th>Email</th>
                    <th>GST Number</th>
                    <th>Address</th>
                    <th>Status</th>
                    <th>City</th>
                    <th>State</th>
                    <th>Country</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($vendors as $vendor)
                <tr>
                    <td class="text-nowrap text-muted">
                        {{ ($vendors->currentPage() - 1) * $vendors->perPage() + $loop->iteration }}
                    </td>
                    <td><strong>{{ $vendor->company_code ?? '-' }}</strong></td>
                    <td>{{ $vendor->name }}</td>
                    <td>{{ $vendor->contact_person ?? '-' }}</td>
                    <td>{{ $vendor->phone_number }}</td>
                    <td>{{ $vendor->email ?? '-' }}</td>
                    <td>{{ $vendor->gst_number ?? '-' }}</td>
                    <td>{{ $vendor->address ?? '-' }}</td>
                    <td>
                        <span class="badge bg-label-{{ $vendor->status == 'active' ? 'success' : 'secondary' }}">
                            {{ ucfirst($vendor->status) }}
                        </span>
                    </td>
                    <td>{{ $vendor->city ?? '-' }}</td>
                    <td>{{ $vendor->state ?? '-' }}</td>
                    <td>{{ $vendor->country ?? '-' }}</td>
                    <td>
                        <div class="dropdown">
                            <button class="btn p-0 btn-sm" data-bs-toggle="dropdown">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                 <li><a class="dropdown-item" href="{{ route('vendor.show', $vendor->id) }}">
                                            <i class="bx bx-show me-1"></i> View</a></li>
                               @can('vendor:edit')  <li><a class="dropdown-item" href="{{ route('vendor.edit', $vendor->id) }}"><i class="bx bx-edit me-1"></i> Edit</a></li> @endcan
                                 @can('vendor:delete') <li>
                                    <form method="POST" action="{{ route('vendor.destroy', $vendor->id) }}" class="delete-form">
                                        @csrf
                                        @method('DELETE')
                                        <button class="dropdown-item text-danger"><i class="bx bx-trash me-1"></i> Delete</button>
                                    </form>
                                </li> @endcan
                            </ul>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="13" class="text-center py-5 text-muted">No vendors found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="card-footer d-flex justify-content-between align-items-center">
        @if($vendors->hasPages())
            <div class="text-muted">Showing {{ $vendors->firstItem() ?? 0 }} to {{ $vendors->lastItem() ?? 0 }} of {{ $vendors->total() }} Vendors</div>
            <div>{{ $vendors->links() }}</div>
        @endif
    </div>
</div>

@endsection

@section('vendor-script')
    <script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <!-- Select2 JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Delete confirmation
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
                if (result.isConfirmed) form.submit();
            });
        });
    });

    // Initialize DataTable
    $('#vendorsTable').DataTable({
        paging: false,
        searching: false,
        info: false,
        lengthChange: false,
        responsive: true,
        dom: 'rt',
        order: [[0, 'asc']],
        columnDefs: [{ orderable: false, targets: -1 }]
    });

    if (typeof $.fn.select2 === 'function' && !$('#brands').hasClass('select2-hidden-accessible')) {
        $('#brands').select2({
            placeholder: 'All Brands',
            allowClear: true,
            width: '100%'
        });
    }
});
</script>
@endsection
