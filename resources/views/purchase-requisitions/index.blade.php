@extends('layouts/contentNavbarLayout')

@section('title', 'Purchase Requisitions')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="mb-0">Purchase Requisitions</h4>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @php
            $exportQuery = array_filter(request()->only(['search', 'status', 'store_id', 'date_from', 'date_to']));
        @endphp
        <a href="{{ route('purchase-requisitions.index', array_merge($exportQuery, ['export' => 'excel'])) }}" class="btn btn-outline-success btn-sm">
            <i class="bx bx-export me-1"></i> Export Excel
        </a>
        <a href="{{ route('purchase-requisitions.index', array_merge($exportQuery, ['export' => 'pdf'])) }}" class="btn btn-outline-danger btn-sm">
            <i class="bx bxs-file-pdf me-1"></i> Export PDF
        </a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header pb-0">
        <h5 class="mb-0">Search &amp; Filters</h5>
    </div>
    <div class="card-body pt-2">
        <form method="GET" action="{{ route('purchase-requisitions.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" placeholder="PR No., Indent No." value="{{ $filters['search'] ?? '' }}">
            </div>
            <div class="col-md-2">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    @foreach ($statusOptions as $status)
                        <option value="{{ $status }}" {{ ($filters['status'] ?? '') === $status ? 'selected' : '' }}>{{ $status }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label">Store</label>
                <select name="store_id" class="form-select">
                    <option value="">All Stores</option>
                    @foreach ($stores as $id => $name)
                        <option value="{{ $id }}" {{ (string) ($filters['store_id'] ?? '') === (string) $id ? 'selected' : '' }}>{{ $name }}</option>
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
            <div class="col-md-1 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-100" title="Search"><i class="bx bx-search"></i></button>
            </div>
            <div class="col-12">
                <a href="{{ route('purchase-requisitions.index') }}" class="btn btn-secondary btn-sm">Reset Filters</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">All Purchase Requisitions</h5>
        </div>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table">
            <thead>
                <tr>
                    <th class="text-nowrap">#</th>
                    <th>PR No.</th>
                    <th>Indent No.</th>
                    <th>Status</th>
                    <th>Created By</th>
                    <th>Created Date &amp; Time</th>
                    <th>Store</th>
                    <th>Remarks</th>
                    <th>Processed By</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse ($requisitions as $pr)
                    @php
                        $status = $pr->statusEnum();
                        $storeName = $pr->requisitionStore->department_name
                            ?? $pr->indent?->toStore?->department_name
                            ?? $pr->indent?->fromStore?->department_name
                            ?? '--';
                    @endphp
                    <tr>
                        <td class="text-nowrap text-muted">
                            {{ ($requisitions->currentPage() - 1) * $requisitions->perPage() + $loop->iteration }}
                        </td>
                        <td><strong>{{ $pr->pr_no }}</strong></td>
                        <td>{{ $pr->indent->indent_no ?? '--' }}</td>
                        <td>
                            <span class="badge bg-label-{{ $status?->badgeClass() ?? 'secondary' }}">
                                {{ $status?->label() ?? $pr->status }}
                            </span>
                        </td>
                        <td>{{ $pr->createdByUser->name ?? ($pr->indent->requestedBy->name ?? '--') }}</td>
                        <td>{{ $pr->created_at?->format('d M Y h:i A') ?? '--' }}</td>
                        <td>{{ $storeName }}</td>
                        @php
                            $purpose = $pr->indent?->purpose;
                        @endphp
                        <td class="text-truncate" style="max-width: 160px;" title="{{ $purpose }}">{{ $purpose ?: '--' }}</td>
                        <td>{{ $pr->purchaseOfficer->name ?? '--' }}</td>
                        <td>
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="{{ route('purchase-requisitions.show', $pr) }}">
                                        <i class="bx bx-show me-1"></i> View
                                    </a>
                                    @if ($pr->statusEnum()?->isAwaitingPurchaseOfficer())
                                        @canany(['purchase-requisition.process', 'purchase-requisitions:process', 'purchase-requisition.proceed-to-rfq', 'purchase-requisitions:proceed-to-rfq', 'rfq.create', 'rfqs:create'])
                                            @if ($pr->allowsProcurement() && !$pr->rfqs()->exists())
                                                <button type="button" class="dropdown-item text-success btn-proceed-rfq"
                                                    data-action="{{ route('purchase-requisitions.process', $pr) }}"
                                                    data-pr="{{ $pr->pr_no }}">
                                                    <i class="bx bx-file me-1"></i> Proceed to RFQ
                                                </button>
                                            @endif
                                        @endcanany
                                        @canany(['purchase-requisition.process', 'purchase-requisitions:process', 'purchase-requisition.transfer-from-store', 'purchase-requisitions:transfer-from-store'])
                                            @if ($pr->allowsProcurement())
                                                <button type="button" class="dropdown-item text-info btn-transfer-store"
                                                    data-action="{{ route('purchase-requisitions.transfer', $pr) }}"
                                                    data-check-url="{{ route('purchase-requisitions.transfer-availability', $pr) }}"
                                                    data-pr="{{ $pr->pr_no }}">
                                                    <i class="bx bx-transfer me-1"></i> Transfer from Store
                                                </button>
                                            @endif
                                        @endcanany
                                        @canany(['purchase-requisition.process', 'purchase-requisitions:process', 'purchase-requisition.close', 'purchase-requisition.manual-close', 'purchase-requisitions:manual-close'])
                                            @if ($pr->canManualClose())
                                                <button type="button" class="dropdown-item text-warning btn-manual-close"
                                                    data-action="{{ route('purchase-requisitions.close', $pr) }}"
                                                    data-pr="{{ $pr->pr_no }}">
                                                    <i class="bx bx-lock me-1"></i> Manual Close
                                                </button>
                                            @endif
                                        @endcanany
                                    @endif
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="10" class="text-center py-4">No purchase requisitions found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if ($requisitions->hasPages())
        <div class="card-footer">{{ $requisitions->links() }}</div>
    @endif
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

    document.querySelectorAll('.btn-manual-close').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const action = btn.dataset.action;
            const prNo = btn.dataset.pr;

            Swal.fire({
                title: 'Close Purchase Requisition?',
                html: '<p class="mb-2">You are about to manually close <strong>' + prNo + '</strong>. RFQ and PO creation will be blocked.</p>',
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
                form.action = action;
                form.submit();
            });
        });
    });

    document.querySelectorAll('.btn-transfer-store').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const action = btn.dataset.action;
            const checkUrl = btn.dataset.checkUrl;
            const prNo = btn.dataset.pr;

            const confirmAndSubmit = function () {
                Swal.fire({
                    title: 'Transfer Items from Store?',
                    html: '<p class="mb-0">This will transfer requested items for <strong>' + prNo + '</strong> from available store stock/assets and <strong>stop the procurement process</strong> at the Purchase Requisition stage.</p>',
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
    });

    document.querySelectorAll('.btn-proceed-rfq').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const action = btn.dataset.action;
            const prNo = btn.dataset.pr;

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
