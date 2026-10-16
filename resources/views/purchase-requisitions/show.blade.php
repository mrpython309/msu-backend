@extends('layouts/contentNavbarLayout')

@section('title', 'Purchase Requisition')

@section('content')
@php
    $indent = $purchaseRequisition->indent;
    $items = $indent?->items ?? collect();
    $status = $purchaseRequisition->statusEnum();
    $storeName = $purchaseRequisition->requisitionStore->department_name
        ?? $indent?->toStore?->department_name
        ?? $indent?->fromStore?->department_name
        ?? '--';
    $linkedGrns = $purchaseRequisition->purchaseOrders->flatMap(fn ($po) => $po->grns);
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="mb-0">PR {{ $purchaseRequisition->pr_no }}</h4>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @if ($purchaseRequisition->statusEnum()?->isAwaitingPurchaseOfficer())
            @canany(['purchase-requisition.process', 'purchase-requisitions:process', 'purchase-requisition.proceed-to-rfq', 'purchase-requisitions:proceed-to-rfq', 'rfq.create', 'rfqs:create'])
                @if ($purchaseRequisition->allowsProcurement() && !$purchaseRequisition->rfqs()->exists())
                    <button type="button" class="btn btn-success btn-sm btn-proceed-rfq"
                        data-action="{{ route('purchase-requisitions.process', $purchaseRequisition) }}"
                        data-pr="{{ $purchaseRequisition->pr_no }}">
                        <i class="bx bx-file me-1"></i> Proceed to RFQ
                    </button>
                @endif
            @endcanany
            @canany(['purchase-requisition.process', 'purchase-requisitions:process', 'purchase-requisition.transfer-from-store', 'purchase-requisitions:transfer-from-store'])
                @if ($purchaseRequisition->allowsProcurement())
                    <button type="button" class="btn btn-info btn-sm" id="btn-transfer-store"
                        data-action="{{ route('purchase-requisitions.transfer', $purchaseRequisition) }}"
                        data-check-url="{{ route('purchase-requisitions.transfer-availability', $purchaseRequisition) }}">
                        <i class="bx bx-transfer me-1"></i> Transfer from Store
                    </button>
                @endif
            @endcanany
            @canany(['purchase-requisition.process', 'purchase-requisitions:process', 'purchase-requisition.close', 'purchase-requisition.manual-close', 'purchase-requisitions:manual-close'])
                @if ($purchaseRequisition->canManualClose())
                    <button type="button" class="btn btn-warning btn-sm" id="btn-manual-close"
                        data-action="{{ route('purchase-requisitions.close', $purchaseRequisition) }}">
                        <i class="bx bx-lock me-1"></i> Manual Close
                    </button>
                @endif
            @endcanany
        @endif
        <a class="btn btn-secondary btn-sm" href="{{ route('purchase-requisitions.index') }}">Back</a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">PR Information</h5>
    </div>
    <div class="card-body row g-3">
        <div class="col-md-3">
            <small class="text-muted d-block">PR No.</small>
            <strong>{{ $purchaseRequisition->pr_no }}</strong>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">Status</small>
            <span class="badge bg-label-{{ $status?->badgeClass() ?? 'secondary' }}">{{ $status?->label() ?? $purchaseRequisition->status }}</span>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">Created By</small>
            <strong>{{ $purchaseRequisition->createdByUser->name ?? ($indent?->requestedBy?->name ?? '--') }}</strong>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">Created Date &amp; Time</small>
            <strong>{{ $purchaseRequisition->created_at?->format('d M Y h:i A') ?? '--' }}</strong>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">Required Date</small>
            <strong>{{ $purchaseRequisition->required_date?->format('d M Y') ?? ($indent?->required_date?->format('d M Y') ?? '--') }}</strong>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">Processed By</small>
            <strong>{{ $purchaseRequisition->purchaseOfficer->name ?? '--' }}</strong>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">Processed At</small>
            <strong>{{ $purchaseRequisition->processed_at?->format('d M Y h:i A') ?? '--' }}</strong>
        </div>
        @if ($purchaseRequisition->closed_at)
            <div class="col-md-3">
                <small class="text-muted d-block">Closed By</small>
                <strong>{{ $purchaseRequisition->closedByUser->name ?? '--' }}</strong>
            </div>
            <div class="col-md-3">
                <small class="text-muted d-block">Closed Date &amp; Time</small>
                <strong>{{ $purchaseRequisition->closed_at->format('d M Y h:i A') }}</strong>
            </div>
            <div class="col-md-6">
                <small class="text-muted d-block">Close Reason</small>
                <strong>{{ $purchaseRequisition->close_reason ?? '--' }}</strong>
            </div>
        @endif
        @if ($purchaseRequisition->rejected_reason)
            <div class="col-12">
                <small class="text-muted d-block">Rejected Reason</small>
                <strong>{{ $purchaseRequisition->rejected_reason }}</strong>
            </div>
        @endif
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Indent Information</h5>
    </div>
    <div class="card-body row g-3">
        <div class="col-md-3">
            <small class="text-muted d-block">Indent No.</small>
            <strong>{{ $indent?->indent_no ?? '--' }}</strong>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">Department</small>
            <strong>{{ $indent?->department->department_name ?? '--' }}</strong>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">From Store</small>
            <strong>{{ $indent?->fromStore->department_name ?? $indent?->department->department_name ?? '--' }}</strong>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">To Store</small>
            <strong>{{ $indent?->toStore->department_name ?? '--' }}</strong>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">Requested By</small>
            <strong>{{ $indent?->requestedBy->name ?? '--' }}</strong>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">Priority</small>
            <strong>{{ $indent?->priority ?? '--' }}</strong>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">Indent Status</small>
            <strong>{{ $indent?->status?->value ?? '--' }}</strong>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">Request Date</small>
            <strong>{{ ($indent?->requested_at ?? $indent?->created_at)?->format('d M Y h:i A') ?? ($indent?->request_date?->format('d M Y') ?? '--') }}</strong>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">Required Date</small>
            <strong>{{ $indent?->required_date?->format('d M Y') ?? '--' }}</strong>
        </div>
        <div class="col-12">
            <small class="text-muted d-block">Purpose / Reason</small>
            <strong>{{ $indent?->purpose ?: '--' }}</strong>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Store</h5>
    </div>
    <div class="card-body row g-3">
        <div class="col-md-4">
            <small class="text-muted d-block">Store</small>
            <strong>{{ $storeName }}</strong>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Requested Items</h5>
    </div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Item</th>
                    <th>Category</th>
                    <th>Qty</th>
                    <th>Unit</th>
                    <th>Est. Price</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $index => $item)
                    <tr>
                        <td>{{ $index + 1 }}</td>
                        <td>{{ $item->item_name }}</td>
                        <td>{{ $item->category->category_name ?? '--' }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ $item->unit }}</td>
                        <td>{{ number_format((float) $item->estimated_price, 2) }}</td>
                        <td>{{ number_format((float) $item->total_amount, 2) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center">No items</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Procurement Status</h5>
    </div>
    <div class="card-body">
        @if ($purchaseRequisition->allowsProcurement())
            <span class="badge bg-label-success">Open for procurement</span>
            <p class="text-muted mb-0 mt-2 small">RFQ and Purchase Order creation is allowed while this PR is open.</p>
        @else
            <span class="badge bg-label-secondary">Procurement closed</span>
            <p class="text-muted mb-0 mt-2 small">RFQ and Purchase Order creation is blocked for this PR.</p>
        @endif
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header"><h5 class="mb-0">Linked RFQ</h5></div>
            <div class="card-body">
                @forelse ($purchaseRequisition->rfqs as $rfq)
                    <div class="mb-2">
                        <a href="{{ route('rfqs.show', $rfq) }}">{{ $rfq->rfq_no }}</a>
                        <span class="badge bg-label-info ms-1">{{ $rfq->status->value ?? $rfq->status }}</span>
                    </div>
                @empty
                    <p class="text-muted mb-0">No RFQ linked yet.</p>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header"><h5 class="mb-0">Linked Purchase Order</h5></div>
            <div class="card-body">
                @forelse ($purchaseRequisition->purchaseOrders as $po)
                    <div class="mb-2">
                        <a href="{{ route('purchase-orders.show', $po) }}">{{ $po->po_no }}</a>
                        <span class="badge bg-label-primary ms-1">{{ $po->status->value ?? $po->status }}</span>
                    </div>
                @empty
                    <p class="text-muted mb-0">No Purchase Order linked yet.</p>
                @endforelse
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-header"><h5 class="mb-0">Linked GRN</h5></div>
            <div class="card-body">
                @forelse ($linkedGrns as $grn)
                    <div class="mb-2">
                        <a href="{{ route('grns.show', $grn) }}">{{ $grn->grn_no }}</a>
                        <span class="badge bg-label-success ms-1">{{ $grn->status->value ?? $grn->status }}</span>
                    </div>
                @empty
                    <p class="text-muted mb-0">No GRN linked yet.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>

<form id="manual-close-form" method="POST" class="d-none">
    @csrf
    <input type="hidden" name="close_reason" id="manual-close-reason">
</form>
<form id="transfer-store-form" method="POST" class="d-none">
    @csrf
</form>
<form id="proceed-rfq-form" method="POST" class="d-none">
    @csrf
</form>
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    @if (session('success'))
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Success',
                text: @json(session('success')),
                icon: 'success',
                confirmButtonText: 'OK',
                customClass: { confirmButton: 'btn btn-primary' },
                buttonsStyling: false
            });
        } else {
            alert(@json(session('success')));
        }
    @endif

    const btnClose = document.getElementById('btn-manual-close');
    if (btnClose) {
        btnClose.addEventListener('click', function () {
            Swal.fire({
                title: 'Close Purchase Requisition?',
                html: '<p class="mb-2">RFQ and PO creation will be blocked once this PR is closed.</p>',
                input: 'textarea',
                inputLabel: 'Close Reason (required)',
                inputPlaceholder: 'Enter reason for closing this PR...',
                inputAttributes: { maxlength: 2000, rows: 4 },
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ff9f43',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, close it',
                cancelButtonText: 'Cancel',
                preConfirm: function (value) {
                    if (!value || !value.trim()) {
                        Swal.showValidationMessage('Close reason is mandatory.');
                        return false;
                    }
                    return value.trim();
                }
            }).then(function (result) {
                if (!result.isConfirmed) {
                    return;
                }
                const form = document.getElementById('manual-close-form');
                document.getElementById('manual-close-reason').value = result.value;
                form.action = btnClose.dataset.action;
                form.submit();
            });
        });
    }

    const btnTransfer = document.getElementById('btn-transfer-store');
    if (btnTransfer) {
        btnTransfer.addEventListener('click', function () {
            const checkUrl = btnTransfer.dataset.checkUrl;
            const action = btnTransfer.dataset.action;

            const confirmAndSubmit = function () {
                Swal.fire({
                    title: 'Transfer Items from Store?',
                    html: '<p class="mb-0">This will transfer requested items from available store stock/assets and <strong>stop the procurement process</strong> at the Purchase Requisition stage.</p>',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#00cfdd',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, transfer from store',
                    cancelButtonText: 'Cancel'
                }).then(function (result) {
                    if (!result.isConfirmed) {
                        return;
                    }
                    const form = document.getElementById('transfer-store-form');
                    form.action = action;
                    form.submit();
                });
            };

            if (!checkUrl) {
                confirmAndSubmit();
                return;
            }

            fetch(checkUrl, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
                .then(function (response) { return response.json(); })
                .then(function (data) {
                    if (!data.available) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Item is not in assets',
                            text: 'Item is not in assets',
                            confirmButtonText: 'OK'
                        });
                        return;
                    }
                    confirmAndSubmit();
                })
                .catch(function () {
                    Swal.fire({
                        icon: 'error',
                        title: 'Unable to check assets',
                        text: 'Could not verify item availability. Please try again.',
                        confirmButtonText: 'OK'
                    });
                });
        });
    }

    document.querySelectorAll('.btn-proceed-rfq').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const action = btn.dataset.action;
            const prNo = btn.dataset.pr || 'this PR';

            Swal.fire({
                title: 'Proceed to RFQ?',
                html: '<p class="mb-0">This will mark <strong>' + prNo + '</strong> to proceed for RFQ processing.</p>',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#28a745',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, proceed to RFQ',
                cancelButtonText: 'Cancel'
            }).then(function (result) {
                if (!result.isConfirmed) {
                    return;
                }
                const form = document.getElementById('proceed-rfq-form');
                form.action = action;
                form.submit();
            });
        });
    });

    @if(session('error') && str_contains(strtolower(session('error')), 'not in assets'))
    Swal.fire({
        icon: 'warning',
        title: 'Item is not in assets',
        text: 'Item is not in assets',
        confirmButtonText: 'OK'
    });
    @endif
});
</script>
@endsection
