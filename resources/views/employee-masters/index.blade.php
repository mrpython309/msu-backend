@extends('layouts/contentNavbarLayout')

@section('title', 'Employee Master')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Employee Master</h4>
    <div class="d-flex gap-2">
        @include('partials.master-export-buttons', ['excelRoute' => 'employee-masters.export', 'pdfRoute' => 'employee-masters.export-pdf', 'permission' => 'employee-master:view'])
        @can('employee-master:create')
        <a href="{{ route('employee-masters.create') }}" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i> Add Employee
        </a>
        @endcan
    </div>
</div>

<x-master-filters action="{{ route('employee-masters.index') }}" placeholder="Name, mobile, email, or department">
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
    <div class="card-header">
        <h5 class="mb-0">Employee List</h5>
    </div>
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th class="text-nowrap">S.No</th>
                    <th>Employee Name</th>
                    <th>Department</th>
                    <th>Mobile Number</th>
                    <th>Email</th>
                    <th>Status</th>
                    <th class="text-center" style="width:100px">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $employee)
                    <tr>
                        <td class="text-nowrap text-muted">
                            {{ ($employees->currentPage() - 1) * $employees->perPage() + $loop->iteration }}
                        </td>
                        <td>{{ $employee->employee_name }}</td>
                        <td>{{ $employee->department?->department_name ?? '--' }}</td>
                        <td>{{ $employee->mobile_number }}</td>
                        <td>{{ $employee->email }}</td>
                        <td>
                            @can('employee-master:status')
                            @if($employee->status === 'active')
                                <span class="badge bg-label-success">Active</span>
                            @else
                                <span class="badge bg-label-danger">Inactive</span>
                            @endif
                            @else
                            <span class="badge bg-{{ $employee->status === 'active' ? 'success' : 'secondary' }}">
                                {{ ucfirst($employee->status) }}
                            </span>
                            @endcan
                        </td>
                        <td class="text-center">
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    @can('employee-master:edit')
                                    <a class="dropdown-item" href="{{ route('employee-masters.edit', $employee) }}">
                                        <i class="bx bx-edit-alt me-1"></i> Edit
                                    </a>
                                    @endcan
                                    @can('employee-master:delete')
                                    <form action="{{ route('employee-masters.destroy', $employee) }}" method="POST" id="delete-form-{{ $employee->id }}">
                                        @csrf @method('DELETE')
                                        <button type="button" class="dropdown-item text-danger" onclick="confirmDelete({{ $employee->id }})">
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
                        <td colspan="7" class="text-center py-4">No employees found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($employees->hasPages())
    <div class="card-footer">{{ $employees->links() }}</div>
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
function updateEmployeeStatus(el) {
    $.ajax({
        url: "{{ route('employee-masters.status') }}",
        type: "PUT",
        data: { _token: "{{ csrf_token() }}", id: el.getAttribute('data-id'), status: el.checked ? 'active' : 'inactive' },
        error: function () { el.checked = !el.checked; }
    });
}
</script>
@endsection
