@extends('layouts/contentNavbarLayout')

@section('title', "Quality Inspection #{$grn->grn_no}")

@section('page-style')
<style>
    /* Hide number input arrows/spinners for accepted quantities */
    .modal-accepted-qty::-webkit-outer-spin-button,
    .modal-accepted-qty::-webkit-inner-spin-button,
    .accepted-qty::-webkit-outer-spin-button,
    .accepted-qty::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    .modal-accepted-qty, .accepted-qty {
        -moz-appearance: textfield;
    }
</style>
@endsection

@section('content')
@php
    $badgeClass = match ($grn->status) {
        'Accepted', 'Approved' => 'success',
        'Partially Accepted', 'Under Inspection' => 'warning',
        'Rejected' => 'danger',
        default => 'secondary',
    };
@endphp

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold py-1 mb-0">
            {{ $grn->grn_no }}
        </h4>
        <div class="mt-1">
            <span class="badge bg-label-{{ $badgeClass }} fs-6 me-2">{{ $grn->status }}</span>
            <span class="text-muted small">Received on {{ $grn->received_date?->format('d M Y') ?? '--' }}</span>
        </div>
    </div>
    <div class="d-flex gap-2 align-items-center">
        @if($canPerform && $grn->items->contains(fn($i) => $i->isApproved()) && $grn->items->flatMap->assets->isEmpty())
            <form method="POST" action="{{ route('quality-inspections.create-assets', $grn) }}" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Transfer approved items to Asset records now? Rejected/Damaged items will be excluded.')">
                    <i class="bx bx-transfer me-1"></i> Transfer to Asset
                </button>
            </form>
        @endif
        <a href="{{ route('grns.show', $grn) }}" class="btn btn-outline-info btn-sm">
            <i class="bx bx-file me-1"></i> View GRN Document
        </a>
        <a href="{{ route('quality-inspections.index') }}" class="btn btn-secondary btn-sm">
            <i class="bx bx-arrow-back me-1"></i> Back to List
        </a>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bx bx-error-circle me-1"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

{{-- Section 1: GRN Overview --}}
<div class="card mb-4">
    <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
        <h6 class="mb-0 text-uppercase fw-bold text-dark"><i class="bx bx-info-circle me-2"></i> Section 1 — GRN Overview & Supplier Details</h6>
        <span class="badge bg-label-primary">Warehouse: {{ $grn->warehouse ?? 'Main Store' }}</span>
    </div>
    <div class="card-body py-3">
        <div class="row g-3">
            <div class="col-md-3">
                <small class="text-muted d-block">Vendor / Supplier</small>
                <strong class="text-primary">{{ $grn->vendor?->name ?? '--' }}</strong>
                <div class="small text-muted">{{ $grn->vendor?->code }}</div>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block">Linked Purchase Order</small>
                @if($grn->purchaseOrder)
                    <a href="{{ route('purchase-orders.show', $grn->purchaseOrder) }}" class="fw-bold">{{ $grn->purchaseOrder->po_no }}</a>
                @else
                    <strong class="text-muted">--</strong>
                @endif
            </div>
            <div class="col-md-2">
                <small class="text-muted d-block">Received By</small>
                <strong>{{ $grn->receivedBy?->name ?? '--' }}</strong>
            </div>
            <div class="col-md-2">
                <small class="text-muted d-block">Challan / Invoice No</small>
                <strong>{{ $grn->challan_number ?: ($grn->invoice_number ?: '--') }}</strong>
            </div>
            <div class="col-md-2">
                <small class="text-muted d-block">Inspection Progress</small>
                <strong>{{ $summary['approved'] + $summary['rejected_or_damaged'] + $summary['hold'] }} / {{ $summary['total'] }} Items Evaluated</strong>
            </div>
        </div>
    </div>
</div>

