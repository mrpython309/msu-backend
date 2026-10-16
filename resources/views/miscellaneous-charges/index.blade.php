@extends('layouts/contentNavbarLayout')

@section('title', 'Miscellaneous Charges')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Miscellaneous Charges</h4>
    <div class="d-flex gap-2">
        @include('partials.master-export-buttons', ['excelRoute' => 'miscellaneous-charges.export', 'pdfRoute' => 'miscellaneous-charges.export-pdf', 'permission' => 'miscellaneous-charge:view'])
        @can('miscellaneous-charge:create')
        <a href="{{ route('miscellaneous-charges.create') }}" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i> Add Miscellaneous Charge
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

<x-master-filters action="{{ route('miscellaneous-charges.index') }}" placeholder="Description or short name" />

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Miscellaneous Charge List</h5>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Description</th>
                    <th>Short Name</th>
                    <th>Active</th>
                    <th class="text-center" style="width:100px">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($miscellaneousCharges as $charge)
                <tr>
                    <td>{{ ($miscellaneousCharges->currentPage() - 1) * $miscellaneousCharges->perPage() + $loop->iteration }}</td>
                    <td>{{ $charge->charge_name }}</td>
                    <td>{{ $charge->code }}</td>
                    <td>
                        @can('miscellaneous-charge:status')
                        @if($charge->status === 'active')
                                <span class="badge bg-label-success">Active</span>
                            @else
                                <span class="badge bg-label-danger">Inactive</span>
                            @endif
                        @else
                        <span class="badge bg-label-{{ $charge->status === 'active' ? 'success' : 'secondary' }}">{{ ucfirst($charge->status) }}</span>
                        @endcan
                    </td>
                    <td class="text-center">
                        <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                @can('miscellaneous-charge:edit')
                                <a class="dropdown-item" href="{{ route('miscellaneous-charges.edit', $charge) }}">
                                    <i class="bx bx-edit-alt me-1"></i> Edit
                                </a>
                                @endcan
                                @can('miscellaneous-charge:delete')
                                <form action="{{ route('miscellaneous-charges.destroy', $charge) }}" method="POST" id="delete-form-{{ $charge->id }}">
                                    @csrf @method('DELETE')
                                    <button type="button" class="dropdown-item text-danger" onclick="confirmDelete({{ $charge->id }})">
                                        <i class="bx bx-trash me-1"></i> Delete
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center py-4">No miscellaneous charges found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($miscellaneousCharges->hasPages())
    <div class="card-footer">{{ $miscellaneousCharges->links() }}</div>
    @endif
</div>
@endsection

@section('page-script')
<script>
function confirmDelete(id) {
    Swal.fire({ title: 'Are you sure?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Yes, delete it!' })
        .then(r => { if (r.isConfirmed) document.getElementById('delete-form-' + id).submit(); });
}
function updateMiscChargeStatus(el) {
    $.ajax({
        url: "{{ route('miscellaneous-charges.status') }}", type: "PUT",
        data: { _token: "{{ csrf_token() }}", id: el.getAttribute('data-id'), status: el.checked ? 'active' : 'inactive' },
        error: function() { el.checked = !el.checked; }
    });
}
</script>
@endsection
