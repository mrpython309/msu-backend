@extends('layouts/contentNavbarLayout')

@section('title', 'HSN Master')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">HSN Master</h4>
    <div class="d-flex gap-2">
        @include('partials.master-export-buttons', ['excelRoute' => 'hsns.export', 'pdfRoute' => 'hsns.export-pdf', 'permission' => 'hsn:view'])
        @can('hsn:create')
        <a href="{{ route('hsns.create') }}" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i> Add HSN
        </a>
        @endcan
    </div>
</div>

<x-master-filters action="{{ route('hsns.index') }}" placeholder="HSN code or description" />

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">HSN List</h5>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th class="text-nowrap">S.No</th>
                    <th>HSN Code</th>
                    <th>Description</th>
                    <th>Tax Rate (%)</th>
                    <th>Status</th>
                    <th class="text-center" style="width:100px">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($hsns as $hsn)
                    <tr>
                        <td class="text-nowrap text-muted">
                            {{ ($hsns->currentPage() - 1) * $hsns->perPage() + $loop->iteration }}
                        </td>
                        <td><code>{{ $hsn->hsn_code }}</code></td>
                        <td>{{ $hsn->description }}</td>
                        <td>{{ number_format((float) $hsn->tax_rate, 2) }}</td>
                        <td>
                            @can('hsn:status')
                            @if($hsn->status === 'active')
                                <span class="badge bg-label-success">Active</span>
                            @else
                                <span class="badge bg-label-danger">Inactive</span>
                            @endif
                            @else
                            <span class="badge bg-{{ $hsn->status === 'active' ? 'success' : 'secondary' }}">
                                {{ ucfirst($hsn->status) }}
                            </span>
                            @endcan
                        </td>
                        <td class="text-center">
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    @can('hsn:edit')
                                    <a class="dropdown-item" href="{{ route('hsns.edit', $hsn) }}">
                                        <i class="bx bx-edit-alt me-1"></i> Edit
                                    </a>
                                    @endcan
                                    @can('hsn:delete')
                                    <form action="{{ route('hsns.destroy', $hsn) }}" method="POST" id="delete-form-{{ $hsn->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="dropdown-item text-danger" onclick="confirmDelete({{ $hsn->id }})">
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
                        <td colspan="6" class="text-center py-4">
                            <p class="mb-2">No HSN codes found.</p>
                            @can('hsn:create')
                            <a href="{{ route('hsns.create') }}" class="btn btn-primary btn-sm">
                                <i class="bx bx-plus me-1"></i> Add HSN
                            </a>
                            @endcan
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center">
        @if($hsns->hasPages())
            <div class="text-muted">Showing {{ $hsns->firstItem() ?? 0 }} to {{ $hsns->lastItem() ?? 0 }} of {{ $hsns->total() }} HSN codes</div>
            <div>{{ $hsns->links() }}</div>
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

function updateHsnStatus(el) {
    let hsnId = el.getAttribute('data-id');
    let status = el.checked ? 'active' : 'inactive';

    $.ajax({
        url: "{{ route('hsns.status') }}",
        type: "PUT",
        data: {
            _token: "{{ csrf_token() }}",
            id: hsnId,
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
