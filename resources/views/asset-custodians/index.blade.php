@extends('layouts/contentNavbarLayout')

@section('title', 'Asset Custodian')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
    Asset Custodian
</h4>

<x-master-filters action="{{ route('asset-custodians.index') }}" placeholder="Name, mobile, email, or department">
    <div class="col-md-3">
        <label class="form-label" for="department_id">Department</label>
        <select id="department_id" name="department_id" class="form-select select2">
            <option value="">All Departments</option>
            @foreach($departments as $department)
                <option value="{{ $department->id }}" @selected(request('department_id') == $department->id)>{{ $department->department_name }}</option>
            @endforeach
        </select>
    </div>
</x-master-filters>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">Asset Custodian List</h5>
        </div>
        <div class="d-flex gap-2">
            @include('partials.master-export-buttons', ['excelRoute' => 'asset-custodians.export', 'pdfRoute' => 'asset-custodians.export-pdf', 'permission' => 'asset-custodian:view'])
            @can('asset-custodian:create')
            <a href="{{ route('asset-custodians.create') }}" class="btn btn-primary">
                <i class="bx bx-plus me-1"></i> Add Custodian
            </a>
            @endcan
        </div>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th class="text-nowrap">S.No</th>
                    <th>Custodian Name</th>
                    <th>Department</th>
                    <th>Mobile Number</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th class="text-center" style="width:100px">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($custodians as $custodian)
                    <tr>
                        <td class="text-nowrap text-muted">
                            {{ ($custodians->currentPage() - 1) * $custodians->perPage() + $loop->iteration }}
                        </td>
                        <td>{{ $custodian->employee_name }}</td>
                        <td>{{ $custodian->department?->department_name ?? '--' }}</td>
                        <td>{{ $custodian->mobile_number }}</td>
                        <td>{{ $custodian->email }}</td>
                        <td>
                            @can('asset-custodian:status')
                            @if($custodian->status === 'active')
                                <span class="badge bg-label-success">Active</span>
                            @else
                                <span class="badge bg-label-danger">Inactive</span>
                            @endif
                            @else
                            <span class="badge bg-{{ $custodian->status === 'active' ? 'success' : 'secondary' }}">
                                {{ ucfirst($custodian->status) }}
                            </span>
                            @endcan
                        </td>
                        <td class="text-center">
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    @can('asset-custodian:edit')
                                    <a class="dropdown-item" href="{{ route('asset-custodians.edit', $custodian) }}">
                                        <i class="bx bx-edit-alt me-1"></i> Edit
                                    </a>
                                    @endcan
                                    @can('asset-custodian:delete')
                                    <form action="{{ route('asset-custodians.destroy', $custodian) }}" method="POST" id="delete-form-{{ $custodian->id }}">
                                        @csrf @method('DELETE')
                                        <button type="button" class="dropdown-item text-danger" onclick="confirmDelete({{ $custodian->id }})">
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
                        <td colspan="7" class="text-center py-4">No asset custodians found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($custodians->hasPages())
    <div class="card-footer">{{ $custodians->links() }}</div>
    @endif
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
function updateCustodianStatus(el) {
    $.ajax({
        url: "{{ route('asset-custodians.status') }}",
        type: "PUT",
        data: { _token: "{{ csrf_token() }}", id: el.getAttribute('data-id'), status: el.checked ? 'active' : 'inactive' },
        error: function () { el.checked = !el.checked; }
    });
}
</script>
@endsection
