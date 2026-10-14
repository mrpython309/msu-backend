@php
    $isEdit = isset($msuPurchaseOrder);
    $oldItems = old('items');
    if ($oldItems === null && $isEdit) {
        $oldItems = $msuPurchaseOrder->items->map(fn ($item) => [
            'msu_item_master_id' => $item->msu_item_master_id,
            'hsn' => $item->hsn,
            'quantity' => $item->quantity,
            'price' => $item->price,
            'tax' => $item->tax,
            'net_value' => $item->net_value,
        ])->toArray();
    }
    $oldItems = $oldItems ?: [['msu_item_master_id' => '', 'hsn' => '', 'quantity' => '', 'price' => 0, 'tax' => 0, 'net_value' => 0]];
@endphp

<style>
    /* Remove borders from inputs in PO items tables, except the item autocomplete and qty */
    #po-items-table .form-control:not(.item-autocomplete):not(.qty-input) {
        border: none !important;
        background-color: transparent !important;
        box-shadow: none !important;
        padding-left: 5px;
        padding-right: 5px;
        border-radius: 0;
    }
    #po-items-table .form-control:not(.item-autocomplete):not(.qty-input):focus {
        background-color: rgba(0,0,0,0.03) !important; /* Very subtle background on focus */
        outline: none;
    }
    #po-items-table input[type=number]::-webkit-outer-spin-button,
    #po-items-table input[type=number]::-webkit-inner-spin-button,
    #po-charges-body input[type=number]::-webkit-outer-spin-button,
    #po-charges-body input[type=number]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    #po-items-table input[type=number],
    #po-charges-body input[type=number] {
        -moz-appearance: textfield;
        appearance: textfield;
    }
    .input-group > .select2-container {
        flex: 1 1 auto;
        width: 1% !important;
    }

    /* Charge name can wrap; Amount / Tax stay a fixed width and never collapse */
    .po-charges-wrap {
        width: 100%;
        min-width: 0;
    }
    .po-charges-wrap:has(#po-charges-body:empty) .po-charges-head {
        display: none;
    }
    .po-charges-head,
    .po-charge-row {
        display: grid;
        grid-template-columns: minmax(0, 1fr) 120px 96px 120px 120px 44px;
        gap: 0.5rem 0.75rem;
        align-items: center;
    }
    .po-charges-head {
        padding: 0 0 0.5rem;
        margin-bottom: 0.75rem;
        border-bottom: 1px solid #eee;
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 0.02em;
        color: #6c757d;
        text-transform: uppercase;
    }
    .po-charge-row {
        margin-bottom: 0.75rem;
    }
    .po-charge-name {
        min-width: 0;
        max-width: 100%;
    }
    .po-charge-name .charge-autocomplete {
        width: 100%;
        max-width: 100%;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    .po-charge-mobile-label {
        display: none;
    }
    .po-charge-amount-wrap input,
    .po-charge-tax-wrap input,
    .po-charge-tax-val-wrap input,
    .po-charge-total-wrap input {
        width: 100%;
        min-width: 0;
        text-align: end;
    }
    .po-charge-action {
        display: flex;
        justify-content: flex-end;
        align-items: center;
    }
    @media (max-width: 991.98px) {
        .po-charges-head {
            display: none;
        }
        .po-charge-row {
            grid-template-columns: 1fr 1fr;
            align-items: end;
            border: 1px solid #eee;
            border-radius: 0.5rem;
            padding: 0.75rem;
            margin-bottom: 0.75rem;
        }
        .po-charge-name {
            grid-column: 1 / -1;
        }
        .po-charge-action {
            grid-column: 1 / -1;
            padding-top: 0.25rem;
        }
        .po-charge-mobile-label {
            display: block;
            margin-bottom: 0.25rem;
            font-size: 0.75rem;
            color: #6c757d;
        }
    }

    #po-items-table {
        table-layout: fixed;
        width: 100%;
        min-width: 900px;
    }
    #po-items-table .item-autocomplete {
        width: 100%;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    #po-items-table .qty-input,
    #po-items-table .price-input,
    #po-items-table .gross-value,
    #po-items-table .tax-input,
    #po-items-table .tax-value,
    #po-items-table .net-value {
        text-align: end;
    }
