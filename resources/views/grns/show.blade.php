@extends('layouts/contentNavbarLayout')

@section('title', 'GRN Details')

@section('page-style')
<style>
    /* Hide number input arrows/spinners for accepted quantities */
    .accepted-qty::-webkit-outer-spin-button,
    .accepted-qty::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    .accepted-qty {
        -moz-appearance: textfield;
    }
</style>
@endsection

@section('content')
@php
    $status = $grn->statusEnum();
    $statusValue = $status?->value ?? ($grn->status->value ?? $grn->status);
    $isApprovable = in_array($status, [
        \App\Enums\Procurement\GrnStatus::Draft,
        \App\Enums\Procurement\GrnStatus::PendingInspection,
        \App\Enums\Procurement\GrnStatus::UnderInspection,
    ], true);
    $generatedAssets = $grn->items->flatMap->assets;
    $po = $grn->purchaseOrder;
    $poTypeLabel = $grn->po_type === 'with_pr' ? 'With PR' : ($grn->po_type === 'without_pr' ? 'Without PR' : '--');
@endphp

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">{{ $grn->grn_no }}</h4>
        <span class="badge bg-label-{{ $status?->badgeClass() ?? 'secondary' }}">{{ $statusValue }}</span>
    </div>
    <div class="d-flex gap-2 align-items-center">
        @if($grn->items->contains(fn($i) => $i->isApproved()) && $generatedAssets->isEmpty())
            @if(auth()->user()?->canAny(['grns:approve', 'grn.approve', 'quality-inspection.perform', 'quality-inspections:perform', 'asset:create', 'asset.create']) || auth()->user()?->hasRole(['Quality Inspector', 'Quality Control', 'Store Incharge', 'Store Officer', 'Store Manager', 'Purchase Officer', 'Admin', 'superadmin', 'Super Admin']))
                <form method="POST" action="{{ route('grns.create-assets', $grn) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success btn-sm" onclick="return confirm('Transfer approved items to Asset records now? Rejected/Damaged items will be excluded.')">
                        <i class="bx bx-transfer me-1"></i> Transfer to Asset
                    </button>
                </form>
            @endif
        @endif
        <a class="btn btn-secondary btn-sm" href="{{ route('grns.index') }}">Back</a>
    </div>
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

{{-- Section 1 --}}
<div class="card mb-4">
    <div class="card-header"><h5 class="mb-0">Section 1 — Basic Information</h5></div>
    <div class="card-body row g-3">
        <div class="col-md-3"><small class="text-muted d-block">GRN Number</small><strong>{{ $grn->grn_no }}</strong></div>
        <div class="col-md-3"><small class="text-muted d-block">Purchase Order</small><strong>@if($po)<a href="{{ route('purchase-orders.show', $po) }}">{{ $po->po_no }}</a>@else -- @endif</strong></div>
        <div class="col-md-3"><small class="text-muted d-block">Supplier Name</small><strong>{{ $grn->vendor->name ?? '--' }}</strong></div>
        <div class="col-md-3"><small class="text-muted d-block">PO Type</small><strong>{{ $poTypeLabel }}</strong></div>
        <div class="col-md-3"><small class="text-muted d-block">GRN Department</small><strong>{{ $grn->warehouse ?? '--' }}</strong></div>
        <div class="col-md-3"><small class="text-muted d-block">GRN Date</small><strong>{{ $grn->received_date?->format('d M Y') ?? '--' }}</strong></div>
        <div class="col-md-3"><small class="text-muted d-block">Status</small><strong>{{ $statusValue }}</strong></div>
        <div class="col-12 mt-4">
            <h6 class="text-uppercase text-muted mb-2" style="font-size:.75rem;letter-spacing:.04em;">Delivery &amp; Inspection Personnel</h6>
            <hr class="mt-0 mb-3">
        </div>
        <div class="col-md-4"><small class="text-muted d-block">Delivered By</small><strong>{{ $grn->delivered_by ?: '--' }}</strong></div>
        <div class="col-md-4"><small class="text-muted d-block">Delivered By Ph No.</small><strong>{{ $grn->delivered_by_phone ?: '--' }}</strong></div>
        <div class="col-md-4"><small class="text-muted d-block">Vehicle No.</small><strong>{{ $grn->vehicle_no ?: '--' }}</strong></div>
        <div class="col-md-4"><small class="text-muted d-block">Received By</small><strong>{{ $grn->receivedBy->name ?? '--' }}</strong></div>
        <div class="col-md-4"><small class="text-muted d-block">Received By Ph No.</small><strong>{{ $grn->received_by_phone ?: '--' }}</strong></div>
        <div class="col-md-4"><small class="text-muted d-block">Inspected By</small><strong>{{ $grn->inspectedBy->name ?? 'Pending' }}</strong></div>
    </div>
