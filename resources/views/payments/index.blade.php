@extends('layouts/contentNavbarLayout')

@section('title', 'Payments')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="mb-1">Payments Module</h4>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bx bx-error me-1"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card mb-4">
    <div class="card-header pb-0"><h5 class="mb-0">Filters & Status</h5></div>
    <div class="card-body pt-2">
        <form method="GET" action="{{ route('payments.index') }}" class="row g-3 mb-3">
            <div class="col-md-5">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="Request No., PO No., Vendor Name, Ref No." value="{{ request('search') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Payment Status</label>
                <select name="status" class="form-select">
                    <option value="all">All Status</option>
                    <option value="Pending" {{ request('status') === 'Pending' ? 'selected' : '' }}>Pending</option>
                    <option value="Approved" {{ request('status') === 'Approved' ? 'selected' : '' }}>Approved</option>
                    <option value="Partially Paid" {{ request('status') === 'Partially Paid' ? 'selected' : '' }}>Partially Paid</option>
                    <option value="Paid" {{ request('status') === 'Paid' ? 'selected' : '' }}>Paid</option>
                    <option value="Rejected" {{ request('status') === 'Rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-100"><i class="bx bx-search me-1"></i> Filter</button>
                <a href="{{ route('payments.index') }}" class="btn btn-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Payment Requests</h5>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>Payment Req No.</th>
                    <th>PO No.</th>
                    <th>Vendor</th>
                    <th>Requested By</th>
                    <th>Request Date</th>
                    <th>PO Total Amount</th>
                    <th>Payment Method</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse ($paymentRequests as $requestItem)
                    <tr>
                        <td>
                            <a href="{{ route('payments.show', $requestItem) }}" class="fw-bold">
                                {{ $requestItem->payment_request_no }}
                            </a>
                        </td>
                        <td>
                            @if ($requestItem->purchaseOrder)
                                <a href="{{ route('purchase-orders.show', $requestItem->purchaseOrder) }}">
                                    {{ $requestItem->purchaseOrder->po_no }}
                                </a>

                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <span class="fw-semibold">{{ $requestItem->purchaseOrder?->vendor?->name ?? 'N/A' }}</span>
                                @if($requestItem->purchaseOrder)
                                    <small class="text-muted">PO Date: {{ $requestItem->purchaseOrder->created_at->format('d M Y') }}</small>
                                @endif
                            </div>
                        </td>
                        <td>{{ $requestItem->requestedBy->name ?? 'System' }}</td>
                        <td>{{ $requestItem->request_date->format('d M Y, h:i A') }}</td>
                        <td>
                            <span class="fw-bold">
                                {{ $requestItem->purchaseOrder?->currency ?? 'INR' }}
                                {{ number_format($requestItem->purchaseOrder?->orderTotalAmount() ?? $requestItem->amount, 2) }}
                            </span>
                        </td>
                        <td>
                            @if ($requestItem->isPaid() || $requestItem->isPartiallyPaid())
                                <span class="badge bg-label-secondary">{{ $requestItem->payment_method ?? 'N/A' }}</span>
                                @if($requestItem->payment_date)
                                    <br><small class="text-muted">{{ $requestItem->payment_date->format('d M Y') }}</small>
                                @endif
                            @else
                                <span class="text-muted">--</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $requestItem->statusBadgeClass() }}">{{ $requestItem->status }}</span>
                        </td>
                        <td>
                            <a href="{{ route('payments.show', $requestItem) }}" class="btn btn-sm btn-outline-primary">
                                <i class="bx bx-show-alt me-1"></i> View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">
                            <i class="bx bx-credit-card d-block fs-2 mb-2"></i>
                            No payment requests found matching your criteria.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if ($paymentRequests->hasPages())
        <div class="card-footer d-flex justify-content-end pb-0">
            {{ $paymentRequests->links() }}
        </div>
    @endif
</div>
@endsection
