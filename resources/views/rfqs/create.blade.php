@extends('layouts/contentNavbarLayout')

@section('title', 'Create RFQ')

@section('vendor-style')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    .select2-container { width: 100% !important; }
</style>
@endsection

@section('content')
@php
    $selectedPr = $pr;
    $indent = $selectedPr?->indent;
    $indentItems = $indent?->items ?? collect();
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="mb-0">Create RFQ</h4>
    </div>
    <a class="btn btn-secondary btn-sm" href="{{ route('rfqs.index') }}">Cancel</a>
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

<form method="POST" action="{{ route('rfqs.store') }}" enctype="multipart/form-data" id="rfq-form">
    @csrf

    {{-- Basic Information --}}
    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Basic Information</h5></div>
        <div class="card-body row g-3">
            <div class="col-md-4 col-lg-3">
                <label class="form-label">RFQ No.</label>
                <input class="form-control" value="Auto Generated" disabled>
            </div>
            <div class="col-md-4 col-lg-3">
                <label class="form-label">RFQ Date</label>
                <input type="date" name="rfq_date" class="form-control" value="{{ old('rfq_date', now()->toDateString()) }}">
            </div>
            <div class="col-md-4 col-lg-3">
                <label class="form-label">Purchase Requisition <span class="text-danger">*</span></label>
                <select name="pr_id" id="pr_id" class="form-select @error('pr_id') is-invalid @enderror" required>
                    <option value="">Select purchase requisition</option>
                    @foreach ($prs as $row)
                        @php
                            $itemNames = $row->indent?->items
                                ?->pluck('item_name')
                                ->filter()
                                ->unique()
                                ->values() ?? collect();
                            $itemLabel = $itemNames->isNotEmpty()
                                ? $itemNames->take(3)->implode(', ').($itemNames->count() > 3 ? ' +'.($itemNames->count() - 3).' more' : '')
                                : 'No items';
                        @endphp
                        <option value="{{ $row->id }}" @selected((string) old('pr_id', $selectedPr?->id) === (string) $row->id)>
                            {{ $row->pr_no }} — {{ $itemLabel }}
                        </option>
                    @endforeach
                </select>
                @error('pr_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4 col-lg-3">
                <label class="form-label">Department</label>
                <input type="text" id="department_name" class="form-control" value="{{ old('department_name', $indent?->department?->department_name) }}" readonly placeholder="From PR">
            </div>
            <div class="col-md-4 col-lg-3">
                <label class="form-label">Priority</label>
                <select name="priority" id="priority" class="form-select">
                    <option value="">Select priority</option>
                    @foreach ($priorities as $priority)
                        <option value="{{ $priority }}" @selected(old('priority', $indent?->priority) === $priority)>{{ $priority }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 col-lg-3">
                <label class="form-label">Closing Date</label>
                <input type="datetime-local" name="closes_at" class="form-control" value="{{ old('closes_at') }}">
            </div>
            <div class="col-md-4 col-lg-3">
                <label class="form-label">Expected Delivery</label>
                <input type="date" name="expected_delivery_date" id="expected_delivery_date" class="form-control"
                    value="{{ old('expected_delivery_date', optional($selectedPr?->required_date ?? $indent?->required_date)->format('Y-m-d')) }}">
            </div>
            <div class="col-md-4 col-lg-3">
                <label class="form-label">Delivery Store</label>
                <select name="delivery_store" id="delivery_store" class="form-select">
                    <option value="">Select delivery store</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->department_name }}" @selected(old('delivery_store') === $dept->department_name)>
                            {{ $dept->department_name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 col-lg-3">
                <label class="form-label">Currency</label>
                <select name="currency" class="form-select">
                    @foreach ($currencies as $currency)
                        <option value="{{ $currency }}" @selected(old('currency', 'INR') === $currency)>{{ $currency }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- Vendor Information --}}
    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Vendor Information</h5></div>
        <div class="card-body">
            <label class="form-label">Select Vendors <span class="text-danger">*</span> <small class="text-muted">(required to Send RFQ)</small></label>
            <select name="vendor_ids[]" id="vendor_ids" class="form-select select2 @error('vendor_ids') is-invalid @enderror" multiple="multiple" data-placeholder="Select vendors">
                @foreach ($vendors as $vendor)
                    <option value="{{ $vendor->id }}"
                            data-name="{{ $vendor->name }}"
                            data-email="{{ $vendor->email }}"
                            data-phone="{{ $vendor->phone_number }}"
                            data-gst="{{ $vendor->gst_number }}"
                            data-city="{{ $vendor->city }}"
                            data-state="{{ $vendor->state }}"
                            data-country="{{ $vendor->country }}"
                            data-status="{{ $vendor->status }}"
                            data-notes="{{ $vendor->notes }}"
                            data-brands="{{ $vendor->brands_names }}"
                            data-address="{{ collect([$vendor->address, $vendor->city, $vendor->state, $vendor->country])->filter()->implode(', ') }}"
                            @selected(collect(old('vendor_ids', []))->contains($vendor->id))>
                        {{ $vendor->name }}
                    </option>
                @endforeach
            </select>
            @error('vendor_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            @error('vendor_ids.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>
    </div>

    {{-- Item Details --}}
    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Item Details</h5></div>
        <div class="table-responsive">
            <table class="table mb-0" id="rfq-items-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Code</th>
                        <th>HSN</th>
                        <th>Qty</th>
                        <th>Unit</th>
                        <th>Manufacturer</th>
                        <th>Remarks</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="rfq-items-body">
                    @forelse ($indentItems as $item)
                        <tr>
                            <td>{{ $item->item_name }}</td>
                            <td>IND-{{ str_pad((string) $item->id, 6, '0', STR_PAD_LEFT) }}</td>
                            <td>--</td>
                            <td>{{ $item->quantity }}</td>
                            <td>{{ $item->unit ?? '--' }}</td>
                            <td>{{ $item->manufacturer ?: '--' }}</td>
                            <td>{{ $item->item_remarks ?: '--' }}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-outline-info btn-view-history" data-item-name="{{ $item->item_name }}" data-item-code="IND-{{ str_pad((string) $item->id, 6, '0', STR_PAD_LEFT) }}">
                                    <i class="bx bx-history me-1"></i> View Previous Item History
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr id="rfq-items-empty">
                            <td colspan="8" class="text-center text-muted py-4">Select a purchase requisition to load items.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Attachments --}}
    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Attachments</h5></div>
        <div class="card-body">
            <label class="form-label">Upload Files</label>
            <input type="file" name="attachments[]" class="form-control" multiple>
            <small class="text-muted">You can upload multiple files (max 10 MB each).</small>
        </div>
    </div>

    {{-- Terms & Conditions --}}
    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Terms &amp; Conditions</h5></div>
        <div class="card-body">
            <label class="form-label">Notes to Vendor</label>
            <textarea name="notes_to_vendor" class="form-control" rows="4" placeholder="Enter notes / terms for vendors">{{ old('notes_to_vendor', old('notes')) }}</textarea>
        </div>
    </div>

    {{-- Actions --}}
    <div class="d-flex flex-wrap gap-2">
        <button type="submit" name="action" value="draft" class="btn btn-outline-secondary">Save Draft</button>
        <button type="submit" name="action" value="send" class="btn btn-primary">Send RFQ</button>
        <a href="{{ route('rfqs.index') }}" class="btn btn-light">Cancel</a>
    </div>