</div>

{{-- Section 2 --}}
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0">Section 2 — Vendor Documents</h5>
    </div>
    <div class="card-body">
        <div class="row g-3 mb-3">
            <div class="col-md-6">
                <div class="border rounded p-3 h-100">
                    <h6 class="text-uppercase text-muted mb-3" style="font-size:.75rem;letter-spacing:.04em;">Bill / Invoice</h6>
                    <div class="row g-3">
                        <div class="col-sm-4">
                            <small class="text-muted d-block">Bill Number</small>
                            <strong>{{ $grn->invoice_number ?: '--' }}</strong>
                        </div>
                        <div class="col-sm-4">
                            <small class="text-muted d-block">Bill Date</small>
                            <strong>{{ $grn->bill_date?->format('d M Y') ?? ($grn->invoice_date?->format('d M Y') ?? '--') }}</strong>
                        </div>
                        <div class="col-sm-4">
                            <small class="text-muted d-block">Bill Amount</small>
                            <strong>{{ $grn->bill_amount !== null ? number_format((float) $grn->bill_amount, 2) : '--' }}</strong>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="border rounded p-3 h-100">
                    <h6 class="text-uppercase text-muted mb-3" style="font-size:.75rem;letter-spacing:.04em;">Delivery Challan</h6>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Challan Number</small>
                            <strong>{{ $grn->challan_number ?: '--' }}</strong>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Challan Date</small>
                            <strong>{{ $grn->challan_date?->format('d M Y') ?? '--' }}</strong>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="border rounded p-3 h-100">
                    <h6 class="text-uppercase text-muted mb-3" style="font-size:.75rem;letter-spacing:.04em;">Delivery Memo</h6>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Memo Number</small>
                            <strong>{{ $grn->delivery_memo_no ?: '--' }}</strong>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Memo Date</small>
                            <strong>{{ $grn->delivery_memo_date?->format('d M Y') ?? '--' }}</strong>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="border rounded p-3 h-100">
                    <h6 class="text-uppercase text-muted mb-3" style="font-size:.75rem;letter-spacing:.04em;">Gate Entry</h6>
                    <div class="row g-3">
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Entry Number</small>
                            <strong>{{ $grn->gate_entry_no ?: '--' }}</strong>
                        </div>
                        <div class="col-sm-6">
                            <small class="text-muted d-block">Entry Date &amp; Time</small>
                            <strong>{{ $grn->gate_entry_date?->format('d M Y h:i A') ?? '--' }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="border rounded p-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="text-uppercase text-muted mb-0" style="font-size:.75rem;letter-spacing:.04em;">Uploaded Files</h6>
                <span class="badge bg-label-secondary">{{ $grn->documents->count() }} file(s)</span>
            </div>
            @if ($grn->documents->isNotEmpty())
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead>
                            <tr>
                                <th style="width:28%">Type</th>
                                <th>File Name</th>
                                <th style="width:12%">Size</th>
                                <th style="width:160px" class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($grn->documents as $doc)
                                <tr>
                                    <td>
                                        <span class="badge bg-label-{{ $doc->document_type === 'photo' ? 'info' : ($doc->document_type === 'invoice' ? 'primary' : 'secondary') }}">
                                            {{ $doc->typeLabel() }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bx {{ str_starts_with((string) $doc->mime_type, 'image/') ? 'bx-image' : 'bx-file' }} text-muted"></i>
                                            <span class="text-break">{{ $doc->fileName() }}</span>
                                        </div>
                                    </td>
                                    <td class="text-muted">{{ $doc->humanFileSize() }}</td>
                                    <td class="text-end">
                                        <a class="btn btn-sm btn-outline-primary" href="{{ route('grns.documents.show', [$grn, $doc]) }}" target="_blank">
                                            <i class="bx bx-show"></i> View
                                        </a>
                                        <a class="btn btn-sm btn-outline-secondary" href="{{ route('grns.documents.show', [$grn, $doc, 'download']) }}">
                                            <i class="bx bx-download"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-muted small mb-0">No files uploaded for this GRN.</div>
            @endif
        </div>
    </div>
</div>

{{-- Section 3 + 5 + 6 --}}
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Section 3 — Item Details @if($isApprovable) &amp; Inspection @endif</h5>
        @php
            $canPerformInspection = auth()->user()?->can('quality-inspection.view')
                || auth()->user()?->can('quality-inspections:view')
                || auth()->user()?->can('quality-inspection.perform')
                || auth()->user()?->can('quality-inspections:perform')
                || auth()->user()?->can('grn.inspect')
                || auth()->user()?->can('grns:approve');
            $allInspected = $grn->items->count() > 0 && $grn->items->every(fn($i) => !empty($i->inspection_status) && !in_array($i->inspection_status, ['Pending', 'Draft', ''], true));
        @endphp
        @if ($canPerformInspection && $grn->status !== 'Draft')
            @if ($allInspected)
                <a href="{{ route('quality-inspections.show', $grn) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bx bx-show me-1"></i> View Inspected Items
                </a>
            @else
                <a href="{{ route('quality-inspections.show', $grn) }}" class="btn btn-primary btn-sm">
                    <i class="bx bx-check-shield me-1"></i> Perform Quality Inspection
                </a>
            @endif
        @endif
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Code</th>
                            <th>HSN</th>
                            <th>Unit</th>
                            <th>Ordered</th>
                            <th>Received</th>
                            <th>Accepted</th>
                            <th>Inspection Status</th>
                            <th>Rate</th>
                            <th>Batch</th>
                            <th>Serials</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($grn->items as $i => $item)
                            @php
                                $received = (int) $item->received_quantity;
                                $rejected = (int) $item->rejected_quantity;
                                $accepted = (int) $item->accepted_quantity ?: max(0, $received - $rejected);
                            @endphp
                            <tr data-received="{{ $received }}">
                                <td>
                                    {{ $item->poItem->item_name ?? 'Item' }}
                                    <div class="small text-muted">{{ $item->poItem?->resolvedItemType()?->label() }}</div>
                                    <input type="hidden" name="items[{{ $i }}][grn_item_id]" value="{{ $item->id }}">
                                    <input type="hidden" name="items[{{ $i }}][received_quantity]" value="{{ $received }}" class="received-hidden">
                                </td>
                                <td>{{ $item->item_code ?? $item->poItem?->item_code ?? '--' }}</td>
                                <td>{{ $item->hsn_code ?? $item->poItem?->hsn_code ?? '--' }}</td>
                                <td>{{ $item->purchase_unit ?? $item->poItem?->unit ?? 'Nos' }}</td>
                                <td>{{ $item->ordered_quantity }}</td>
                                <td>{{ $received }}</td>
                                <input type="hidden" name="items[{{ $i }}][rejected_quantity]" value="0">
                                <td>
                                    <input class="form-control accepted-qty" type="number" min="0" max="{{ $received }}"
                                           name="items[{{ $i }}][accepted_quantity]" value="{{ $accepted }}"
                                           {{ $isApprovable ? '' : 'readonly' }}>
                                </td>
                                <td>
                                    <span class="badge bg-label-{{ $item->statusBadgeClass() }}">{{ $item->inspection_status ?: 'Pending' }}</span>
                                </td>
                                <td>{{ number_format((float) ($item->unit_rate ?? $item->poItem?->price ?? 0), 2) }}</td>
                                <td>{{ $item->batch_number ?? '--' }}</td>
                                <td><small>{{ is_array($item->serial_numbers) ? implode(', ', $item->serial_numbers) : '--' }}</small></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
        </div>
    </div>
</div>

@if ($generatedAssets->isNotEmpty())
<div class="card mb-4">
    <div class="card-header border-bottom d-flex justify-content-between align-items-center bg-light">
        <div>
            <h5 class="mb-0 text-dark"><i class="bx bx-cube text-success me-2"></i> Created Assets from Approved Items</h5>
        </div>
        <span class="badge bg-success">{{ $generatedAssets->count() }} Asset(s) Created</span>
    </div>
    <div class="table-responsive text-nowrap">
        <table class="table table-hover align-middle mb-0">
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
                @foreach ($generatedAssets as $asset)
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
                            <div class="small"><strong>GRN:</strong> #{{ $grn->grn_no }}</div>
                            @if($grn->purchaseOrder)
                                <div class="small"><strong>PO:</strong> <a href="{{ route('purchase-orders.show', $grn->purchaseOrder) }}">#{{ $grn->purchaseOrder->po_no }}</a></div>
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

@if ($grn->approvedBy || $grn->accepted_at)
<div class="card mb-4">
    <div class="card-header"><h5 class="mb-0">Section 4 — Inspection Summary</h5></div>
    <div class="card-body row g-3">
        @php
            $displayInspectionStatus = !empty($grn->inspection_status) && strtolower($grn->inspection_status) !== 'pending'
                ? ucfirst($grn->inspection_status)
                : ($grn->items->every(fn($i) => $i->isApproved()) && $grn->items->count() > 0 ? 'Approved' : ($grn->items->contains(fn($i) => $i->isApproved()) ? 'Partially Approved' : ($grn->items->contains(fn($i) => $i->isRejectedOrDamaged()) ? 'Rejected' : ucfirst($grn->inspection_status ?? $grn->status))));
        @endphp
        <div class="col-md-3"><small class="text-muted d-block">Inspection Status</small><strong class="badge bg-label-primary fs-6">{{ $displayInspectionStatus }}</strong></div>
        <div class="col-md-3"><small class="text-muted d-block">Approved By</small><strong>{{ $grn->approvedBy->name ?? $grn->inspectedBy->name ?? '--' }}</strong></div>
        <div class="col-md-3"><small class="text-muted d-block">Approval Date</small><strong>{{ $grn->accepted_at?->format('d M Y h:i A') ?? '--' }}</strong></div>
        <div class="col-md-12"><small class="text-muted d-block">Inspection Remarks</small><strong>{{ $grn->inspection_remarks ?: '--' }}</strong></div>
    </div>
</div>
@endif

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center py-3"
         role="button"
         data-bs-toggle="collapse"
         data-bs-target="#grnAuditTrailCollapse"
         aria-expanded="true"
         aria-controls="grnAuditTrailCollapse"
         style="cursor: pointer;">
        <h5 class="mb-0">Approval Timeline &amp; Audit Trail</h5>
        <i class="bx bx-chevron-up fs-4 text-muted grn-audit-toggle-icon"></i>
    </div>
    <div class="collapse show" id="grnAuditTrailCollapse">
        <div class="card-body">
            @php
                $timelineLogs = $grn->activityLogs->isNotEmpty() ? $grn->activityLogs : $grn->histories;
            @endphp
            @if ($timelineLogs->isNotEmpty())
                <div class="timeline mb-0">
                    @foreach ($timelineLogs as $history)
                        <div class="border-start border-2 ps-3 mb-3">
                            <strong>{{ ucwords(str_replace('_', ' ', $history->action)) }}</strong>
                            <div class="small text-muted">{{ $history->user->name ?? 'System' }} · {{ $history->created_at?->format('d M Y h:i A') }}</div>
                            @if ($history->comments)<div class="small">{{ $history->comments }}</div>@endif
                            @if ($history->from_status && $history->to_status && $history->from_status !== $history->to_status)
                                <div class="small text-muted">{{ $history->from_status }} → {{ $history->to_status }}</div>
                            @elseif ($history->to_status && (!$history->from_status || $history->from_status === $history->to_status))
                                <div class="small text-muted">Status: {{ $history->to_status }}</div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-muted mb-0">No approval timeline yet.</p>
            @endif
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const auditCollapse = document.getElementById('grnAuditTrailCollapse');
    const auditIcon = document.querySelector('.grn-audit-toggle-icon');
    if (auditCollapse && auditIcon) {
        auditCollapse.addEventListener('show.bs.collapse', function () {
            auditIcon.classList.replace('bx-chevron-down', 'bx-chevron-up');
        });
        auditCollapse.addEventListener('hide.bs.collapse', function () {
            auditIcon.classList.replace('bx-chevron-up', 'bx-chevron-down');
        });
    }

    function recalcApproveRow(row) {
        const received = parseInt(row.dataset.received || '0', 10);
        const rejectInput = row.querySelector('.reject-qty');
        const acceptedInput = row.querySelector('.accepted-qty');
        const stockAfterCell = row.querySelector('.stock-after');
        const stockBefore = parseFloat(row.querySelector('td:nth-child(12)')?.textContent || '0');

        if (!rejectInput || !acceptedInput) return;

        let rejected = parseInt(rejectInput.value || '0', 10);
        if (rejected > received) rejected = received;
        rejectInput.value = rejected;

        let accepted = parseInt(acceptedInput.value || '0', 10);
        if (accepted > received) accepted = received;
        acceptedInput.value = accepted;

        if (stockAfterCell) {
            stockAfterCell.textContent = (stockBefore + accepted).toFixed(3);
        }
    }

    document.querySelectorAll('tbody tr[data-received]').forEach(function (row) {
        row.querySelectorAll('.reject-qty, .accepted-qty').forEach(function (input) {
            input.addEventListener('input', function () { recalcApproveRow(row); });
        });
    });
});
</script>
@endsection
