@extends('layouts/contentNavbarLayout')

@section('title', 'Item Stock Ledger - ' . $item->item_code)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0">
            <span class="text-muted fw-light"><a href="{{ route('item-stocks.index') }}" class="text-muted">Item Stock</a> /</span> {{ $item->item_name }} ({{ $item->item_code }})
        </h4>
        <a href="{{ route('item-stocks.index') }}" class="btn btn-secondary">
            <i class="bx bx-arrow-back"></i> Back
        </a>
    </div>

    <!-- Stock Summary Cards -->
    <div class="row mb-4">
        <div class="col-sm-6 col-lg-3 mb-4">
            <div class="card card-border-shadow-primary h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2 pb-1">
                        <div class="avatar me-2">
                            <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-box"></i></span>
                        </div>
                        <h4 class="ms-1 mb-0">{{ number_format($todayStock ? $todayStock->opening_stock : $item->opening_stock, 3) }}</h4>
                    </div>
                    <p class="mb-1 fw-semibold">Opening Stock</p>
                    <small class="text-muted">Base Quantity</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3 mb-4">
            <div class="card card-border-shadow-success h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2 pb-1">
                        <div class="avatar me-2">
                            <span class="avatar-initial rounded bg-label-success"><i class="bx bx-down-arrow-alt"></i></span>
                        </div>
                        <h4 class="ms-1 mb-0 text-success">+{{ number_format($totalIn, 3) }}</h4>
                    </div>
                    <p class="mb-1 fw-semibold">Stock In (GRN)</p>
                    <small class="text-muted">Total Received</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3 mb-4">
            <div class="card card-border-shadow-danger h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2 pb-1">
                        <div class="avatar me-2">
                            <span class="avatar-initial rounded bg-label-danger"><i class="bx bx-up-arrow-alt"></i></span>
                        </div>
                        <h4 class="ms-1 mb-0 text-danger">-{{ number_format($totalOut, 3) }}</h4>
                    </div>
                    <p class="mb-1 fw-semibold">Stock Out (MI)</p>
                    <small class="text-muted">Total Issued</small>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3 mb-4">
            <div class="card card-border-shadow-info h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center mb-2 pb-1">
                        <div class="avatar me-2">
                            <span class="avatar-initial rounded bg-label-info"><i class="bx bx-check-shield"></i></span>
                        </div>
                        <h4 class="ms-1 mb-0 text-info">{{ number_format($item->current_stock, 3) }}</h4>
                    </div>
                    <p class="mb-1 fw-semibold">Current Stock</p>
                    <small class="text-muted">Available Quantity</small>
                    <div class="mt-2">
                        <span class="badge bg-label-{{ $item->stockStatusBadgeClass() }}">{{ $item->stock_status }}</span>
                        @if($item->threshold_quantity !== null)
                            <small class="text-muted ms-1">Threshold: {{ number_format((float) $item->threshold_quantity, 3) }}</small>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ledger Table -->
    <div class="card">
        <div class="card-header border-bottom">
            <h5 class="card-title mb-0">Transaction Ledger</h5>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date & Time</th>
                        <th>Type</th>
                        <th class="text-end">Qty</th>
                        <th class="text-end">Stock Before</th>
                        <th class="text-end">Stock After</th>
                        <th>Remarks</th>
                        <th>By</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($movements as $movement)
                        <tr>
                            <td>{{ $movement->created_at->format('d-M-Y H:i') }}</td>
                            <td>
                                @if($movement->transaction_type == 'in')
                                    <span class="badge bg-label-success">IN</span>
                                @elseif($movement->transaction_type == 'out')
                                    <span class="badge bg-label-danger">OUT</span>
                                @else
                                    <span class="badge bg-label-warning">ADJ</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold {{ $movement->transaction_type == 'in' ? 'text-success' : ($movement->transaction_type == 'out' ? 'text-danger' : '') }}">
                                {{ $movement->transaction_type == 'in' ? '+' : ($movement->transaction_type == 'out' ? '-' : '') }}{{ number_format($movement->quantity, 3) }}
                            </td>
                            <td class="text-end text-muted">{{ number_format($movement->stock_before, 3) }}</td>
                            <td class="text-end fw-semibold">{{ number_format($movement->stock_after, 3) }}</td>
                            <td>
                                <span class="d-inline-block text-truncate" style="max-width: 200px;" title="{{ $movement->remarks }}">
                                    {{ $movement->remarks ?? '-' }}
                                </span>
                            </td>
                            <td>{{ $movement->createdBy?->name ?? 'System' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No stock movements found for this item.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($movements->hasPages())
        <div class="card-footer d-flex justify-content-center">
            {{ $movements->links() }}
        </div>
        @endif
    </div>

    <!-- Daily Stock History -->
    <div class="card mt-4">
        <div class="card-header border-bottom">
            <h5 class="card-title mb-0">Daily Stock History</h5>
        </div>
        <div class="table-responsive text-nowrap">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th class="text-end">Opening Stock</th>
                        <th class="text-end">Stock In</th>
                        <th class="text-end">Stock Out</th>
                        <th class="text-end">Closing Stock</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($dailyStocks as $daily)
                        <tr>
                            <td>{{ $daily->date->format('d-M-Y') }}</td>
                            <td class="text-end">{{ number_format($daily->opening_stock, 3) }}</td>
                            <td class="text-end text-success">+{{ number_format($daily->stock_in, 3) }}</td>
                            <td class="text-end text-danger">-{{ number_format($daily->stock_out, 3) }}</td>
                            <td class="text-end fw-bold">{{ number_format($daily->closing_stock, 3) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center py-4 text-muted">No daily stock history available.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
