@extends('layouts/contentNavbarLayout')

@section('title', 'Work Invoices')

@section('content')
@php
    $filterQuery = request()->except('page');
@endphp
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Work Invoices</h4>
    <div class="d-flex gap-2">
        <!-- Export/PDF placeholder if needed -->
    </div>
</div>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('msu-work-invoices.index') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Vendor</label>
                <select name="vendor_id" class="form-select">
                    <option value="">Select Vendor</option>
                    @foreach($vendors as $vendor)
                        <option value="{{ $vendor->id }}" {{ (string) request('vendor_id') === (string) $vendor->id ? 'selected' : '' }}>
                            {{ $vendor->company_code ? $vendor->company_code.' - ' : '' }}{{ $vendor->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">WI Number</label>
                <input type="text" name="wi_number" class="form-control" value="{{ request('wi_number') }}" placeholder="Search WI number">
            </div>
            <div class="col-md-1">
                <label class="form-label">Per Page</label>
                <select name="per_page" class="form-select" onchange="this.form.submit()">
                    <option value="10" {{ request('per_page', $perPage ?? 10) == 10 ? 'selected' : '' }}>10</option>
                    <option value="15" {{ request('per_page', $perPage ?? 10) == 15 ? 'selected' : '' }}>15</option>
                    <option value="25" {{ request('per_page', $perPage ?? 10) == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page', $perPage ?? 10) == 50 ? 'selected' : '' }}>50</option>
                    <option value="1000000" {{ request('per_page', $perPage ?? 10) == 1000000 ? 'selected' : '' }}>All</option>
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary me-1">Filter</button>
                <a href="{{ route('msu-work-invoices.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive text-nowrap">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>#</th>
                    <th>WI Number</th>
                    <th>Invoice Date</th>
                    <th>WO Number</th>
                    <th>Vendor</th>
                    <th>Invoice No</th>
                    <th class="text-end">Items</th>
                    <th>Status</th>
                    <th class="text-center">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($workInvoices as $wi)
                <tr>
                    <td>{{ ($workInvoices->currentPage() - 1) * $workInvoices->perPage() + $loop->iteration }}</td>
                    <td><strong>{{ $wi->wi_number }}</strong></td>
                    <td>{{ $wi->invoice_date?->format('d-m-Y') }}</td>
                    <td>{{ $wi->workOrder?->wo_number ?? '--' }}</td>
                    <td>{{ $wi->vendor?->name ?? '--' }}</td>
                    <td>{{ $wi->invoice_number ?: '--' }}</td>
                    <td class="text-end">{{ $wi->items_count }}</td>
                    <td><span class="badge bg-label-success">Passed</span></td>
                    <td class="text-center">
                        <div class="dropdown">
                            <button type="button" class="btn btn-sm btn-icon btn-outline-secondary dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end">
                                @can('msu-work-invoice:view')
                                <li><a class="dropdown-item" href="{{ route('msu-work-invoices.show', $wi) }}"><i class="bx bx-show me-1"></i> View</a></li>
                                @endcan
                                @can('msu-work-invoice:delete')
                                <li>
                                    <form action="{{ route('msu-work-invoices.destroy', $wi) }}" method="POST" id="delete-wi-{{ $wi->id }}">
                                        @csrf @method('DELETE')
                                        <button type="button" class="dropdown-item text-danger" onclick="confirmDeleteWI({{ $wi->id }})">
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
                <tr><td colspan="9" class="text-center py-4 text-muted">No Work Invoices found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($workInvoices->hasPages())
    <div class="card-footer">{{ $workInvoices->links() }}</div>
    @endif
</div>
@endsection

@section('page-script')
<script>
function confirmDeleteWI(id) {
    Swal.fire({ title: 'Delete this Invoice?', text: 'WO received quantities will be reverted.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Yes, delete' })
        .then(r => { if (r.isConfirmed) document.getElementById('delete-wi-' + id).submit(); });
}
</script>
@endsection
