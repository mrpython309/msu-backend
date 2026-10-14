@extends('layouts/contentNavbarLayout')

@section('title', 'Indents')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Indents</h4>
    @can('indents:create')
    <a href="{{ route('indents.create') }}" class="btn btn-primary btn-sm">
        <i class="bx bx-plus me-1"></i> Create Indent
    </a>
    @endcan
</div>

<div class="card mb-4">
    <div class="card-header pb-0">
        <h5 class="mb-0">Quick Filters</h5>
    </div>
    <div class="card-body pt-2">
        <form method="GET" action="{{ route('indents.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Indent No</label>
                <input type="text" name="indent_no" class="form-control" value="{{ request('indent_no') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    @foreach (['Draft', 'Pending Approval', 'Approved', 'Completed', 'Rejected'] as $status)
                        <option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Priority</label>
                <select name="priority" class="form-select">
                    <option value="">All Priority</option>
                    @foreach (['Low', 'Medium', 'High', 'Urgent'] as $priority)
                        <option value="{{ $priority }}" {{ request('priority') === $priority ? 'selected' : '' }}>{{ $priority }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary"><i class="bx bx-search"></i></button>
                <a href="{{ route('indents.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">All Indents</h5>
        </div>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table">
            <thead>
                <tr>
                    <th class="text-nowrap">#</th>
                    <th>Request No.</th>
                    <th>Date &amp; Time</th>
                    <th>From Store</th>
                    <th>Requester</th>
                    <th>Approval Level</th>
                    <th>Assigned Approver</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse ($indents as $indent)
                    <tr>
                        <td class="text-nowrap text-muted">
                            {{ ($indents->currentPage() - 1) * $indents->perPage() + $loop->iteration }}
                        </td>
                        <td>
                            <a href="{{ route('indents.show', $indent) }}">{{ $indent->indent_no }}</a>
                            @if ($indent->is_urgent)
                                <span class="badge bg-label-danger">Urgent</span>
                            @endif
                            @if ($indent->tag)
                                <div class="small text-muted">{{ $indent->tag }}</div>
                            @endif
                        </td>
                        <td>{{ ($indent->requested_at ?? $indent->created_at)?->format('d M Y h:i A') ?? \Carbon\Carbon::parse($indent->request_date)->format('d M Y') }}</td>
                        <td>{{ $indent->fromStore->department_name ?? $indent->department->department_name ?? 'N/A' }}</td>
                        <td>{{ $indent->requestedBy->name ?? '--' }}</td>
                        <td>
                            @if (! empty($indent->index_approval_level))
                                <span class="badge bg-label-primary">{{ $indent->index_approval_level }}</span>
                            @else
                                --
                            @endif
                        </td>
                        <td>
                            <strong>{{ $indent->index_assigned_approver ?: '--' }}</strong>
                        </td>
                        <td>
                            <span class="badge bg-label-{{ $indent->priority === 'Urgent' || $indent->is_urgent ? 'danger' : ($indent->priority === 'High' ? 'warning' : 'info') }}">
                                {{ $indent->priority }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-label-{{ in_array($indent->status?->value, ['Approved', 'Completed', 'Converted to Purchase Requisition'], true) ? 'success' : ($indent->status?->value === 'Rejected' ? 'danger' : 'warning') }}">
                                {{ $indent->statusLabel() }}
                            </span>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn btn-sm btn-icon btn-outline-secondary dropdown-toggle hide-arrow"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    @can('indents:view')
                                    <li>
                                        <a class="dropdown-item" href="{{ route('indents.show', $indent) }}">
                                            <i class="bx bx-show me-1"></i> View
                                        </a>
                                    </li>
                                    @endcan
                                    @canany(['indents:edit', 'indents:create'])
                                    @if ($indent->canBeEdited())
                                    <li>
                                        <a class="dropdown-item" href="{{ route('indents.edit', $indent) }}">
                                            <i class="bx bx-edit me-1"></i> Edit
                                        </a>
                                    </li>
                                    @endif
                                    @endcanany
                                    @can('indents:delete')
                                    @if ($indent->canBeDeleted())
                                    <li>
                                        <form method="POST" action="{{ route('indents.destroy', $indent) }}" class="delete-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item text-danger">
                                                <i class="bx bx-trash me-1"></i> Delete
                                            </button>
                                        </form>
                                    </li>
                                    @endif
                                    @endcan
                                </ul>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="11" class="text-center">No indents found</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($indents->hasPages())
    <div class="card-footer">
        {{ $indents->links() }}
    </div>
    @endif
</div>
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.delete-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: 'This action cannot be undone!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'Cancel'
            }).then(function (result) {
                if (result.isConfirmed) form.submit();
            });
        });
    });
});
</script>
@endsection
