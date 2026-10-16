@extends('layouts/contentNavbarLayout')

@section('title', 'Purchase Officer Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Purchase Management</h4>
    </div>
</div>

<div class="row g-3 mb-4">
    @foreach ([
        ['Pending Purchase Requisitions', $metrics['pending_prs'], 'warning', route('purchase-requisitions.index', ['status' => 'Pending Purchase'])],
        ['RFQs Awaiting Vendor Response', $metrics['rfqs_awaiting'], 'info', route('rfqs.index')],
        ['Quotations Received', $metrics['quotations_received'], 'primary', route('rfqs.index')],
        ['Pending Purchase Orders', $metrics['pending_pos'], 'secondary', route('purchase-orders.index')],
        ['Completed Purchase Orders', $metrics['completed_pos'], 'success', route('purchase-orders.index')],
    ] as [$label, $value, $color, $url])
        <div class="col-md-4 col-xl">
            <a href="{{ $url }}" class="card h-100 text-decoration-none">
                <div class="card-body">
                    <small class="text-muted d-block">{{ $label }}</small>
                    <h3 class="mb-0 text-{{ $color }}">{{ $value }}</h3>
                </div>
            </a>
        </div>
    @endforeach
</div>

<div class="row g-4">
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0">Recent Purchase Requisitions</h5>
                <a href="{{ route('purchase-requisitions.index') }}" class="btn btn-sm btn-outline-primary">View all</a>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>PR No</th>
                            <th>Indent</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentPrs as $pr)
                            <tr>
                                <td><a href="{{ route('purchase-requisitions.show', $pr) }}">{{ $pr->pr_no }}</a></td>
                                <td>{{ $pr->indent->indent_no ?? '--' }}</td>
                                <td><span class="badge bg-label-warning">{{ $pr->statusEnum()?->label() ?? $pr->status }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-4">No requisitions yet</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex justify-content-between">
                <h5 class="mb-0">Open RFQs</h5>
                <a href="{{ route('rfqs.index') }}" class="btn btn-sm btn-outline-primary">View all</a>
            </div>
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>RFQ No</th>
                            <th>Vendors</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($openRfqs as $rfq)
                            <tr>
                                <td><a href="{{ route('rfqs.show', $rfq) }}">{{ $rfq->rfq_no }}</a></td>
                                <td>{{ $rfq->rfqVendors->count() }}</td>
                                <td><span class="badge bg-label-info">{{ $rfq->status?->value ?? $rfq->status }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="text-center text-muted py-4">No open RFQs</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