{{-- Section 2: Item-by-Item Quality Inspection --}}
<div class="card mb-4">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0"><i class="bx bx-check-shield text-primary me-2"></i> Section 2 — Item Quality Inspection & Verification</h5>
        </div>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Item Details</th>
                    <th class="text-center">Ordered</th>
                    <th class="text-center">Received</th>
                    <th class="text-center">Accepted</th>
                    <th class="text-center">Rejected</th>
                    <th>Inspection Status & By</th>
                    <th>Post-Inspection Action / Destination</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($grn->items as $item)
                    @php
                        $statusClass = $item->statusBadgeClass();
                    @endphp
                    <tr>
                        <td>
                            <strong class="d-block">{{ $item->poItem?->item_name ?? 'Item' }}</strong>
                            <span class="small text-muted">Code: {{ $item->item_code ?? ($item->poItem?->item_code ?? '--') }} | Unit: {{ $item->purchase_unit ?? 'Nos' }}</span>
                            @if($item->inspection_remarks)
                                <div class="small text-muted mt-1"><i class="bx bx-message-detail text-secondary me-1"></i> <em>"{{ $item->inspection_remarks }}"</em></div>
                            @endif
                        </td>
                        <td class="text-center">{{ $item->ordered_quantity }}</td>
                        <td class="text-center fw-bold">{{ $item->received_quantity }}</td>
                        <td class="text-center text-success fw-bold">{{ $item->accepted_quantity }}</td>
                        <td class="text-center text-danger fw-bold">{{ $item->rejected_quantity }}</td>
                        <td>
                            <span class="badge bg-label-{{ $statusClass }} me-2">{{ $item->inspection_status ?: 'Pending' }}</span>
                            @if($item->inspectedBy || $item->inspected_at)
                                <div class="small text-muted mt-1">
                                    By: <strong>{{ $item->inspectedBy?->name ?? '--' }}</strong>
                                    ({{ $item->inspected_at?->format('d M, Y H:i') ?? '--' }})
                                </div>
                            @endif
                        </td>
                        <td>
                            @if($item->isApproved())
                                @if($item->assets->isNotEmpty())
                                    <span class="badge bg-label-success d-inline-flex align-items-center">
                                        <i class="bx bx-check-circle me-1"></i> Transferred to Asset
                                    </span>
                                @else
                                    <form method="POST" action="{{ route('quality-inspections.create-item-assets', ['grn' => $grn, 'item' => $item]) }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success" onclick="return confirm('Transfer this approved item to Asset inventory now?')">
                                            <i class="bx bx-transfer me-1"></i> Transfer to Asset
                                        </button>
                                    </form>
                                @endif
                            @elseif($item->isRejectedOrDamaged())
                                <span class="badge bg-label-danger d-inline-flex align-items-center">
                                    <i class="bx bx-revision me-1"></i> Pending Replacement / Vendor Return
                                </span>
                            @elseif($item->isHold())
                                <span class="badge bg-label-info d-inline-flex align-items-center">
                                    <i class="bx bx-time me-1"></i> Under Review / On Hold
                                </span>
                            @else
                                <span class="badge bg-label-secondary">Awaiting Inspection</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if(!empty($item->inspection_status) && !in_array($item->inspection_status, ['Pending', 'Draft', ''], true))
                                <span class="badge bg-label-secondary d-inline-flex align-items-center" title="Item already inspected">
                                    <i class="bx bx-check-double me-1"></i> Inspected
                                </span>
                            @elseif($canPerform)
                                <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#inspectModal-{{ $item->id }}">
                                    <i class="bx bx-edit-alt me-1"></i> Inspect
                                </button>

                                {{-- Individual Item Inspect Modal --}}
                                <div class="modal fade text-start" id="inspectModal-{{ $item->id }}" tabindex="-1" aria-labelledby="inspectModalLabel-{{ $item->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form method="POST" action="{{ route('quality-inspections.inspect-item', [$grn, $item]) }}">
                                                @csrf
                                                <div class="modal-header border-bottom">
                                                    <h5 class="modal-title" id="inspectModalLabel-{{ $item->id }}">
                                                        <i class="bx bx-check-shield text-primary me-1"></i> Inspect Item: {{ $item->poItem?->item_name }}
                                                    </h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body py-3">
                                                    <div class="alert alert-info py-2 mb-3">
                                                        <small><strong>Received Qty:</strong> <span class="modal-received-qty">{{ $item->received_quantity }}</span> {{ $item->purchase_unit }} | <strong>Current Status:</strong> {{ $item->inspection_status ?: 'Pending' }}</small>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Accepted Qty</label>
                                                            <input type="number" class="form-control modal-accepted-qty" name="accepted_quantity" min="0" max="{{ $item->received_quantity }}" value="{{ $item->received_quantity }}">
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label fw-bold">Rejected Qty</label>
                                                            <input type="number" class="form-control modal-rejected-qty" name="rejected_quantity" min="0" max="{{ $item->received_quantity }}" value="0">
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Inspection Status <span class="text-danger">*</span></label>
                                                        <select class="form-select" name="inspection_status" required>
                                                            <option value="Approved" {{ $item->inspection_status === 'Approved' ? 'selected' : '' }}>Approved (Proceed to Store / Asset Creation)</option>
                                                            <option value="Rejected" {{ $item->inspection_status === 'Rejected' ? 'selected' : '' }}>Rejected (Return to Vendor)</option>
                                                            <option value="Damaged" {{ $item->inspection_status === 'Damaged' ? 'selected' : '' }}>Damaged (Pending Replacement)</option>
                                                            <option value="Hold" {{ $item->inspection_status === 'Hold' ? 'selected' : '' }}>Hold (Requires Further Evaluation)</option>
                                                        </select>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label class="form-label fw-bold">Inspection Date</label>
                                                        <input type="datetime-local" class="form-control" name="inspected_at" value="{{ $item->inspected_at?->format('Y-m-d\TH:i') ?? now()->format('Y-m-d\TH:i') }}">
                                                    </div>
                                                    <div class="mb-2">
                                                        <label class="form-label fw-bold">Inspection Remarks / Observations</label>
                                                        <textarea class="form-control" name="remarks" rows="3" placeholder="Enter remarks regarding quality, condition, or defects...">{{ $item->inspection_remarks }}</textarea>
                                                        <small class="text-muted">Mandatory if item is rejected or marked as damaged.</small>
                                                    </div>
                                                </div>
                                                <div class="modal-footer border-top">
                                                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-primary"><i class="bx bx-check me-1"></i> Submit Inspection</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @else
                                <span class="text-muted small">No Access</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@php
    $generatedAssets = $grn->items->flatMap->assets;
