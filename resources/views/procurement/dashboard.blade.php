@extends('layouts/contentNavbarLayout')

@section('title', 'Procurement Dashboard')

@section('content')
<h4 class="fw-bold mb-4">Procurement Dashboard</h4>

<div class="row g-4 mb-4">
    @foreach ([
        'indents' => ['Total Indents', 'primary'],
        'pending_approvals' => ['Pending Approvals', 'warning'],
        'requisitions' => ['Purchase Requisitions', 'info'],
        'orders' => ['Purchase Orders', 'success'],
        'grns' => ['Goods Received (Pending)', 'danger'],
        'assets_created' => ['Assets Created', 'secondary'],
        'procurement_cost' => ['Procurement Cost', 'dark'],
        'open_rfqs' => ['Open RFQs', 'info'],
    ] as $key => [$label, $color])
        <div class="col-md-6 col-xl-3">
            <div class="card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <small class="text-muted d-block mb-1">{{ $label }}</small>
                            <h3 class="mb-0">
                                @if ($key === 'procurement_cost')
                                    {{ number_format((float) ($metrics[$key] ?? 0), 2) }}
                                @else
                                    {{ $metrics[$key] ?? 0 }}
                                @endif
                            </h3>
                        </div>
                        <span class="avatar-initial rounded bg-label-{{ $color }}">
                            <i class="bx bx-bar-chart-alt-2"></i>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0">Recent Indents</h5>
                <a href="{{ route('indents.index') }}" class="btn btn-sm btn-primary">View All</a>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Indent</th>
                            <th>Department</th>
                            <th>Priority</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentIndents as $indent)
                            <tr>
                                <td><a href="{{ route('indents.show', $indent) }}">{{ $indent->indent_no }}</a></td>
                                <td>{{ $indent->department->department_name ?? '--' }}</td>
                                <td>{{ $indent->priority }}</td>
                                <td><span class="badge bg-label-secondary">{{ $indent->status->value ?? $indent->status }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center">No indents yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card">
            <div class="card-header"><h5 class="mb-0">Monthly Procurement Trend</h5></div>
            <div class="card-body">
                <ul class="list-unstyled mb-0">
                    @forelse ($monthlyTrends as $trend)
                        <li class="d-flex justify-content-between mb-2">
                            <span>{{ $trend['label'] }}</span>
                            <strong>{{ number_format((float) $trend['total'], 2) }}</strong>
                        </li>
                    @empty
                        <li class="text-muted">No purchase order data yet.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
