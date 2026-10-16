@extends('layouts/contentNavbarLayout')

@section('title', 'Cost Centres')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
  Cost Centres
</h4>

<x-master-filters action="{{ route('cost-centres.index') }}" placeholder="Cost centre name" />

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">Cost Centre List</h5>
        </div>
        <div class="d-flex gap-2">
            @include('partials.master-export-buttons', ['excelRoute' => 'cost-centres.export', 'pdfRoute' => 'cost-centres.export-pdf', 'permission' => 'cost-centres:view'])
            @can('cost-centres:create')
            <a href="{{ route('cost-centres.create') }}" class="btn btn-primary">
                Add Cost Centre
            </a>
            @endcan
        </div>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th class="text-nowrap">#</th>
                    <th>Cost Centre</th>
                    <th>Status</th>
                    <th width="180">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($costCentres as $costCentre)
                    <tr>
                        <td class="text-nowrap text-muted">
                            {{ ($costCentres->currentPage() - 1) * $costCentres->perPage() + $loop->iteration }}
                        </td>
                        <td>{{ $costCentre->cost_centre_name }}</td>
                        <td>
                            @if($costCentre->status === 'active')
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

                             @can('cost-centres:edit')
                            <a href="{{ route('cost-centres.edit', $costCentre->id) }}"
                               class="dropdown-item"><i class="bx bx-edit-alt me-1"></i> Edit</a>
                            @endcan

                             @can('cost-centres:delete')

                            <form action="{{ route('cost-centres.destroy', $costCentre->id) }}"
                                  method="POST"
                                  class="d-inline"
                                  id="delete-form-{{ $costCentre->id }}">
                                @csrf
                                @method('DELETE')
                                <button type="button"
                                        class="dropdown-item text-danger"
                                        onclick="confirmDelete({{ $costCentre->id }})"><i class="bx bx-trash me-1"></i> Delete</button>
                            </form>

                            @endcan

                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">
                            No Cost Centres found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center">
        @if($costCentres->hasPages())
            <div class="text-muted">Showing {{ $costCentres->firstItem() ?? 0 }} to {{ $costCentres->lastItem() ?? 0 }} of {{ $costCentres->total() }} Cost Centres</div>
            <div>{{ $costCentres->links() }}</div>
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
</script>
@endsection
