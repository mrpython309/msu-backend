@extends('layouts/contentNavbarLayout')

@section('title', 'Purchase Orders')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="mb-1">Purchase Orders</h4>
    </div>
    @canany(['purchase-order.create', 'purchase-orders:create'])
        <a href="{{ route('purchase-orders.create') }}" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i> Create PO
        </a>
    @endcanany
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible">{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if (session('error'))
    <div class="alert alert-danger alert-dismissible">{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
<div id="po-payment-alert" class="d-none"></div>

<div class="card mb-4">
    <div class="card-header pb-0"><h5 class="mb-0">Filters</h5></div>
    <div class="card-body pt-2">
        <form method="GET" action="{{ route('purchase-orders.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="PO No., supplier, indent" value="{{ $filters['search'] ?? '' }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">PO Status</label>
                <select name="status" class="form-select">
                    <option value="">All</option>
                    @foreach ($statusOptions as $status)
                        <option value="{{ $status }}" {{ ($filters['status'] ?? '') === $status ? 'selected' : '' }}>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Date From</label>
                <input type="date" name="date_from" class="form-control" value="{{ $filters['date_from'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Date To</label>
                <input type="date" name="date_to" class="form-control" value="{{ $filters['date_to'] ?? '' }}">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100"><i class="bx bx-search"></i></button>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">All Purchase Orders</h5>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead>
                <tr>
                    <th class="text-nowrap">#</th>
                    <th>PO Number</th>
                    <th>Vendor</th>
                    <th>Item Asset</th>
                    <th>Total Amount</th>
                    <th>Remaining Amount</th>
                    <th>Payment Status</th>
                    <th>PO Status</th>
                    <th style="width:70px">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($orders as $po)
                    @php
                        $status = $po->statusEnum();
                        $paid = $po->paidAmount();
                        $remaining = $po->remainingAmount();
                        $payStatus = $po->paymentStatusEnum();
                        $currency = $po->currency ?: 'INR';
                    @endphp
                    <tr data-po-id="{{ $po->id }}">
                        <td class="text-nowrap text-muted">
                            {{ ($orders->currentPage() - 1) * $orders->perPage() + $loop->iteration }}
                        </td>
                        <td class="text-nowrap">@include('purchase-orders._po-number', ['po' => $po])</td>
                        <td>{{ $po->vendor->name ?? '-' }}</td>
                        <td>@include('partials.item-names-summary', ['names' => $po->itemNameList()])</td>
                        <td class="po-total">{{ number_format($po->orderTotalAmount(), 2) }}</td>
                        <td class="po-remaining">{{ number_format($remaining, 2) }}</td>
                        <td>
                            <span class="badge bg-label-{{ $payStatus->badgeClass() }} po-payment-status">
                                {{ $payStatus->value }}
                            </span>
                        </td>
                        <td>
                            <span class="badge bg-label-{{ $status?->badgeClass() ?? 'secondary' }}">
                                {{ $status?->label() ?? $po->status }}
                            </span>
                        </td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn btn-sm btn-icon btn-outline-secondary dropdown-toggle hide-arrow"
                                        data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item" href="{{ route('purchase-orders.show', $po) }}">
                                            <i class="bx bx-show me-1"></i> View
                                        </a>
                                    </li>

                                    @php
                                        $hasPaymentActivity = $po->paymentRequests()->exists() || $po->payments()->exists() || $paid > 0 || strcasecmp($payStatus->value, 'Paid') === 0 || strcasecmp($payStatus->value, 'Partially Paid') === 0 || $remaining <= 0;
                                    @endphp

                                    @if (! $hasPaymentActivity)
                                        @if ($po->isRejectedStatus())
                                            @canany(['purchase-order.delete', 'purchase-orders:delete'])
                                                @if ($po->canBeDeleted())
                                                    <li>
                                                        <form method="POST" action="{{ route('purchase-orders.destroy', $po) }}"
                                                              onsubmit="return confirm('Delete purchase order {{ $po->po_no }}? This cannot be undone.');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger">
                                                                <i class="bx bx-trash me-1"></i> Delete
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endif
                                            @endcanany
                                        @else
                                            @php
                                                $hasAnyPaymentOrRequest = $po->paymentRequests()->exists() || $po->paidAmount() > 0 || $po->payments()->exists();
                                            @endphp
                                            @if (($po->isCompletedStatus() || $po->allowsPayment()) && ! $hasAnyPaymentOrRequest)
                                                <li>
                                                    <form action="{{ route('payment-request.store', $po) }}" method="POST" class="payment-req-form">
                                                        @csrf
                                                        <button type="submit" class="dropdown-item text-primary btn-payment-request-confirm" data-po="{{ $po->po_no }}" data-total="{{ (float) ($po->grand_total ?: $po->total_amount ?: 0) }}" data-min-adv="{{ (float) ($po->minimum_advance_payment_percentage ?? 0) }}" style="background:none; border:none; width:100%; text-align:left;">
                                                            <i class="bx bx-credit-card me-1"></i> Payment Request
                                                        </button>
                                                    </form>
                                                </li>
                                            @endif
                                            @canany(['purchase-order.edit', 'purchase-orders:edit'])
                                                @if ($po->canBeEdited())
                                                    <li>
                                                        <a class="dropdown-item" href="{{ route('purchase-orders.edit', $po) }}">
                                                            <i class="bx bx-edit me-1"></i> Edit
                                                        </a>
                                                    </li>
                                                @endif
                                            @endcanany
                                            @canany(['purchase-order.delete', 'purchase-orders:delete'])
                                                @if ($po->canBeDeleted())
                                                    <li>
                                                        <form method="POST" action="{{ route('purchase-orders.destroy', $po) }}"
                                                              onsubmit="return confirm('Delete purchase order {{ $po->po_no }}? This cannot be undone.');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="dropdown-item text-danger">
                                                                <i class="bx bx-trash me-1"></i> Delete
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endif
                                            @endcanany
                                        @endif
                                    @endif
                                </ul>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-4">No purchase orders found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($orders->hasPages())
        <div class="card-footer">{{ $orders->links() }}</div>
    @endif
</div>

@include('purchase-orders._payment-modal', ['paymentMethods' => $paymentMethods])
@endsection

@section('page-script')
@include('purchase-orders._payment-scripts')
@endsection
