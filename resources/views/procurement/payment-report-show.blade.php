@extends('layouts/contentNavbarLayout')

@section('title', 'Payment Report')

@section('content')
@php
    $query = array_filter([
        'vendor_id' => $filters['vendor_id'] ?? null,
        'date_from' => $filters['date_from'] ?? null,
        'date_to' => $filters['date_to'] ?? null,
        'status' => $filters['status'] ?? null,
    ], fn ($v) => $v !== null && $v !== '');
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="mb-0">Payment Report</h4>
    </div>
    <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-outline-success btn-sm"
           href="{{ route('procurement.reports.show', array_merge(['report' => 'payments', 'format' => 'excel'], $query)) }}">
            <i class="bx bx-file me-1"></i> Export Excel
        </a>
        <a class="btn btn-outline-primary btn-sm"
           href="{{ route('procurement.reports.show', array_merge(['report' => 'payments', 'format' => 'pdf'], $query)) }}">
            <i class="bx bx-printer me-1"></i> Export PDF
        </a>
        <a class="btn btn-secondary btn-sm" href="{{ route('procurement.reports') }}">Back</a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('procurement.reports.show', 'payments') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label class="form-label">Vendor</label>
                <select name="vendor_id" class="form-select">
                    <option value="">All Vendors</option>
                    @foreach ($vendors as $vendor)
                        <option value="{{ $vendor->id }}" @selected((string) ($filters['vendor_id'] ?? '') === (string) $vendor->id)>
                            {{ $vendor->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">From Date</label>
                <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">To Date</label>
                <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Payment Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>
                            {{ $status->value }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3 d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-primary">Apply Filter</button>
                <a href="{{ route('procurement.reports.show', 'payments') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <small class="text-muted d-block">Purchase Orders</small>
                <h4 class="mb-0">{{ $summary['po_count'] }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <small class="text-muted d-block">Total PO Amount</small>
                <h4 class="mb-0">{{ number_format((float) $summary['total_po_amount'], 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <small class="text-muted d-block">Total Paid</small>
                <h4 class="mb-0 text-success">{{ number_format((float) $summary['total_paid_amount'], 2) }}</h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100">
            <div class="card-body">
                <small class="text-muted d-block">Total Remaining</small>
                <h4 class="mb-0">{{ number_format((float) $summary['total_remaining'], 2) }}</h4>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Payment Details</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover mb-0 align-middle">
            <thead>
                <tr>
                    <th>PO No</th>
                    <th>Vendor</th>
                    <th>PO Date</th>
                    <th class="text-end">PO Total</th>
                    <th class="text-end">Total Paid</th>
                    <th class="text-end">Remaining</th>
                    <th>Payment Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($records as $po)
                    @php $status = $po->paymentStatusEnum(); @endphp
                    <tr>
                        <td>
                            <a href="{{ route('purchase-orders.show', $po) }}">{{ $po->po_no }}</a>
                        </td>
                        <td>{{ $po->vendor?->name ?? '--' }}</td>
                        <td>{{ optional($po->purchase_date)->format('d M Y') ?? '--' }}</td>
                        <td class="text-end">{{ number_format((float) $po->orderTotalAmount(), 2) }}</td>
                        <td class="text-end fw-semibold">{{ number_format((float) $po->paidAmount(), 2) }}</td>
                        <td class="text-end">{{ number_format((float) $po->remainingAmount(), 2) }}</td>
                        <td>
                            <span class="badge bg-label-{{ $status->badgeClass() }}">{{ $status->value }}</span>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No purchase orders found for the selected filters.</td>
                    </tr>
                @endforelse
            </tbody>
            @if ($records->isNotEmpty())
                <tfoot>
                    <tr class="table-light fw-semibold">
                        <td colspan="3">Total</td>
                        <td class="text-end">{{ number_format((float) $summary['total_po_amount'], 2) }}</td>
                        <td class="text-end">{{ number_format((float) $summary['total_paid_amount'], 2) }}</td>
                        <td class="text-end">{{ number_format((float) $summary['total_remaining'], 2) }}</td>
                        <td></td>
                    </tr>
                </tfoot>
            @endif
        </table>
    </div>
</div>
@endsection
