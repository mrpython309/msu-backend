@extends('layouts/contentNavbarLayout')

@section('title', 'Indent Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Indent Dashboard</h4>
</div>

<div class="row">
    <div class="col-lg-3 col-md-6 col-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="avatar flex-shrink-0 mb-2">
                    <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-receipt"></i></span>
                </div>
                <span class="fw-semibold d-block mb-1">Total Indents</span>
                <h3 class="card-title mb-0">{{ $totalIndents }}</h3>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="avatar flex-shrink-0 mb-2">
                    <span class="avatar-initial rounded bg-label-warning"><i class="bx bx-time-five"></i></span>
                </div>
                <span class="fw-semibold d-block mb-1">Pending Approvals</span>
                <h3 class="card-title mb-0">{{ $pendingIndents }}</h3>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="avatar flex-shrink-0 mb-2">
                    <span class="avatar-initial rounded bg-label-success"><i class="bx bx-check-circle"></i></span>
                </div>
                <span class="fw-semibold d-block mb-1">Completed</span>
                <h3 class="card-title mb-0">{{ $completedIndents }}</h3>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-md-6 col-6 mb-4">
        <div class="card">
            <div class="card-body">
                <div class="avatar flex-shrink-0 mb-2">
                    <span class="avatar-initial rounded bg-label-danger"><i class="bx bx-x-circle"></i></span>
                </div>
                <span class="fw-semibold d-block mb-1">Rejected</span>
                <h3 class="card-title mb-0">{{ $rejectedIndents }}</h3>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Recent Indents</h5>
        <a href="{{ route('indents.index') }}" class="btn btn-sm btn-primary">View All</a>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table">
            <thead>
                <tr>
                    <th>Indent No</th>
                    <th>Date</th>
                    <th>Department</th>
                    <th>Requester</th>
                    <th>Priority</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse ($recentIndents as $indent)
                    <tr>
                        <td>
                            <a href="{{ route('indents.show', $indent) }}">{{ $indent->indent_no }}</a>
                        </td>
                        <td>{{ \Carbon\Carbon::parse($indent->request_date)->format('d M Y') }}</td>
                        <td>{{ $indent->department->department_name ?? 'N/A' }}</td>
                        <td>{{ $indent->requestedBy->name ?? '--' }}</td>
                        <td>
                            <span class="badge bg-label-{{ $indent->priority === 'Urgent' ? 'danger' : ($indent->priority === 'High' ? 'warning' : 'info') }}">
                                {{ $indent->priority }}
                            </span>
                        </td>
                        <td>
                            @php
                                $statusLabel = $indent->statusLabel();
                                $statusValue = $indent->status?->value ?? (string) $indent->status;
                            @endphp
                            <span class="badge bg-label-{{ in_array($statusValue, ['Approved', 'Completed', 'Converted to Purchase Requisition', 'Closed'], true) ? 'success' : ($statusValue === 'Rejected' ? 'danger' : 'warning') }}">
                                {{ $statusLabel }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">No recent indents</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
