@extends('layouts/contentNavbarLayout')

@section('title', 'Units')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Units</h4>
    <div class="d-flex gap-2">
        @include('partials.master-export-buttons', ['excelRoute' => 'units.export', 'pdfRoute' => 'units.export-pdf', 'permission' => 'unit:view'])
        @can('unit:create')
        <a href="{{ route('units.create') }}" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i> Add Unit
        </a>
        @endcan
    </div>
</div>

<x-master-filters action="{{ route('units.index') }}" placeholder="Unit name" />

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">Unit List</h5>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th class="text-nowrap">#</th>
                    <th>Unit Name</th>
                    <th>Status</th>
                    <th class="text-center" style="width:100px">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($units as $unit)
                    <tr>
                        <td class="text-nowrap text-muted">
                            {{ ($units->currentPage() - 1) * $units->perPage() + $loop->iteration }}
                        </td>
                        <td>{{ $unit->unit_name }}</td>
                        <td>
                            @can('unit:status')
                            @if($unit->status === 'active')
                                <span class="badge bg-label-success">Active</span>
                            @else
                                <span class="badge bg-label-danger">Inactive</span>
                            @endif
                            @else
                            <span class="badge bg-{{ $unit->status === 'active' ? 'success' : 'secondary' }}">
                                {{ ucfirst($unit->status) }}
                            </span>
                            @endcan
                        </td>
                        <td class="text-center">
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    @can('unit:edit')
                                    <a class="dropdown-item" href="{{ route('units.edit', $unit->id) }}">
                                        <i class="bx bx-edit-alt me-1"></i> Edit
                                    </a>
                                    @endcan
                                    @can('unit:delete')
                                    <form action="{{ route('units.destroy', $unit->id) }}" method="POST" id="delete-form-{{ $unit->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="dropdown-item text-danger" onclick="confirmDelete({{ $unit->id }})">
                                            <i class="bx bx-trash me-1"></i> Delete
                                        </button>
                                    </form>
                                    @endcan
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center py-4">
                            <p class="mb-2">No units found.</p>
                            @can('unit:create')
                            <a href="{{ route('units.create') }}" class="btn btn-primary btn-sm">
                                <i class="bx bx-plus me-1"></i> Add Unit
                            </a>
                            @endcan
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center">
        @if($units->hasPages())
            <div class="text-muted">Showing {{ $units->firstItem() ?? 0 }} to {{ $units->lastItem() ?? 0 }} of {{ $units->total() }} Units</div>
            <div>{{ $units->links() }}</div>
        @endif
    </div>
</div>
@endsection

@section('page-script')
<script>
function confirmDelete(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form-' + id).submit();
        }
    });
}

function updateUnitStatus(el) {
    let unitId = el.getAttribute('data-id');
    let status = el.checked ? 'active' : 'inactive';

    $.ajax({
        url: "{{ route('units.status') }}",
        type: "PUT",
        data: {
            _token: "{{ csrf_token() }}",
            id: unitId,
            status: status
        },
        success: function (response) {
            if (!response.success) {
                el.checked = !el.checked;
            }
        },
        error: function () {
            el.checked = !el.checked;
        }
    });
}
</script>
@endsection
