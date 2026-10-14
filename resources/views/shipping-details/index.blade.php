@extends('layouts/contentNavbarLayout')

@section('title', 'Shipping Details')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Shipping Details</h4>
    <div class="d-flex gap-2">
        @include('partials.master-export-buttons', ['excelRoute' => 'shipping-details.export', 'pdfRoute' => 'shipping-details.export-pdf', 'permission' => 'shipping-detail:view'])
        @can('shipping-detail:create')
        <a href="{{ route('shipping-details.create') }}" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i> Add Shipping Detail
        </a>
        @endcan
    </div>
</div>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

<x-master-filters action="{{ route('shipping-details.index') }}" placeholder="Name, GST, contact, or email" />

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Shipping Detail List</h5>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>S.No</th>
                    <th>Shipping Name</th>
                    <th>GST Number</th>
                    <th>Contact Person</th>
                    <th>Contact No</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th class="text-center" style="width:100px">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($shippingDetails as $detail)
                    <tr>
                        <td>{{ ($shippingDetails->currentPage() - 1) * $shippingDetails->perPage() + $loop->iteration }}</td>
                        <td>{{ $detail->shipping_name }}</td>
                        <td>{{ $detail->gst_number ?: '--' }}</td>
                        <td>{{ $detail->contact_person ?: '--' }}</td>
                        <td>{{ $detail->contact_no ?: '--' }}</td>
                        <td>{{ $detail->email ?: '--' }}</td>
                        <td>
                            @can('shipping-detail:status')
                            @if($detail->status === 'active')
                                <span class="badge bg-label-success">Active</span>
                            @else
                                <span class="badge bg-label-danger">Inactive</span>
                            @endif
                            @else
                            <span class="badge bg-{{ $detail->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($detail->status) }}</span>
                            @endcan
                        </td>
                        <td class="text-center">
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    @can('shipping-detail:edit')
                                    <a class="dropdown-item" href="{{ route('shipping-details.edit', $detail) }}">
                                        <i class="bx bx-edit-alt me-1"></i> Edit
                                    </a>
                                    @endcan
                                    @can('shipping-detail:delete')
                                    <form action="{{ route('shipping-details.destroy', $detail) }}" method="POST" id="delete-form-{{ $detail->id }}">
                                        @csrf @method('DELETE')
                                        <button type="button" class="dropdown-item text-danger" onclick="confirmDelete({{ $detail->id }})">
                                            <i class="bx bx-trash me-1"></i> Delete
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center py-4">No shipping details found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($shippingDetails->hasPages())
    <div class="card-footer">{{ $shippingDetails->links() }}</div>
    @endif
</div>
@endsection

@section('page-script')
<script>
function confirmDelete(id) {
    Swal.fire({
        title: 'Are you sure?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) document.getElementById('delete-form-' + id).submit();
    });
}
function updateShippingDetailStatus(el) {
    $.ajax({
        url: "{{ route('shipping-details.status') }}",
        type: "PUT",
        data: { _token: "{{ csrf_token() }}", id: el.getAttribute('data-id'), status: el.checked ? 'active' : 'inactive' },
        error: function () { el.checked = !el.checked; }
    });
}
</script>
@endsection