@endphp
@if($generatedAssets->isNotEmpty())
<div class="card mb-4">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center bg-light">
        <div>
            <h5 class="mb-0 text-dark"><i class="bx bx-cube text-success me-2"></i> Section — Created Assets from Approved Items</h5>
        </div>
        <span class="badge bg-success">{{ $generatedAssets->count() }} Asset(s) Created</span>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover align-middle">
            <thead class="table-light">
                <tr>
                    <th>Asset Code</th>
                    <th>Asset Name & Item</th>
                    <th>Serial No</th>
                    <th>PO / GRN Reference</th>
                    <th>Vendor / Supplier</th>
                    <th>Purchase Date & Cost</th>
                    <th>Warranty Info</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($generatedAssets as $asset)
                    <tr>
                        <td>
                            <a href="{{ route('asset.show', $asset->id) }}" class="fw-bold text-primary">{{ $asset->asset_code }}</a>
                            <div class="small text-muted">Unit #{{ $asset->source_unit_index }}</div>
                        </td>
                        <td>
                            <strong>{{ $asset->asset_name }}</strong>
                            <div class="small text-muted">Cat: {{ $asset->category?->category_name ?? '--' }} | Type: {{ $asset->assetType?->asset_type_name ?? '--' }}</div>
                        </td>
                        <td>{{ $asset->serial_no ?: '--' }}</td>
                        <td>
                            <div class="small"><strong>GRN:</strong> {{ $grn->grn_no }}</div>
                            @if($grn->purchaseOrder)
                                <div class="small"><strong>PO:</strong> <a href="{{ route('purchase-orders.show', $grn->purchaseOrder) }}">{{ $grn->purchaseOrder->po_no }}</a></div>
                            @endif
                        </td>
                        <td>
                            <strong>{{ $grn->vendor?->name ?? $asset->supplier ?? '--' }}</strong>
                        </td>
                        <td>
                            <div>{{ $asset->purchase_date?->format('d M Y') ?? '--' }}</div>
                            <strong class="text-dark">{{ number_format((float) $asset->purchase_cost, 2) }}</strong>
                        </td>
                        <td>
                            <div class="small">{{ $asset->warrenty_information ?: ($asset->warranties->first()?->notes ?: '--') }}</div>
                            @if($asset->warrenty_expires || $asset->warranties->first()?->warranty_expire_date)
                                <span class="badge bg-label-info small">Exp: {{ ($asset->warrenty_expires ?? $asset->warranties->first()->warranty_expire_date)->format('d M Y') }}</span>
                            @endif
                        </td>
                        <td>
                            {!! $asset->status_label ?? '<span class="badge bg-label-success">Available</span>' !!}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.modal-accepted-qty').forEach(function (acceptedInput) {
            const form = acceptedInput.closest('form');
            const rejectedInput = form.querySelector('.modal-rejected-qty');
            const receivedQty = parseInt(form.querySelector('.modal-received-qty').textContent, 10);
            
            acceptedInput.addEventListener('input', function () {
                let accepted = parseInt(this.value, 10) || 0;
                if (accepted > receivedQty) {
                    accepted = receivedQty;
                    this.value = accepted;
                }
                rejectedInput.value = receivedQty - accepted;
            });
            
            rejectedInput.addEventListener('input', function () {
                let rejected = parseInt(this.value, 10) || 0;
                if (rejected > receivedQty) {
                    rejected = receivedQty;
                    this.value = rejected;
                }
                acceptedInput.value = receivedQty - rejected;
            });
            
            // Auto-update status based on selection?
            // Optionally we can set status to 'Approved' if accepted > 0
        });
    });
</script>
@endpush
