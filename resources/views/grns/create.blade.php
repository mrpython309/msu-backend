@extends('layouts/contentNavbarLayout')

@section('title', 'Create GRN')

@section('content')
@php
    $indent = $purchaseOrder->purchaseRequisition?->indent;
    $departmentName = $purchaseOrder->department_name ?? $indent?->department?->department_name;
    $poTypeLabel = $purchaseOrder->po_type === 'with_pr' ? 'With PR' : ($purchaseOrder->po_type === 'without_pr' ? 'Without PR' : '--');
    $costCenter = $purchaseOrder->purchaseRequisition?->fan_no;
@endphp

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Create Goods Receipt Note</h4>
    </div>
    <a class="btn btn-secondary btn-sm" href="{{ route('grns.index') }}">Back to GRNs</a>
</div>

@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

@if ($errors->any())
    <div class="alert alert-danger">
        <strong>Please fix the following errors:</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" enctype="multipart/form-data" action="{{ route('grns.store', $purchaseOrder) }}" id="grn-create-form">
    @csrf

    {{-- Section 1: Basic Information --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Section 1 — Basic Information</h5>
        </div>
        <div class="card-body row g-3">
            <div class="col-md-3">
                <label class="form-label">GRN Number</label>
                <input class="form-control" value="Auto Generated" disabled>
            </div>
            <div class="col-md-3">
                <label class="form-label">Purchase Order</label>
                <input class="form-control" value="{{ $purchaseOrder->po_no }}" disabled>
            </div>
            <div class="col-md-3">
                <label class="form-label">Supplier Name</label>
                <input class="form-control" value="{{ $purchaseOrder->vendor->name ?? '--' }}" disabled>
            </div>
            <div class="col-md-3">
                <label class="form-label">Purchase Order Type</label>
                <input class="form-control" value="{{ $poTypeLabel }}" disabled>
            </div>
            <div class="col-md-3">
                <label class="form-label">GRN Department</label>
                <input name="warehouse" class="form-control" value="{{ old('warehouse', $purchaseOrder->grn_store ?? '') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">GRN Date <span class="text-danger">*</span></label>
                <input required type="date" name="received_date" value="{{ old('received_date', now()->toDateString()) }}" class="form-control @error('received_date') is-invalid @enderror">
                @error('received_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <input class="form-control" value="Pending Inspection" disabled>
            </div>
            <div class="col-12 mt-4">
                <h6 class="text-uppercase text-muted mb-2" style="font-size:.75rem;letter-spacing:.04em;">Delivery &amp; Inspection Personnel</h6>
                <hr class="mt-0 mb-3">
            </div>
            <div class="col-md-4">
                <label class="form-label">Delivered By <span class="text-danger">*</span></label>
                <input required type="text" name="delivered_by" value="{{ old('delivered_by') }}" class="form-control @error('delivered_by') is-invalid @enderror" placeholder="Name of delivery person">
                @error('delivered_by')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Delivered By Ph No.</label>
                <input type="text" name="delivered_by_phone" minlength="10" maxlength="12" pattern="[0-9\+\-\s]{10,12}" title="Please enter between 10 and 12 digits" value="{{ old('delivered_by_phone') }}" class="form-control @error('delivered_by_phone') is-invalid @enderror" placeholder="10 to 12 digits phone number">
                @error('delivered_by_phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Vehicle No.</label>
                <input type="text" name="vehicle_no" value="{{ old('vehicle_no') }}" class="form-control @error('vehicle_no') is-invalid @enderror" placeholder="Vehicle / Truck number">
                @error('vehicle_no')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Received By</label>
                <input type="text" class="form-control text-muted" value="{{ auth()->user()->name }}" disabled>
                <input type="hidden" name="received_by" value="{{ auth()->id() }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Received By Ph No.</label>
                <input type="text" name="received_by_phone" minlength="10" maxlength="12" pattern="[0-9\+\-\s]{10,12}" title="Please enter between 10 and 12 digits" value="{{ old('received_by_phone') }}" class="form-control @error('received_by_phone') is-invalid @enderror" placeholder="10 to 12 digits phone number">
                @error('received_by_phone')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="form-label">Inspected By</label>
                <input type="text" class="form-control text-muted" value="Assigned during Quality Inspection" disabled>
            </div>
        </div>
    </div>

    {{-- Section 2: Vendor Documents --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Section 2 — Vendor Documents</h5>
        </div>
        <div class="card-body">
            {{-- Bill / Invoice --}}
            <div class="border rounded p-3 mb-3">
                <h6 class="text-uppercase text-muted mb-3" style="font-size:.75rem;letter-spacing:.04em;">Bill / Invoice</h6>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Bill Number <span class="text-danger">*</span></label>
                        <input required name="invoice_number" class="form-control @error('invoice_number') is-invalid @enderror" value="{{ old('invoice_number') }}">
                        @error('invoice_number')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Bill Date</label>
                        <input type="date" name="bill_date" class="form-control" value="{{ old('bill_date') }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Bill Amount</label>
                        <input type="number" step="0.01" min="0" name="bill_amount" class="form-control" value="{{ old('bill_amount', $purchaseOrder->grand_total) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Bill File</label>
                        <input type="file" name="invoice" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        <div class="form-text">PDF / JPG / PNG</div>
                    </div>
                </div>
            </div>

            {{-- Challan --}}
            <div class="border rounded p-3 mb-3">
                <h6 class="text-uppercase text-muted mb-3" style="font-size:.75rem;letter-spacing:.04em;">Delivery Challan</h6>
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label">Challan Number</label>
                        <input name="challan_number" class="form-control @error('challan_number') is-invalid @enderror" value="{{ old('challan_number') }}">
                        @error('challan_number')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Challan Date</label>
                        <input type="date" name="challan_date" class="form-control" value="{{ old('challan_date') }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Challan File</label>
                        <input type="file" name="delivery_challan" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        <div class="form-text">PDF / JPG / PNG</div>
                    </div>
                </div>
            </div>

            {{-- Delivery Memo + Gate Entry --}}
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <h6 class="text-uppercase text-muted mb-3" style="font-size:.75rem;letter-spacing:.04em;">Delivery Memo</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Memo Number</label>
                                <input name="delivery_memo_no" class="form-control" value="{{ old('delivery_memo_no') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Memo Date</label>
                                <input type="date" name="delivery_memo_date" class="form-control" value="{{ old('delivery_memo_date') }}">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="border rounded p-3 h-100">
                        <h6 class="text-uppercase text-muted mb-3" style="font-size:.75rem;letter-spacing:.04em;">Gate Entry</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Entry Number</label>
                                <input name="gate_entry_no" class="form-control" value="{{ old('gate_entry_no') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Entry Date &amp; Time</label>
                                <input type="datetime-local" name="gate_entry_date" class="form-control" value="{{ old('gate_entry_date') }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Photos --}}
            <div class="border rounded p-3">
                <h6 class="text-uppercase text-muted mb-3" style="font-size:.75rem;letter-spacing:.04em;">Photos</h6>
                <div class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label">Upload Photos</label>
                        <input type="file" name="photos[]" class="form-control" accept="image/*" multiple>
                        <div class="form-text">You can select multiple images.</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Section 3: Item Details --}}
    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Section 3 — Item Details</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Item Code</th>
                            <th>HSN</th>
                            <th>Unit</th>
                            <th>Purchase Qty</th>
                            <th>Free Qty</th>
                            <th>Expected</th>
                            <th>Remaining</th>
                            <th>Received</th>
                            <th>Accepted</th>
                            <th>Rate</th>
                            <th>Batch</th>
                            <th>Serial Numbers</th>
                            <th>Remarks</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $rowIndex = 0; @endphp
                        @foreach ($purchaseOrder->items as $item)
                            @php
                                $already = (int) $item->grnItems->sum('received_quantity');
                                $remaining = max(0, ((int) $item->quantity + (float) $item->free_qty) - $already);
                                $stockBefore = (float) ($item->indentItem?->stock_in_hand ?? $item->getRawOriginal('qoh') ?? 0);
                            @endphp
                            @if ($remaining > 0)
                            <tr data-remaining="{{ $remaining }}" data-stock-before="{{ $stockBefore }}">
                                <td>
                                    <strong>{{ $item->item_name }}</strong>
                                    <div class="small text-muted">{{ $item->resolvedItemType()->label() }}</div>
                                    <input type="hidden" name="items[{{ $rowIndex }}][po_item_id]" value="{{ $item->id }}">
                                </td>
                                <td><span class="text-muted">{{ $item->item_code ?: '--' }}</span></td>
                                <td><span class="text-muted">{{ $item->hsn_code ?: '--' }}</span></td>
                                <td>{{ $item->purchase_unit ?? $item->unit ?? 'Nos' }}</td>
                                <td>{{ $item->quantity }}</td>
                                <td>{{ (float) $item->free_qty }}</td>
                                <td>{{ (int) $item->quantity + (float) $item->free_qty }}</td>
                                <td>{{ $remaining }}</td>
                                <td>
                                    <input type="number" min="0" max="{{ $remaining }}" required
                                           class="form-control received-qty @error('items.'.$rowIndex.'.received_quantity') is-invalid @enderror"
                                           name="items[{{ $rowIndex }}][received_quantity]"
                                           value="{{ old('items.'.$rowIndex.'.received_quantity', $remaining) }}">
                                    @error('items.'.$rowIndex.'.received_quantity')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </td>
                                <input type="hidden" name="items[{{ $rowIndex }}][rejected_quantity]" value="0">
                                <td>
                                    <input type="number" min="0" class="form-control accepted-qty" readonly
                                           value="{{ old('items.'.$rowIndex.'.received_quantity', $remaining) }}">
                                </td>
                                <td>{{ number_format((float) $item->price, 2) }}</td>
                                <td>
                                    <input class="form-control" name="items[{{ $rowIndex }}][batch_number]" value="{{ old('items.'.$rowIndex.'.batch_number') }}">
                                </td>
                                <td>
                                    @if ($item->isAssetItem())
                                        <input class="form-control" name="items[{{ $rowIndex }}][serial_numbers]"
                                               placeholder="SN001, SN002" value="{{ old('items.'.$rowIndex.'.serial_numbers') }}">
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <input class="form-control" name="items[{{ $rowIndex }}][remarks]" value="{{ old('items.'.$rowIndex.'.remarks') }}">
                                </td>
                            </tr>
                            @php $rowIndex++; @endphp
                            @endif
                        @endforeach
                        @if ($rowIndex === 0)
                            <tr><td colspan="15" class="text-center text-muted py-4">All PO lines are fully received.</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Section 4: Accounting Information --}}
    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Section 4 — Accounting Information</h5></div>
        <div class="card-body row g-3">
            <div class="col-md-4">
                <label class="form-label">Department Name</label>
                <input class="form-control" value="{{ $departmentName ?? '--' }}" disabled>
            </div>
            <div class="col-md-4">
                <label class="form-label">Cost Center</label>
                <input class="form-control" value="{{ $costCenter ?? '--' }}" disabled>
            </div>
            <div class="col-md-4">
                <label class="form-label">Characteristic</label>
                <input name="characteristic" class="form-control" value="{{ old('characteristic') }}">
            </div>
            <div class="col-12">
                <label class="form-label">GRN Remarks</label>
                <textarea name="remarks" class="form-control" rows="2">{{ old('remarks') }}</textarea>
            </div>
        </div>
    </div>

    @if ($rowIndex > 0)
    <input type="hidden" name="save_as_draft" id="save-as-draft-flag" value="0">
    <button type="submit" class="d-none" id="grn-hidden-submit" tabindex="-1" aria-hidden="true"></button>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-primary" id="submit-grn-btn">
            <i class="bx bx-check me-1"></i> Create GRN
        </button>
        <button type="button" class="btn btn-outline-secondary" id="draft-grn-btn">
            Save as Draft
        </button>
    </div>
    @endif
</form>
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    function recalcRow(row) {
        const remaining = parseInt(row.dataset.remaining || '0', 10);
        const receivedInput = row.querySelector('.received-qty');
        const rejectInput = row.querySelector('.reject-qty');
        const acceptedInput = row.querySelector('.accepted-qty');
        if (!receivedInput || !rejectInput || !acceptedInput) return;

        let received = parseInt(receivedInput.value || '0', 10);
        let rejected = parseInt(rejectInput.value || '0', 10);

        if (received > remaining) received = remaining;
        if (rejected > received) rejected = received;

        receivedInput.value = received;
        rejectInput.value = rejected;
        acceptedInput.value = Math.max(0, received - rejected);
    }

    document.querySelectorAll('tbody tr[data-remaining]').forEach(function (row) {
        row.querySelectorAll('.received-qty, .reject-qty').forEach(function (input) {
            input.addEventListener('input', function () { recalcRow(row); });
        });
        recalcRow(row);
    });

    const form = document.getElementById('grn-create-form');
    const submitBtn = document.getElementById('submit-grn-btn');
    const draftBtn = document.getElementById('draft-grn-btn');
    const draftFlag = document.getElementById('save-as-draft-flag');

    if (draftBtn && form && draftFlag) {
        draftBtn.addEventListener('click', function () {
            draftFlag.value = '1';
            form.submit();
        });
    }

    if (form && submitBtn && draftFlag) {
        submitBtn.addEventListener('click', function () {
            draftFlag.value = '0';
            if (!form.reportValidity()) {
                return;
            }
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Create GRN?',
                    text: 'Goods receipt will be recorded against this Purchase Order.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, create GRN',
                    cancelButtonText: 'Cancel'
                }).then(function (result) {
                    if (result.isConfirmed) form.requestSubmit();
                });
            } else {
                form.requestSubmit();
            }
        });
    }
});
</script>
@endsection
