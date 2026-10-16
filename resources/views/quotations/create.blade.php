@extends('layouts/contentNavbarLayout')

@section('title', 'Record Quotation')

@section('content')
@php
    $indentItems = $rfq->purchaseRequisition->indent->items ?? collect();
@endphp
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Record Quotation — {{ $rfq->rfq_no }}</h4>
    <a class="btn btn-secondary btn-sm" href="{{ route('rfqs.show', $rfq) }}">Back to RFQ</a>
</div>

<form method="POST" action="{{ route('rfqs.quotations.store', $rfq) }}" id="multi-quotation-form">
    @csrf

    @foreach ($vendors as $vIndex => $vendor)
        <div class="card mb-4 vendor-card" data-vendor-index="{{ $vIndex }}">
            <div class="card-header bg-light d-flex justify-content-between align-items-center py-3">
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input vendor-toggle" type="checkbox" id="toggle-vendor-{{ $vIndex }}" checked>
                    <label class="form-check-label fw-bold ms-2" for="toggle-vendor-{{ $vIndex }}">{{ $vendor->name }}</label>
                </div>
                <small class="text-muted">{{ $vendor->email ?? 'No email' }} | {{ $vendor->phone_number ?? 'No phone' }}</small>
            </div>
            
            <div class="card-body mt-3 vendor-body">
                <input type="hidden" name="quotations[{{ $vIndex }}][vendor_id]" value="{{ $vendor->id }}" class="vendor-input">
                
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <label class="form-label">Contact Person</label>
                        <input name="quotations[{{ $vIndex }}][contact_person]" class="form-control vendor-input" value="{{ $vendor->name }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Contact Phone</label>
                        <input name="quotations[{{ $vIndex }}][contact_phone]" class="form-control vendor-input" value="{{ $vendor->phone_number }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Contact Email</label>
                        <input name="quotations[{{ $vIndex }}][contact_email]" type="email" class="form-control vendor-input" value="{{ $vendor->email }}">
                    </div>
                </div>

                <h6 class="fw-bold">Quoted Items <small class="text-muted fw-normal">(pre-filled from the indent)</small></h6>
                <div class="table-responsive">
                    <table class="table vendor-items-table">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th style="width:100px">Qty</th>
                                <th style="width:90px">Unit</th>
                                <th style="width:140px">Unit Price</th>
                                <th style="width:140px">Line Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($indentItems as $i => $item)
                                <tr>
                                    <td>
                                        {{ $item->item_name }}
                                        <input type="hidden" name="quotations[{{ $vIndex }}][items][{{ $i }}][indent_item_id]" value="{{ $item->id }}" class="vendor-input">
                                        <input type="hidden" name="quotations[{{ $vIndex }}][items][{{ $i }}][item_name]" value="{{ $item->item_name }}" class="vendor-input">
                                    </td>
                                    <td>
                                        <input type="number" min="1" class="form-control qty-input vendor-input"
                                               name="quotations[{{ $vIndex }}][items][{{ $i }}][quantity]" value="{{ $item->quantity }}">
                                    </td>
                                    <td>
                                        <input class="form-control vendor-input" name="quotations[{{ $vIndex }}][items][{{ $i }}][unit]" value="{{ $item->unit }}">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" min="0.01" class="form-control price-input vendor-input"
                                               name="quotations[{{ $vIndex }}][items][{{ $i }}][unit_price]" value="" required placeholder="Enter price">
                                    </td>
                                    <td class="line-total">0.00</td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center">No indent items found.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-md-3">
                        <label class="form-label">Items Total (Price)</label>
                        <input name="quotations[{{ $vIndex }}][price]" type="number" step="0.01" class="form-control quote-price vendor-input" readonly>
                        <small class="text-muted">Auto-calculated</small>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Tax</label>
                        <input name="quotations[{{ $vIndex }}][tax]" type="number" step="0.01" value="0" class="form-control quote-tax vendor-input">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Shipping</label>
                        <input name="quotations[{{ $vIndex }}][shipping]" type="number" step="0.01" value="0" class="form-control quote-shipping vendor-input">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Total Cost</label>
                        <input type="text" class="form-control quote-total" readonly>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Delivery Days</label>
                        <input name="quotations[{{ $vIndex }}][delivery_days]" type="number" min="0" class="form-control vendor-input">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Warranty</label>
                        <input name="quotations[{{ $vIndex }}][warranty]" class="form-control vendor-input" placeholder="e.g. 1 Year Onsite">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Notes</label>
                        <input name="quotations[{{ $vIndex }}][notes]" class="form-control vendor-input">
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <div class="d-flex justify-content-end mb-5">
        <button class="btn btn-primary btn-lg" type="submit" id="save-all-btn">Save All Quotations</button>
    </div>
</form>

@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('multi-quotation-form');
    const saveBtn = document.getElementById('save-all-btn');

    function toggleVendor(card, isEnabled) {
        const body = card.querySelector('.vendor-body');
        const inputs = card.querySelectorAll('.vendor-input');
        
        if (isEnabled) {
            body.style.opacity = '1';
            body.style.pointerEvents = 'auto';
            inputs.forEach(input => {
                input.disabled = false;
            });
        } else {
            body.style.opacity = '0.5';
            body.style.pointerEvents = 'none';
            inputs.forEach(input => {
                input.disabled = true;
            });
        }
        checkAnyEnabled();
    }

    function checkAnyEnabled() {
        const anyChecked = document.querySelectorAll('.vendor-toggle:checked').length > 0;
        saveBtn.disabled = !anyChecked;
    }

    document.querySelectorAll('.vendor-toggle').forEach(toggle => {
        toggle.addEventListener('change', function() {
            const card = this.closest('.vendor-card');
            toggleVendor(card, this.checked);
        });
    });

    function recalcVendor(card) {
        let total = 0;
        card.querySelectorAll('.vendor-items-table tbody tr').forEach(function (row) {
            const qty = parseFloat(row.querySelector('.qty-input')?.value || 0);
            const price = parseFloat(row.querySelector('.price-input')?.value || 0);
            const line = qty * price;
            const cell = row.querySelector('.line-total');
            if (cell) cell.textContent = line.toFixed(2);
            total += line;
        });
        
        const priceInput = card.querySelector('.quote-price');
        if (priceInput) priceInput.value = total.toFixed(2);
        
        const tax = parseFloat(card.querySelector('.quote-tax')?.value || 0);
        const shipping = parseFloat(card.querySelector('.quote-shipping')?.value || 0);
        const totalInput = card.querySelector('.quote-total');
        if (totalInput) totalInput.value = (total + tax + shipping).toFixed(2);
    }

    document.querySelectorAll('.vendor-card').forEach(card => {
        card.querySelectorAll('.qty-input, .price-input, .quote-tax, .quote-shipping').forEach(el => {
            el.addEventListener('input', () => recalcVendor(card));
        });
        recalcVendor(card);
    });
});
</script>
@endsection
