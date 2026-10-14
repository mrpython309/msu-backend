@extends('layouts/contentNavbarLayout')

@section('title', 'Stock Adjustment Details')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0">
            <span class="text-muted fw-light">Inventory / Stock Adjustments /</span> Details
        </h4>
        <div class="d-flex gap-2">
            @can('stock-adjustments:view')
            <a href="{{ route('stock-adjustments.export', ['id' => $stockAdjustment->id]) }}" class="btn btn-outline-success">
                <i class="bx bx-export me-1"></i> Excel
            </a>
            <a href="{{ route('stock-adjustments.export-pdf', ['id' => $stockAdjustment->id]) }}" class="btn btn-outline-danger">
                <i class="bx bxs-file-pdf me-1"></i> PDF
            </a>
            @endcan
            <a href="{{ route('stock-adjustments.index') }}" class="btn btn-secondary">
                <i class="bx bx-arrow-back me-1"></i> Back
            </a>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Adjustment: {{ $stockAdjustment->adjustment_number }}</h5>
            @if($stockAdjustment->adjustment_type === 'increment')
                <span class="badge bg-label-success fs-6"><i class="bx bx-up-arrow-alt"></i> Increment</span>
            @else
                <span class="badge bg-label-danger fs-6"><i class="bx bx-down-arrow-alt"></i> Decrement</span>
            @endif
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-3 mb-3">
                    <small class="text-muted d-block">Date</small>
                    <strong>{{ $stockAdjustment->adjustment_date->format('d-m-Y') }}</strong>
                </div>
                <div class="col-md-3 mb-3">
                    <small class="text-muted d-block">Item Code</small>
                    <strong>{{ $stockAdjustment->itemMaster?->item_code ?? '--' }}</strong>
                </div>
                <div class="col-md-3 mb-3">
                    <small class="text-muted d-block">Item Name</small>
                    <strong>{{ $stockAdjustment->itemMaster?->item_name ?? '--' }}</strong>
                </div>
                <div class="col-md-3 mb-3">
                    <small class="text-muted d-block">Adjusted Quantity</small>
                    <strong class="{{ $stockAdjustment->adjustment_type === 'increment' ? 'text-success' : 'text-danger' }} fs-5">
                        {{ $stockAdjustment->adjustment_type === 'increment' ? '+' : '-' }}{{ number_format($stockAdjustment->quantity, 3) }}
                    </strong>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-2 mb-3">
                    <small class="text-muted d-block">Unit</small>
                    <strong>{{ $stockAdjustment->unit ?: '--' }}</strong>
                </div>
                <div class="col-md-2 mb-3">
                    <small class="text-muted d-block">Price</small>
                    <strong>{{ number_format((float) $stockAdjustment->price, 2) }}</strong>
                </div>
                <div class="col-md-2 mb-3">
                    <small class="text-muted d-block">Total</small>
                    <strong>{{ number_format((float) $stockAdjustment->total, 2) }}</strong>
                </div>
                <div class="col-md-2 mb-3">
                    <small class="text-muted d-block">GST Percentage</small>
                    <strong>{{ number_format((float) $stockAdjustment->gst_percentage, 2) }}%</strong>
                </div>
                <div class="col-md-2 mb-3">
                    <small class="text-muted d-block">GST Amount</small>
                    <strong>{{ number_format((float) $stockAdjustment->gst_amount, 2) }}</strong>
                </div>
                <div class="col-md-2 mb-3">
                    <small class="text-muted d-block">Grand Total</small>
                    <strong class="fs-5">{{ number_format((float) $stockAdjustment->grand_total, 2) }}</strong>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-md-3 mb-3">
                    <small class="text-muted d-block">Employee</small>
                    <strong>{{ $stockAdjustment->employee?->employee_name ?? '--' }}</strong>
                </div>
                <div class="col-md-3 mb-3">
                    <small class="text-muted d-block">Department</small>
                    <strong>{{ $stockAdjustment->department?->department_name ?? '--' }}</strong>
                </div>
                <div class="col-md-3 mb-3">
                    <small class="text-muted d-block">Created By</small>
                    <strong>{{ $stockAdjustment->createdBy?->name ?? '--' }}</strong>
                </div>
                <div class="col-md-3 mb-3">
                    <small class="text-muted d-block">Created At</small>
                    <strong>{{ $stockAdjustment->created_at->format('d-m-Y H:i A') }}</strong>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <small class="text-muted d-block">Remarks</small>
                    <div class="p-3 bg-light border rounded mt-1">
                        {{ $stockAdjustment->remarks ?: 'No remarks provided.' }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
