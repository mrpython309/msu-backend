@extends('layouts/contentNavbarLayout')

@section('title', 'Physical Location')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
  Physical Location
</h4>

<x-master-filters action="{{ route('location.index') }}" placeholder="Physical location, branch, or company" />

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">Physical Location List</h5>
        </div>
        <div class="d-flex gap-2">
            @include('partials.master-export-buttons', ['excelRoute' => 'location.export', 'pdfRoute' => 'location.export-pdf', 'permission' => 'location:view'])
            @can('location:create')
            <a href="{{ route('location.create') }}" class="btn btn-primary">
                Add Physical Location
            </a>
            @endcan
        </div>
        
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th class="text-nowrap">#</th>
                    <th>Physical Location</th>
                    <th>Branch</th>
                    <th>Company</th>
                    <th>Status</th>
                    <th width="180">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($locations as $location)
                    <tr>
                        <td class="text-nowrap text-muted">
                            {{ ($locations->currentPage() - 1) * $locations->perPage() + $loop->iteration }}
                        </td>
                        <td>{{ $location->location_name }}</td>
                        <td>
                            @php $branches = $location->_branches ?? collect(); @endphp
                             @if($branches->isEmpty())
                                    --
                            @else
                                 {{ $branches->take(3)->pluck('name')->implode(', ') }}@if($branches->count() > 3)...@endif
                            @endif
                        </td>
                        <td>
                        {{ $location->branch->company->name ?? '--' }}
                        </td>
                        <td>
                            @if($location->status === 'active')
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

                             @can('location:edit')
                            <a href="{{ route('location.edit', $location->id) }}"
                               class="dropdown-item"><i class="bx bx-edit-alt me-1"></i> Edit</a>
                            @endcan
                            
 @can('location:delete')
                            <form action="{{ route('location.destroy', $location->id) }}"
                                  method="POST"
                                  class="d-inline"
                                  id="delete-form-{{ $location->id }}">
                                @csrf
                                @method('DELETE')
                                <button type="button"
                                        class="dropdown-item text-danger"
                                        onclick="confirmDelete({{ $location->id }})"><i class="bx bx-trash me-1"></i> Delete</button>
                            </form>
                            @endcan
                        
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">
                            No Physical Location found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center">
        @if($locations->hasPages())
            <div class="text-muted">Showing {{ $locations->firstItem() ?? 0 }} to {{ $locations->lastItem() ?? 0 }} of {{ $locations->total() }} Physical Locations</div>
            <div>{{ $locations->links() }}</div>
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
        url: "{{ route('location.status') }}",
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
