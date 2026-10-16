@extends('layouts/contentNavbarLayout')

@section('title', 'Edit GRN ' . $grn->grn_number)

@section('page-style')
<style>
#grn-items-table .item-flag-cell {
    min-width: 120px;
    text-align: center;
}
#grn-items-table .item-is-group:disabled {
    opacity: 0.35;
    cursor: not-allowed;
}
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Edit GRN — {{ $grn->grn_number }}</h4>
    <a href="{{ route('msu-grns.index') }}" class="btn btn-secondary btn-sm">Back</a>
</div>

@if(session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif
@if($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">
        @foreach($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<div class="alert alert-info">
    Only <strong>CAPEX</strong> and <strong>Group Asset</strong> can be changed. Quantities and the rest of this GRN stay as received.
    Group Asset is available when CAPEX is checked and the received quantity is more than 1.
</div>

<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3 mb-3"><small class="text-muted d-block">GRN Number</small><strong>{{ $grn->grn_number }}</strong></div>
            <div class="col-md-3 mb-3"><small class="text-muted d-block">GRN Date</small><strong>{{ $grn->grn_date?->format('d-m-Y') }}</strong></div>
            <div class="col-md-3 mb-3"><small class="text-muted d-block">Status</small>
                <span class="badge bg-label-{{ $grn->statusEnum()->badgeClass() }}">{{ $grn->statusEnum()->label() }}</span>
            </div>
            <div class="col-md-3 mb-3"><small class="text-muted d-block">PO Number</small><strong>{{ $grn->purchaseOrder?->po_number ?? '--' }}</strong></div>
            <div class="col-md-3 mb-3"><small class="text-muted d-block">Vendor</small><strong>{{ $grn->vendor?->name ?? '--' }}</strong></div>
            <div class="col-md-3 mb-3"><small class="text-muted d-block">Department</small><strong>{{ $grn->department?->department_name ?? '--' }}</strong></div>
            <div class="col-md-3 mb-0"><small class="text-muted d-block">Invoice No</small><strong>{{ $grn->vendor_invoice_number ?: '--' }}</strong></div>
        </div>
    </div>
</div>

<form method="POST" action="{{ route('msu-grns.update', $grn) }}" id="grn-classification-form">
    @csrf
    @method('PUT')

    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">GRN Items</h5></div>
        <div class="table-responsive">
            <table class="table mb-0" id="grn-items-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Item Code</th>
                        <th>Item Name</th>
                        <th class="text-end">Ordered Qty</th>
                        <th class="text-end">Previously Received</th>
                        <th class="text-end">Received Qty</th>
                        <th class="text-center">CAPEX</th>
                        <th class="text-center">Group Asset</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($grn->items as $item)
                    @php
                        $capexChecked = (bool) $item->is_capex;
                        $groupChecked = (bool) $item->is_group_asset;
                        $oldItems = old('items');
                        if (is_array($oldItems)) {
                            foreach ($oldItems as $oldRow) {
                                if ((int) ($oldRow['id'] ?? 0) === (int) $item->id) {
                                    $capexChecked = filter_var($oldRow['is_capex'] ?? false, FILTER_VALIDATE_BOOLEAN);
                                    $groupChecked = filter_var($oldRow['is_group_asset'] ?? false, FILTER_VALIDATE_BOOLEAN);
                                    break;
                                }
                            }
                        }
                        $receivedQty = (float) $item->received_quantity;
                    @endphp
                    <tr class="grn-item-row" data-received="{{ $receivedQty }}">
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->item_code }}</td>
                        <td>{{ $item->item_name }}</td>
                        <td class="text-end">{{ number_format((float) $item->ordered_quantity, 3) }}</td>
                        <td class="text-end">{{ number_format((float) $item->previously_received_quantity, 3) }}</td>
                        <td class="text-end">{{ number_format($receivedQty, 3) }}</td>
                        <td class="item-flag-cell">
                            <input type="hidden" name="items[{{ $loop->index }}][id]" value="{{ $item->id }}">
                            <input type="hidden" name="items[{{ $loop->index }}][is_capex]" value="0">
                            <input type="checkbox"
                                   class="form-check-input item-is-capex"
                                   name="items[{{ $loop->index }}][is_capex]"
                                   value="1"
                                   {{ $capexChecked ? 'checked' : '' }}
                                   title="Checked items are created as assets. Unchecked items stay Opex and do not create assets.">
                        </td>
                        <td class="item-flag-cell">
                            <input type="hidden" name="items[{{ $loop->index }}][is_group_asset]" value="0">
                            <input type="checkbox"
                                   class="form-check-input item-is-group"
                                   name="items[{{ $loop->index }}][is_group_asset]"
                                   value="1"
                                   {{ $groupChecked ? 'checked' : '' }}
                                   title="Available when CAPEX is checked and received quantity is more than 1. Checked creates one asset for the full quantity.">
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="d-flex gap-2">
        <button type="submit" class="btn btn-primary">Save</button>
        <a href="{{ route('msu-grns.show', $grn) }}" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>
@endsection

@section('page-script')
<script>
function syncGrnItemFlags(row) {
    var qty = parseFloat(row.getAttribute('data-received')) || 0;
    var capex = row.querySelector('.item-is-capex').checked;
    var group = row.querySelector('.item-is-group');
    var enabled = qty > 1 && capex;
    group.disabled = !enabled;
    if (!enabled) {
        group.checked = false;
    }
}

document.querySelectorAll('#grn-items-table .grn-item-row').forEach(function(row) {
    syncGrnItemFlags(row);
    row.querySelector('.item-is-capex').addEventListener('change', function() {
        syncGrnItemFlags(row);
    });
});
</script>
@endsection
