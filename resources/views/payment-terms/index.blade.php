@extends('layouts/contentNavbarLayout')

@section('title', 'Payment Terms')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Payment Terms</h4>
    <div class="d-flex gap-2">
        @include('partials.master-export-buttons', ['excelRoute' => 'payment-terms.export', 'pdfRoute' => 'payment-terms.export-pdf', 'permission' => 'payment-term:view'])
        @can('payment-term:create')
        <a href="{{ route('payment-terms.create') }}" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i> Add Payment Term
        </a>
        @endcan
    </div>
</div>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

<x-master-filters action="{{ route('payment-terms.index') }}" placeholder="Mode, description, or short name" />

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Payment Term List</h5>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Payment Mode</th>
                    <th>Payment Description</th>
                    <th>Short Name</th>
                    <th>Active</th>
                    <th class="text-center" style="width:100px">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($paymentTerms as $term)
                <tr>
                    <td>{{ ($paymentTerms->currentPage() - 1) * $paymentTerms->perPage() + $loop->iteration }}</td>
                    <td>{{ $term->payment_mode ?: '--' }}</td>
                    <td>{{ $term->term_name }}</td>
                    <td>{{ $term->code ?: '--' }}</td>
                    <td>
                        @can('payment-term:status')
                        @if($term->status === 'active')
                                <span class="badge bg-label-success">Active</span>
                            @else
                                <span class="badge bg-label-danger">Inactive</span>
                            @endif
                        @else
                        <span class="badge bg-label-{{ $term->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($term->status) }}</span>
                        @endcan
                    </td>
                    <td class="text-center">
                        <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                @can('payment-term:edit')
                                <a class="dropdown-item" href="{{ route('payment-terms.edit', $term) }}">
                                    <i class="bx bx-edit-alt me-1"></i> Edit
                                </a>
                                @endcan
                                @can('payment-term:delete')
                                <form action="{{ route('payment-terms.destroy', $term) }}" method="POST" id="delete-form-{{ $term->id }}">
                                    @csrf @method('DELETE')
                                    <button type="button" class="dropdown-item text-danger" onclick="confirmDelete({{ $term->id }})">
                                        <i class="bx bx-trash me-1"></i> Delete
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center py-4">No payment terms found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($paymentTerms->hasPages())
    <div class="card-footer">{{ $paymentTerms->links() }}</div>
    @endif
</div>
@endsection

@section('page-script')
<script>
function confirmDelete(id) {
    Swal.fire({ title: 'Are you sure?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Yes, delete it!' })
        .then(r => { if (r.isConfirmed) document.getElementById('delete-form-' + id).submit(); });
}
function updatePaymentTermStatus(el) {
    $.ajax({
        url: "{{ route('payment-terms.status') }}", type: "PUT",
        data: { _token: "{{ csrf_token() }}", id: el.getAttribute('data-id'), status: el.checked ? 'active' : 'inactive' },
        error: function() { el.checked = !el.checked; }
    });
}
</script>
@endsection