</form>
@include('procurement._item-history-modal')
@endsection

@section('page-script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const $vendorSelect = $('#vendor_ids');
    if ($vendorSelect.hasClass('select2-hidden-accessible')) {
        $vendorSelect.select2('destroy');
    }
    $vendorSelect.select2({
        theme: 'bootstrap-5',
        placeholder: 'Select vendors',
        allowClear: true,
        width: '100%',
        dropdownParent: $(document.body)
    });

    const prSelect = document.getElementById('pr_id');
    const departmentInput = document.getElementById('department_name');
    const prioritySelect = document.getElementById('priority');
    const expectedDelivery = document.getElementById('expected_delivery_date');
    const itemsBody = document.getElementById('rfq-items-body');
    const prDataUrl = @json(route('rfqs.purchase-requisitions.data', ['purchaseRequisition' => '__ID__']));

    function esc(v) {
        return String(v ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function renderItems(items) {
        if (!items.length) {
            itemsBody.innerHTML = '<tr id="rfq-items-empty"><td colspan="8" class="text-center text-muted py-4">No items found on this purchase requisition.</td></tr>';
            return;
        }

        itemsBody.innerHTML = items.map(function (item) {
            return '<tr>' +
                '<td>' + esc(item.item_name) + '</td>' +
                '<td>' + esc(item.item_code || '--') + '</td>' +
                '<td>' + esc(item.hsn_code || '--') + '</td>' +
                '<td>' + esc(item.quantity) + '</td>' +
                '<td>' + esc(item.unit || '--') + '</td>' +
                '<td>' + esc(item.manufacturer || '--') + '</td>' +
                '<td>' + esc(item.remarks || '--') + '</td>' +
                '<td>' +
                    '<button type="button" class="btn btn-sm btn-outline-info btn-view-history" data-item-name="' + esc(item.item_name) + '" data-item-code="' + esc(item.item_code || '') + '">' +
                        '<i class="bx bx-history me-1"></i> View Previous Item History' +
                    '</button>' +
                '</td>' +
            '</tr>';
        }).join('');
    }

    function loadPr(id) {
        if (!id) {
            departmentInput.value = '';
            itemsBody.innerHTML = '<tr id="rfq-items-empty"><td colspan="8" class="text-center text-muted py-4">Select a purchase requisition to load items.</td></tr>';
            return;
        }

        fetch(prDataUrl.replace('__ID__', id), { headers: { Accept: 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                departmentInput.value = data.department || '';
                if (data.priority) {
                    prioritySelect.value = data.priority;
                }
                if (data.expected_delivery_date && !expectedDelivery.value) {
                    expectedDelivery.value = data.expected_delivery_date;
                }
                renderItems(data.items || []);
            })
            .catch(function () {
                itemsBody.innerHTML = '<tr><td colspan="7" class="text-center text-danger py-4">Unable to load PR items.</td></tr>';
            });
    }

    prSelect.addEventListener('change', function () {
        loadPr(prSelect.value);
    });
});
</script>
@endsection
