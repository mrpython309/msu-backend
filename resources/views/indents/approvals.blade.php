@extends('layouts/contentNavbarLayout')

@section('title', 'Waiting for Indent Approval')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Waiting for Indent Approval</h4>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('indents.index') }}" class="btn btn-secondary btn-sm">All Indents</a>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <small class="text-muted d-block">All waiting</small>
                <h4 class="mb-0">{{ $allWaitingCount }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-body">
                <small class="text-muted d-block">Assigned to me</small>
                <h4 class="mb-0 text-primary">{{ $waitingForMeCount }}</h4>
            </div>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">View</label>
                <select name="scope" class="form-select">
                    <option value="all" @selected(($scope ?? 'all') === 'all')>All waiting approvals</option>
                    <option value="mine" @selected(($scope ?? '') === 'mine')>Assigned to me only</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Priority</label>
                <select name="priority" class="form-select">
                    <option value="">All</option>
                    @foreach (['Low', 'Medium', 'High', 'Urgent'] as $priority)
                        <option value="{{ $priority }}" @selected(request('priority') === $priority)>{{ $priority }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <button class="btn btn-primary" type="submit">Filter</button>
                <a class="btn btn-outline-secondary" href="{{ route('indents.approvals') }}">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead>
                <tr>
                    <th>Indent Number</th>
                    <th>Requester</th>
                    <th>From Store</th>
                    <th>Approval Level</th>
                    <th>Assigned Approver</th>
                    <th>Pending Since</th>
                    <th>Priority</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($indents as $indent)
                    @php
                        $isMine = (bool) ($indent->is_waiting_for_me ?? ((int) $indent->assigned_approver_id === (int) auth()->id()));
                        $assigneeLabel = $indent->awaiting_label
                            ?? ($indent->assignedApprover->name ?? '--');
                    @endphp
                    <tr class="{{ $isMine ? 'table-primary' : '' }}">
                        <td>
                            <a href="{{ route('indents.show', $indent) }}">{{ $indent->indent_no }}</a>
                            @if ($indent->is_urgent)
                                <span class="badge bg-label-danger">Urgent</span>
                            @endif
                            @if ($isMine)
                                <div><span class="badge bg-label-success">Waiting for you</span></div>
                            @endif
                        </td>
                        <td>{{ $indent->requestedBy->name ?? '--' }}</td>
                        <td>{{ $indent->fromStore->department_name ?? $indent->department->department_name ?? '--' }}</td>
                        <td><span class="badge bg-label-primary">Level {{ $indent->current_sequence }}</span></td>
                        <td>
                            <strong>{{ $assigneeLabel }}</strong>
                        </td>
                        <td>{{ $indent->submitted_at?->diffForHumans() ?? '--' }}</td>
                        <td>
                            <span class="badge bg-label-{{ $indent->priority === 'Urgent' || $indent->is_urgent ? 'danger' : ($indent->priority === 'High' ? 'warning' : 'info') }}">
                                {{ $indent->priority }}
                            </span>
                        </td>
                        <td>{{ $indent->approval_status ?? $indent->status?->value }}</td>
                        <td>
                            <div class="d-flex align-items-center gap-1 flex-wrap">
                                @if ($isMine)
                                    <a class="btn btn-sm btn-primary" href="{{ route('indents.show', $indent) }}" title="Approve / Act">
                                        <i class="bx bx-check-circle me-1"></i> Approve
                                    </a>
                                @else
                                    <a class="btn btn-sm btn-outline-primary" href="{{ route('indents.show', $indent) }}" title="View">
                                        <i class="bx bx-show"></i>
                                    </a>
                                @endif
                                @canany(['indents:edit', 'indents:create'])
                                    @if ($indent->canBeEdited())
                                        <a href="{{ route('indents.edit', $indent) }}" class="btn btn-sm btn-outline-secondary" title="Edit">
                                            <i class="bx bx-edit"></i>
                                        </a>
                                    @endif
                                @endcanany
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center py-4 text-muted">
                            No indents are currently waiting for approval.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($indents->hasPages())
        <div class="card-footer">{{ $indents->links() }}</div>
    @endif
</div>
@endsection
