@extends('layouts/contentNavbarLayout')

@section('title', 'Designations')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
  Designations
</h4>

<x-master-filters action="{{ route('designation.index') }}" placeholder="Designation" />

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">Designation List</h5>
        </div>
        <div class="d-flex gap-2">
            @include('partials.master-export-buttons', ['excelRoute' => 'designation.export', 'pdfRoute' => 'designation.export-pdf', 'permission' => 'designation:view'])
            @can('designation:create')
            <a href="{{ route('designation.create') }}" class="btn btn-primary">
                Add Designation
            </a>
            @endcan
        </div>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th class="text-nowrap">#</th>
                    <th>Designation</th>
                    <th>Status</th>
                    <th width="180">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($designations as $designation)
                    <tr>
                        <td class="text-nowrap text-muted">
                            {{ ($designations->currentPage() - 1) * $designations->perPage() + $loop->iteration }}
                        </td>
                        <td>{{ $designation->designation_name }}</td>
                        <td>
                            @if($designation->status === 'active')
                                <span class="badge bg-label-success">Active</span>
                            @else
                                <span class="badge bg-label-danger">Inactive</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">

                             @can('designation:edit')
                            <a href="{{ route('designation.edit', $designation->id) }}"
                               class="dropdown-item"><i class="bx bx-edit-alt me-1"></i> Edit</a>
                            @endcan

                             @can('designation:delete')

                            <form action="{{ route('designation.destroy', $designation->id) }}"
                                  method="POST"
                                  class="d-inline"
                                  id="delete-form-{{ $designation->id }}">
                                @csrf
                                @method('DELETE')
                                <button type="button"
                                        class="dropdown-item text-danger"
                                        onclick="confirmDelete({{ $designation->id }})"><i class="bx bx-trash me-1"></i> Delete</button>
                            </form>

                            @endcan
                        
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">
                            No designations found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center">
        @if($designations->hasPages())
            <div class="text-muted">Showing {{ $designations->firstItem() ?? 0 }} to {{ $designations->lastItem() ?? 0 }} of {{ $designations->total() }} Designations</div>
            <div>{{ $designations->links() }}</div>
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

function updateDesignationStatus(el) {

    let designationId = el.getAttribute('data-id');
    let status = el.checked ? 'active' : 'inactive';

    $.ajax({
        url: "{{ route('designation.status') }}",
        type: "PUT",
        data: {
            _token: "{{ csrf_token() }}",
            id: designationId,
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