</style>

@if ($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ $isEdit ? route('msu-purchase-orders.update', $msuPurchaseOrder) : route('msu-purchase-orders.store') }}" id="msu-po-form">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">PO Details</h5></div>
        <div class="card-body">
            <div class="row row-cols-1 row-cols-md-4 g-3">
                <div class="col">
                    <label class="form-label">PO Number</label>
                    <input type="text" class="form-control" id="po_number_preview"
                           value="{{ $isEdit ? ($displayPoNumber ?? $msuPurchaseOrder->po_number) : ($previewPoNumber ?? 'MSULLP/BRN/PO/----/--/0001') }}"
                           disabled>
                </div>
                <div class="col">
                    <label class="form-label">PO Date <span class="text-danger">*</span></label>
                    <input type="date" name="po_date" id="po_date" class="form-control @error('po_date') is-invalid @enderror"
                           value="{{ old('po_date', $isEdit ? $msuPurchaseOrder->po_date?->format('Y-m-d') : now()->format('Y-m-d')) }}"
                           max="{{ now()->format('Y-m-d') }}" required>
                    @error('po_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col">
                    <label class="form-label">Date of Commencement</label>
                    <input type="date" name="date_of_commencement" id="date_of_commencement" class="form-control"
                           value="{{ old('date_of_commencement', $isEdit ? $msuPurchaseOrder->date_of_commencement?->format('Y-m-d') : ($isEdit ? $msuPurchaseOrder->po_date?->format('Y-m-d') : now()->format('Y-m-d'))) }}">
                </div>
                <div class="col">
                    <label class="form-label">Vendor <span class="text-danger">*</span></label>
                    <select name="vendor_id" id="vendor_id" class="form-select @error('vendor_id') is-invalid @enderror" required>
                        <option value="" data-state="">Select Vendor</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}"
                                data-state="{{ $vendor->state }}"
                                {{ old('vendor_id', $isEdit ? $msuPurchaseOrder->vendor_id : '') == $vendor->id ? 'selected' : '' }}>
                                {{ $vendor->company_code ? $vendor->company_code.' - ' : '' }}{{ $vendor->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('vendor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col">
                    <label class="form-label">Branch <span class="text-danger">*</span></label>
                    @php
                        $selectedBranchId = old('branch_id', $isEdit ? $msuPurchaseOrder->branch_id : '');
                        if ($selectedBranchId && ! ($branches ?? collect())->contains('id', (int) $selectedBranchId)) {
                            $selectedBranchId = '';
                        }
                    @endphp
                    <select name="branch_id" id="branch_id" class="form-select" required>
                        <option value="" data-state="" data-code="BRN" data-company-code="{{ $companyCode ?? 'MSULLP' }}">Select Branch</option>
                        @foreach(($branches ?? collect()) as $branch)
                            <option value="{{ $branch->id }}"
                                data-state="{{ $branch->state }}"
                                data-code="{{ $branch->code ?: $branch->name }}"
                                data-company-code="{{ $branch->company?->code ?: ($companyCode ?? 'MSULLP') }}"
                                {{ (string) $selectedBranchId === (string) $branch->id ? 'selected' : '' }}>
                                {{ $branch->code ? $branch->code.' - ' : '' }}{{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col">
                    <label class="form-label">Employee</label>
                    @php $selectedEmployeeId = old('employee_master_id', $isEdit ? $msuPurchaseOrder->employee_master_id : ''); @endphp
                    <select name="employee_master_id" id="employee_master_id" class="form-select">
                        <option value="">Select Employee</option>
                        @foreach(($employees ?? collect()) as $employee)
                            <option value="{{ $employee->id }}" {{ (string) $selectedEmployeeId === (string) $employee->id ? 'selected' : '' }}>
                                {{ $employee->employee_name }}
                                @if($employee->department)
                                    ({{ $employee->department->department_name }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col">
                    <label class="form-label">Department</label>
                    @php $selectedDepartmentId = old('department_id', $isEdit ? $msuPurchaseOrder->department_id : ''); @endphp
                    <select name="department_id" id="department_id" class="form-select">
                        <option value="">Select Department</option>
                        @foreach(($departments ?? collect()) as $department)
                            <option value="{{ $department->id }}" {{ (string) $selectedDepartmentId === (string) $department->id ? 'selected' : '' }}>
                                {{ $department->department_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col">
                    <label class="form-label">Payment Terms</label>
                    @php
                        $selectedPaymentTermId = old('payment_term_id', $isEdit ? $msuPurchaseOrder->payment_term_id : '');
                    @endphp
                    <div class="input-group">
                        <select name="payment_term_id" id="payment_term_id" class="form-select">
                            <option value="">Select Payment Term</option>
                            @foreach(($paymentTerms ?? collect()) as $term)
                                <option value="{{ $term->id }}" {{ (string) $selectedPaymentTermId === (string) $term->id ? 'selected' : '' }}>
                                    {{ $term->payment_mode ? $term->payment_mode.' - ' : '' }}{{ $term->term_name }}
                                </option>
                            @endforeach
                        </select>
                        <button type="button"
                                class="btn btn-outline-secondary"
                                id="manage-payment-terms-btn"
                                title="Manage Payment Terms">
                            <i class="bx bx-edit-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="col">
                    <label class="form-label">Shipping Details</label>
                    @php $selectedShippingId = old('shipping_detail_id', $isEdit ? $msuPurchaseOrder->shipping_detail_id : ''); @endphp
                    <select name="shipping_detail_id" id="shipping_detail_id" class="form-select">
                        <option value="">Select Shipping</option>
                        @foreach(($shippingDetails ?? collect()) as $shipping)
                            <option value="{{ $shipping->id }}"
                                data-name="{{ $shipping->shipping_name }}"
                                data-gst="{{ $shipping->gst_number }}"
                                data-address="{{ $shipping->address }}"
                                data-contact-person="{{ $shipping->contact_person }}"
                                data-contact-no="{{ $shipping->contact_no }}"
                                data-email="{{ $shipping->email }}"
                                {{ (string) $selectedShippingId === (string) $shipping->id ? 'selected' : '' }}>
                                {{ $shipping->shipping_name }}
                                @if($shipping->contact_person)
                                    ({{ $shipping->contact_person }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-9">
                    <label class="form-label">Remarks</label>
                    <textarea name="remarks" id="remarks" class="form-control" rows="1"
                              placeholder="Enter remarks">{{ old('remarks', $isEdit ? $msuPurchaseOrder->remarks : '') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Purchase Order Item Details</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table" id="po-items-table">
                    <thead>
                        <tr>
                            <th style="width:28%">Item <span class="text-danger">*</span></th>
                            <th style="width:10%">HSN</th>
                            <th style="width:9%">Qty <span class="text-danger">*</span></th>
                            <th style="width:11%">Rate <span class="text-danger">*</span></th>
                            <th style="width:11%">Gross Value</th>
                            <th style="width:8%">GST %</th>
                            <th style="width:11%">GST Value</th>
                            <th style="width:12%">Net Value</th>
                            <th style="width:56px"></th>
                        </tr>
                    </thead>
                    <datalist id="po-item-options">
                        @foreach($items as $item)
                            <option value="{{ $item['short_label'] }}"
                                data-id="{{ $item['id'] }}"
                                data-code="{{ $item['item_code'] }}"
                                data-label="{{ $item['label'] }}"
                                data-hsn="{{ $item['hsn'] }}"
                                data-rate="{{ $item['rate'] }}"
                                data-tax="{{ $item['tax'] }}"></option>
                        @endforeach
                    </datalist>
                    <tbody id="po-items-body">
                        @php $selectedItemTaxAmount = 0; @endphp
                        @foreach($oldItems as $idx => $row)
                        @php
                            $qtyRaw = $row['quantity'] ?? '';
                            $qty = $qtyRaw === '' || $qtyRaw === null ? 0 : (float) $qtyRaw;
                            $rate = (float) ($row['price'] ?? 0);
                            $taxPct = (float) ($row['tax'] ?? 0);
                            $gross = round($qty * $rate, 2);
                            $taxValue = round($gross * $taxPct / 100, 2);
                            $selectedItemTaxAmount += $taxValue;
                            $net = isset($row['net_value']) ? (float) $row['net_value'] : round($gross + $taxValue, 2);
                        @endphp
                        <tr class="po-item-row">
                            <td>
                                @php
                                    $selectedLabel = '';
                                    $found = null;
                                    $itemHsnReadonly = false;
                                    $itemRateReadonly = false;
                                    $itemTaxReadonly = false;
                                    if(isset($row['msu_item_master_id']) && $row['msu_item_master_id']) {
                                        $found = collect($items)->firstWhere('id', $row['msu_item_master_id']);
                                        if($found) {
                                            $selectedLabel = $found['short_label'] ?? $found['label'];
                                            if ($found['hsn']) $itemHsnReadonly = true;
                                            if ($found['rate'] !== null && $found['rate'] !== '') $itemRateReadonly = true;
                                            if ($found['tax'] !== null && $found['tax'] !== '') $itemTaxReadonly = true;
                                        }
                                    }
                                @endphp
                                <div class="position-relative">
                                    <input type="text" class="form-control item-autocomplete" list="po-item-options" placeholder="Type to search item..." value="{{ $selectedLabel }}" title="{{ $found['label'] ?? $selectedLabel }}" autocomplete="off" required>
                                    <input type="hidden" name="items[{{ $idx }}][msu_item_master_id]" class="item-select-hidden" value="{{ $row['msu_item_master_id'] ?? '' }}" required>
                                    <div class="text-danger small mt-1 d-none item-error"></div>
                                </div>
                            </td>
                            <td><input type="text" name="items[{{ $idx }}][hsn]" class="form-control hsn-input" value="{{ $row['hsn'] ?? '' }}" required minlength="4" {{ $itemHsnReadonly ? 'readonly tabindex="-1"' : '' }}></td>
                            <td><input type="number" step="0.001" min="0.001" name="items[{{ $idx }}][quantity]" class="form-control qty-input" value="{{ $qtyRaw === '' || $qtyRaw === null ? '' : $qtyRaw }}" required></td>
                            <td><input type="number" step="0.01" min="0" name="items[{{ $idx }}][price]" class="form-control price-input" value="{{ number_format($rate, 2, '.', '') }}" required {{ $itemRateReadonly ? 'readonly tabindex="-1"' : '' }}></td>
                            <td><input type="text" class="form-control gross-value" value="{{ number_format($gross, 2, '.', '') }}" readonly tabindex="-1"></td>
                            <td><input type="number" step="0.01" min="0" name="items[{{ $idx }}][tax]" class="form-control tax-input" value="{{ number_format($taxPct, 2, '.', '') }}" {{ $itemTaxReadonly ? 'readonly tabindex="-1"' : '' }}></td>
                            <td><input type="text" class="form-control tax-value" value="{{ number_format($taxValue, 2, '.', '') }}" readonly tabindex="-1"></td>
                            <td><input type="text" class="form-control net-value" value="{{ number_format($net, 2, '.', '') }}" readonly tabindex="-1"></td>
                            <td><button type="button" class="btn btn-sm btn-outline-danger remove-item-row"><i class="bx bx-trash"></i></button></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <button type="button" class="btn btn-sm btn-primary mt-3" id="add-item-row"><i class="bx bx-plus"></i> Add Item</button>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Miscellaneous Charges</h5>
            </div>
            <button type="button" class="btn btn-sm btn-primary" id="add-charge-row"><i class="bx bx-plus"></i> Add Charge</button>
        </div>
        <div class="card-body">
            <div class="po-charges-wrap">
                <datalist id="po-charge-options">
                    @foreach(($miscellaneousCharges ?? collect()) as $charge)
                        @php
                            $chargeLabel = ($charge->code ? $charge->code.' - ' : '').$charge->charge_name;
                        @endphp
                        <option value="{{ $chargeLabel }}"
                            data-name="{{ $charge->charge_name }}"
                            data-amount="{{ number_format((float) $charge->amount, 2, '.', '') }}"></option>
                    @endforeach
                </datalist>
                <div class="po-charges-head">
                    <div>Charge Name</div>
                    <div class="text-end">Amount</div>
                    <div class="text-end">Tax %</div>
                    <div class="text-end">Tax Value</div>
                    <div class="text-end">Total</div>
                    <div></div>
                </div>
                <div id="po-charges-body">
                    @php
                        $oldCharges = old('charges', $isEdit ? $msuPurchaseOrder->charges->toArray() : []);
                        $selectedMiscAmount = 0;
                        $selectedMiscTaxAmount = 0;
                    @endphp
                    @foreach($oldCharges as $idx => $row)
                    @php
                        $amount = (float) ($row['amount'] ?? 0);
                        $taxPct = (float) ($row['tax_percentage'] ?? 0);
                        $taxVal = round($amount * $taxPct / 100, 2);
                        $total = round($amount + $taxVal, 2);
                        $selectedMiscAmount += $amount;
                        $selectedMiscTaxAmount += $taxVal;
                    @endphp
                    @php
                        $selectedChargeName = $row['charge_name'] ?? '';
                        $selectedChargeLabel = $selectedChargeName;
                        if ($selectedChargeName) {
                            $foundCharge = collect($miscellaneousCharges ?? [])->first(function ($charge) use ($selectedChargeName) {
                                return $charge->charge_name === $selectedChargeName;
                            });
                            if ($foundCharge) {
                                $selectedChargeLabel = ($foundCharge->code ? $foundCharge->code.' - ' : '').$foundCharge->charge_name;
                            }
                        }
                    @endphp
                    <div class="po-charge-row">
                        <div class="po-charge-name">
                            <label class="form-label po-charge-mobile-label">Charge Name</label>
                            <div class="position-relative">
                                <input type="text" class="form-control charge-autocomplete" list="po-charge-options"
                                       placeholder="Type to search charge..." value="{{ $selectedChargeLabel }}"
                                       autocomplete="off" required title="{{ $selectedChargeLabel }}">
                                <input type="hidden" name="charges[{{ $idx }}][charge_name]" class="charge-select-hidden"
                                       value="{{ $selectedChargeName }}" required>
                                <div class="text-danger small mt-1 d-none charge-error"></div>
                            </div>
                        </div>
                        <div class="po-charge-amount-wrap">
                            <label class="form-label po-charge-mobile-label">Amount</label>
                            <input type="number" step="0.01" min="0" name="charges[{{ $idx }}][amount]" class="form-control charge-amount" value="{{ number_format($amount, 2, '.', '') }}" required>
                        </div>
                        <div class="po-charge-tax-wrap">
                            <label class="form-label po-charge-mobile-label">Tax %</label>
                            <input type="number" step="0.01" min="0" name="charges[{{ $idx }}][tax_percentage]" class="form-control charge-tax" value="{{ number_format($taxPct, 2, '.', '') }}">
                        </div>
                        <div class="po-charge-tax-val-wrap">
                            <label class="form-label po-charge-mobile-label">Tax Value</label>
                            <input type="text" class="form-control charge-tax-val" value="{{ number_format($taxVal, 2, '.', '') }}" readonly tabindex="-1">
                        </div>
                        <div class="po-charge-total-wrap">
                            <label class="form-label po-charge-mobile-label">Total</label>
                            <input type="text" class="form-control charge-total" value="{{ number_format($total, 2, '.', '') }}" readonly tabindex="-1">
                        </div>
                        <div class="po-charge-action">
                            <button type="button" class="btn btn-sm btn-outline-danger remove-charge-row"><i class="bx bx-trash"></i></button>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Overall PO Values</h5></div>
        <div class="card-body">
            @php
                $selectedItemValue = (float) old('total_value', $isEdit ? $msuPurchaseOrder->total_value : 0);
                $selectedItemMiscValues = $selectedItemValue + $selectedMiscAmount;
            @endphp
            <div class="row g-3">
                <div class="col">
                    <label class="form-label">Total Items Value</label>
                    <input type="text" id="total_value_display" class="form-control" readonly
                           value="{{ number_format($selectedItemValue, 2, '.', '') }}">
                </div>
                <div class="col">
                    <label class="form-label">Total Items Tax Value</label>
                    <input type="text" id="item_tax_total_display" class="form-control" readonly
                           value="{{ number_format($selectedItemTaxAmount, 2, '.', '') }}">
                </div>
                <div class="col">
                    <label class="form-label">Total Misc Value</label>
                    <input type="text" id="misc_total_display" class="form-control" readonly
                           value="{{ number_format($selectedMiscAmount, 2, '.', '') }}">
                </div>
                <div class="col">
                    <label class="form-label">Total Misc Tax Value</label>
                    <input type="text" id="misc_tax_total_display" class="form-control" readonly
                           value="{{ number_format($selectedMiscTaxAmount, 2, '.', '') }}">
                </div>
                <div class="col">
                    <label class="form-label">Total Item+Misc Values</label>
                    <input type="text" id="item_misc_total_display" class="form-control" readonly
                           value="{{ number_format($selectedItemMiscValues, 2, '.', '') }}">
                </div>
                <div class="col" id="col-sgst">
                    <label class="form-label">SGST</label>
                    <input type="text" id="sgst" class="form-control" readonly tabindex="-1"
                           value="{{ number_format((float) old('sgst', $isEdit ? $msuPurchaseOrder->sgst : 0), 2, '.', '') }}">
                </div>
                <div class="col" id="col-cgst">
                    <label class="form-label">CGST</label>
                    <input type="text" id="cgst" class="form-control" readonly tabindex="-1"
                           value="{{ number_format((float) old('cgst', $isEdit ? $msuPurchaseOrder->cgst : 0), 2, '.', '') }}">
                </div>
                <div class="col" id="col-igst">
                    <label class="form-label">IGST</label>
                    <input type="text" id="igst" class="form-control" readonly tabindex="-1"
                           value="{{ number_format((float) old('igst', $isEdit ? ($msuPurchaseOrder->igst ?? 0) : 0), 2, '.', '') }}">
                </div>
                <div class="col">
                    <label class="form-label">Grand Total</label>
                    <input type="text" id="grand_total_display" class="form-control fw-bold" readonly
                           value="{{ number_format((float) old('grand_total', $isEdit ? $msuPurchaseOrder->grand_total : 0), 2, '.', '') }}">
                </div>
            </div>
        </div>
    </div>

    <div class="mb-4">
        <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update PO' : 'Create PO' }}</button>
        <a href="{{ route('msu-purchase-orders.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<template id="po-item-row-template">
    <tr class="po-item-row">
        <td>
            <div class="position-relative">
                <input type="text" class="form-control item-autocomplete" list="po-item-options" placeholder="Type to search item..." autocomplete="off" required>
                <input type="hidden" name="items[__INDEX__][msu_item_master_id]" class="item-select-hidden" required>
                <div class="text-danger small mt-1 d-none item-error"></div>
            </div>
        </td>
        <td><input type="text" name="items[__INDEX__][hsn]" class="form-control hsn-input" required minlength="4"></td>
        <td><input type="number" step="0.001" min="0.001" name="items[__INDEX__][quantity]" class="form-control qty-input" value="" required></td>
        <td><input type="number" step="0.01" min="0" name="items[__INDEX__][price]" class="form-control price-input" value="0" required></td>
        <td><input type="text" class="form-control gross-value" value="0.00" readonly tabindex="-1"></td>
        <td><input type="number" step="0.01" min="0" name="items[__INDEX__][tax]" class="form-control tax-input" value="0"></td>
        <td><input type="text" class="form-control tax-value" value="0.00" readonly tabindex="-1"></td>
        <td><input type="text" class="form-control net-value" value="0.00" readonly tabindex="-1"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger remove-item-row"><i class="bx bx-trash"></i></button></td>
    </tr>
</template>

<template id="po-charge-row-template">
    <div class="po-charge-row">
        <div class="po-charge-name">
            <label class="form-label po-charge-mobile-label">Charge Name</label>
            <div class="position-relative">
                <input type="text" class="form-control charge-autocomplete" list="po-charge-options"
                       placeholder="Type to search charge..." autocomplete="off" required>
                <input type="hidden" name="charges[__INDEX__][charge_name]" class="charge-select-hidden" required>
                <div class="text-danger small mt-1 d-none charge-error"></div>
            </div>
        </div>
        <div class="po-charge-amount-wrap">
            <label class="form-label po-charge-mobile-label">Amount</label>
            <input type="number" step="0.01" min="0" name="charges[__INDEX__][amount]" class="form-control charge-amount" value="0.00" required>
        </div>
        <div class="po-charge-tax-wrap">
            <label class="form-label po-charge-mobile-label">Tax %</label>
            <input type="number" step="0.01" min="0" name="charges[__INDEX__][tax_percentage]" class="form-control charge-tax" value="0.00">
        </div>
        <div class="po-charge-tax-val-wrap">
            <label class="form-label po-charge-mobile-label">Tax Value</label>
            <input type="text" class="form-control charge-tax-val" value="0.00" readonly tabindex="-1">
        </div>
        <div class="po-charge-total-wrap">
            <label class="form-label po-charge-mobile-label">Total</label>
            <input type="text" class="form-control charge-total" value="0.00" readonly tabindex="-1">
        </div>
        <div class="po-charge-action">
            <button type="button" class="btn btn-sm btn-outline-danger remove-charge-row"><i class="bx bx-trash"></i></button>
        </div>
    </div>
</template>

{{-- Manage masters modals --}}
<div class="modal fade" id="paymentTermsManageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Payment Terms</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table align-middle" id="payment-terms-manage-table">
                        <thead>
                            <tr>
                                <th>Payment Mode</th>
                                <th>Payment Description</th>
                                <th style="width:140px">Short Name</th>
                                <th class="text-center" style="width:90px">Active</th>
                                <th class="text-center" style="width:80px">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary" id="payment-terms-add-row">
                    <i class="bx bx-plus"></i> Add
                </button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="payment-terms-save-btn">Save</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="miscChargesManageModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Miscellaneous Charges</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table class="table align-middle" id="misc-charges-manage-table">
                        <thead>
                            <tr>
                                <th>Description</th>
                                <th style="width:120px">Short Name</th>
                                <th style="width:120px">Amount</th>
                                <th class="text-center" style="width:80px">Active</th>
                                <th class="text-center" style="width:70px">Action</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <button type="button" class="btn btn-sm btn-outline-primary" id="misc-charges-add-row">
                    <i class="bx bx-plus"></i> Add
                </button>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="misc-charges-save-btn">Save</button>
            </div>
        </div>
    </div>
</div>
