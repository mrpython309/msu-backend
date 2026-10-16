@php
    $isEdit = isset($msuWorkOrder);
    $oldItems = old('items');
    if ($oldItems === null && $isEdit) {
        $oldItems = $msuWorkOrder->items->map(fn ($item) => [
            'scope_of_work' => $item->scope_of_work,
            'work_description' => $item->work_description,
            'hsn' => $item->hsn,
            'unit_id' => $item->unit_id,
            'quantity' => $item->quantity,
            'price' => $item->price,
            'tax' => $item->tax,
            'net_value' => $item->net_value,
        ])->toArray();
    }
    $oldItems = $oldItems ?: [['scope_of_work' => '', 'work_description' => '', 'hsn' => '', 'unit_id' => '', 'quantity' => 1, 'price' => 0, 'tax' => 0, 'net_value' => 0]];
@endphp

<style>
    #wo-items-table .form-control:not(.qty-input) {
        border: none !important;
        background-color: transparent !important;
        box-shadow: none !important;
        padding-left: 5px;
        padding-right: 5px;
        border-radius: 0;
    }
    #wo-items-table .form-control:not(.qty-input):focus {
        background-color: rgba(0,0,0,0.03) !important;
        outline: none;
    }
    #wo-items-table td:nth-child(3) {
        min-width: 130px;
        white-space: nowrap;
    }
    #wo-items-table .unit-select {
        min-width: 120px;
        width: 100%;
    }
    #wo-items-table input[type=number]::-webkit-outer-spin-button,
    #wo-items-table input[type=number]::-webkit-inner-spin-button,
    #wo-charges-table input[type=number]::-webkit-outer-spin-button,
    #wo-charges-table input[type=number]::-webkit-inner-spin-button {
        -webkit-appearance: none;
        margin: 0;
    }
    #wo-items-table input[type=number],
    #wo-charges-table input[type=number] {
        -moz-appearance: textfield;
        appearance: textfield;
    }
</style>

@if ($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ $isEdit ? route('msu-work-orders.update', $msuWorkOrder) : route('msu-work-orders.store') }}" id="msu-wo-form">
    @csrf
    @if($isEdit) @method('PUT') @endif

    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">WO Details</h5></div>
        <div class="card-body">
            <div class="row row-cols-1 row-cols-md-4 g-3">
                <div class="col">
                    <label class="form-label">WO Number</label>
                    <input type="text" class="form-control" id="wo_number_preview"
                           value="{{ $isEdit ? ($displayWoNumber ?? $msuWorkOrder->wo_number) : ($previewWoNumber ?? 'MSULLP/BRN/WO/----/--/0001') }}"
                           disabled>
                </div>
                <div class="col">
                    <label class="form-label">WO Date <span class="text-danger">*</span></label>
                    <input type="date" name="wo_date" id="wo_date" class="form-control @error('wo_date') is-invalid @enderror"
                           value="{{ old('wo_date', $isEdit ? $msuWorkOrder->wo_date?->format('Y-m-d') : now()->format('Y-m-d')) }}"
                           max="{{ now()->format('Y-m-d') }}" required>
                    @error('wo_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col">
                    <label class="form-label">Contract Period - From</label>
                    <input type="date" name="contract_period_from" id="contract_period_from" class="form-control"
                           value="{{ old('contract_period_from', $isEdit ? $msuWorkOrder->contract_period_from?->format('Y-m-d') : '') }}">
                </div>
                <div class="col">
                    <label class="form-label">Contract Period - To</label>
                    <input type="date" name="contract_period_to" id="contract_period_to" class="form-control"
                           value="{{ old('contract_period_to', $isEdit ? $msuWorkOrder->contract_period_to?->format('Y-m-d') : '') }}">
                </div>
                <div class="col">
                    <label class="form-label">Frequency</label>
                    @php
                        $selectedFrequency = old('frequency', $isEdit ? ($msuWorkOrder->frequencyEnum()?->value ?? '') : '');
                    @endphp
                    <select name="frequency" id="frequency" class="form-select">
                        <option value="">None</option>
                        @foreach(\App\Enums\Procurement\MsuWorkOrderFrequency::cases() as $frequency)
                            <option value="{{ $frequency->value }}" {{ (string) $selectedFrequency === $frequency->value ? 'selected' : '' }}>
                                {{ $frequency->label() }}
                            </option>
                        @endforeach
                    </select>
                    <small class="text-muted">A new work order is created automatically at this interval. Generation stops after the contract end date.</small>
                </div>
                
                <div class="col-md-6">
                    <label class="form-label">Subject</label>
                    <textarea name="subject" id="subject" class="form-control" rows="1" placeholder="Enter work subject">{{ old('subject', $isEdit ? $msuWorkOrder->subject : '') }}</textarea>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Remarks</label>
                    <textarea name="remarks" id="remarks" class="form-control" rows="1" placeholder="Enter remarks">{{ old('remarks', $isEdit ? $msuWorkOrder->remarks : '') }}</textarea>
                </div>

                <div class="col">
                    <label class="form-label">Vendor <span class="text-danger">*</span></label>
                    <select name="vendor_id" id="vendor_id" class="form-select @error('vendor_id') is-invalid @enderror" required>
                        <option value="" data-state="">Select Vendor</option>
                        @foreach($vendors as $vendor)
                            <option value="{{ $vendor->id }}"
                                data-state="{{ $vendor->state }}"
                                {{ old('vendor_id', $isEdit ? $msuWorkOrder->vendor_id : '') == $vendor->id ? 'selected' : '' }}>
                                {{ $vendor->company_code ? $vendor->company_code.' - ' : '' }}{{ $vendor->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('vendor_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col">
                    <label class="form-label">Branch <span class="text-danger">*</span></label>
                    @php
                        $selectedBranchId = old('branch_id', $isEdit ? $msuWorkOrder->branch_id : '');
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
                    @php $selectedEmployeeId = old('employee_master_id', $isEdit ? $msuWorkOrder->employee_master_id : ''); @endphp
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
                    @php $selectedDepartmentId = old('department_id', $isEdit ? $msuWorkOrder->department_id : ''); @endphp
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
                        $selectedPaymentTermId = old('payment_term_id', $isEdit ? $msuWorkOrder->payment_term_id : '');
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
                        <button type="button" class="btn btn-outline-secondary" id="manage-payment-terms-btn" title="Manage Payment Terms">
                            <i class="bx bx-edit-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="col">
                    <label class="form-label">Shipping Details</label>
                    @php $selectedShippingId = old('shipping_detail_id', $isEdit ? $msuWorkOrder->shipping_detail_id : ''); @endphp
                    <select name="shipping_detail_id" id="shipping_detail_id" class="form-select">
                        <option value="">Select Shipping</option>
                        @foreach(($shippingDetails ?? collect()) as $shipping)
                            <option value="{{ $shipping->id }}"
                                {{ (string) $selectedShippingId === (string) $shipping->id ? 'selected' : '' }}>
                                {{ $shipping->shipping_name }}
                                @if($shipping->contact_person)
                                    ({{ $shipping->contact_person }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Work Details</h5>
            </div>
            <button type="button" class="btn btn-sm btn-primary" id="add-item-row"><i class="bx bx-plus"></i> Add Work Detail</button>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table" id="wo-items-table">
                    <thead>
                        <tr>
                            <th style="min-width:300px">Scope of Work <span class="text-danger">*</span></th>
                            <th style="min-width:300px">Work Description <span class="text-danger">*</span></th>
                            <th style="min-width:90px">HSN</th>
                            <th style="min-width:130px">Unit</th>
                            <th style="min-width:120px">Qty <span class="text-danger">*</span></th>
                            <th style="min-width:100px">Rate <span class="text-danger">*</span></th>
                            <th style="min-width:100px">Gross Value</th>
                            <th style="min-width:80px">Tax %</th>
                            <th style="min-width:100px">Tax Value</th>
                            <th style="min-width:110px">Net Value</th>
                            <th width="60"></th>
                        </tr>
                    </thead>
                    <tbody id="wo-items-body">
                        @foreach($oldItems as $idx => $row)
                        @php
                            $qty = (float) ($row['quantity'] ?? 1);
                            $rate = (float) ($row['price'] ?? 0);
                            $taxPct = (float) ($row['tax'] ?? 0);
                            $gross = round($qty * $rate, 2);
                            $taxValue = round($gross * $taxPct / 100, 2);
                            $net = isset($row['net_value']) ? (float) $row['net_value'] : round($gross + $taxValue, 2);
                        @endphp
                        <tr class="wo-item-row">
                            <td><input type="text" name="items[{{ $idx }}][scope_of_work]" class="form-control" value="{{ $row['scope_of_work'] ?? '' }}" placeholder="Scope of work..." required></td>
                            <td><input type="text" name="items[{{ $idx }}][work_description]" class="form-control" value="{{ $row['work_description'] ?? '' }}" placeholder="Describe work..." required></td>
                            <td><input type="text" name="items[{{ $idx }}][hsn]" class="form-control hsn-input" value="{{ $row['hsn'] ?? '' }}" placeholder="HSN"></td>
                            <td>
                                <select name="items[{{ $idx }}][unit_id]" class="form-select unit-select">
                                    <option value="">Select Unit</option>
                                    @foreach(($units ?? collect()) as $unit)
                                        <option value="{{ $unit->id }}" {{ (string) ($row['unit_id'] ?? '') === (string) $unit->id ? 'selected' : '' }}>
                                            {{ $unit->unit_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="number" step="0.001" min="0.001" name="items[{{ $idx }}][quantity]" class="form-control qty-input" value="{{ $row['quantity'] ?? 1 }}" required></td>
                            <td><input type="number" step="0.01" min="0" name="items[{{ $idx }}][price]" class="form-control price-input" value="{{ number_format($rate, 2, '.', '') }}" required></td>
                            <td><input type="text" class="form-control gross-value" value="{{ number_format($gross, 2, '.', '') }}" readonly tabindex="-1"></td>
                            <td><input type="number" step="0.01" min="0" name="items[{{ $idx }}][tax]" class="form-control tax-input" value="{{ number_format($taxPct, 2, '.', '') }}"></td>
                            <td><input type="text" class="form-control tax-value" value="{{ number_format($taxValue, 2, '.', '') }}" readonly tabindex="-1"></td>
                            <td><input type="text" class="form-control net-value" value="{{ number_format($net, 2, '.', '') }}" readonly tabindex="-1"></td>
                            <td><button type="button" class="btn btn-sm btn-outline-danger remove-item-row"><i class="bx bx-trash"></i></button></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
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
            <div class="table-responsive">
                <table class="table" id="wo-charges-table">
                    <thead>
                        <tr>
                            <th>Charge Name</th>
                            <th>Amount</th>
                            <th>Tax %</th>
                            <th>Tax Value</th>
                            <th>Total</th>
                            <th width="60"></th>
                        </tr>
                    </thead>
                    <tbody id="wo-charges-body">
                        @php
                            $oldCharges = old('charges', $isEdit ? $msuWorkOrder->charges->toArray() : []);
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
                        <tr class="wo-charge-row">
                            <td>
                                <select name="charges[{{ $idx }}][charge_name]" class="form-select charge-select" required>
                                    <option value="" data-amount="0">Select Charge</option>
                                    @foreach(($miscellaneousCharges ?? collect()) as $charge)
                                        <option value="{{ $charge->charge_name }}" 
                                            data-amount="{{ number_format((float) $charge->amount, 2, '.', '') }}"
                                            {{ ($row['charge_name'] ?? '') === $charge->charge_name ? 'selected' : '' }}>
                                            {{ $charge->code ? $charge->code.' - ' : '' }}{{ $charge->charge_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="number" step="0.01" min="0" name="charges[{{ $idx }}][amount]" class="form-control charge-amount" value="{{ number_format($amount, 2, '.', '') }}" required></td>
                            <td><input type="number" step="0.01" min="0" name="charges[{{ $idx }}][tax_percentage]" class="form-control charge-tax" value="{{ number_format($taxPct, 2, '.', '') }}"></td>
                            <td><input type="text" class="form-control charge-tax-val" value="{{ number_format($taxVal, 2, '.', '') }}" readonly tabindex="-1"></td>
                            <td><input type="text" class="form-control charge-total" value="{{ number_format($total, 2, '.', '') }}" readonly tabindex="-1"></td>
                            <td><button type="button" class="btn btn-sm btn-outline-danger remove-charge-row"><i class="bx bx-trash"></i></button></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Overall WO Values</h5></div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Total Value (Work)</label>
                    <input type="text" id="total_value_display" class="form-control" readonly
                           value="{{ number_format((float) old('total_value', $isEdit ? $msuWorkOrder->total_value : 0), 2, '.', '') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label" id="misc_charges_label">Misc. Charges{{ $selectedMiscTaxAmount > 0 ? ' (Tax Inc.)' : '' }}</label>
                    <input type="text" id="misc_total_display" class="form-control" readonly
                           value="{{ number_format($selectedMiscAmount + $selectedMiscTaxAmount, 2, '.', '') }}">
                </div>
                <div class="col-md-2" id="col-sgst">
                    <label class="form-label">SGST</label>
                    <input type="text" id="sgst" class="form-control" readonly tabindex="-1"
                           value="{{ number_format((float) old('sgst', $isEdit ? $msuWorkOrder->sgst : 0), 2, '.', '') }}">
                </div>
                <div class="col-md-2" id="col-cgst">
                    <label class="form-label">CGST</label>
                    <input type="text" id="cgst" class="form-control" readonly tabindex="-1"
                           value="{{ number_format((float) old('cgst', $isEdit ? $msuWorkOrder->cgst : 0), 2, '.', '') }}">
                </div>
                <div class="col-md-2" id="col-igst">
                    <label class="form-label">IGST</label>
                    <input type="text" id="igst" class="form-control" readonly tabindex="-1"
                           value="{{ number_format((float) old('igst', $isEdit ? ($msuWorkOrder->igst ?? 0) : 0), 2, '.', '') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Grand Total</label>
                    <input type="text" id="grand_total_display" class="form-control fw-bold" readonly
                           value="{{ number_format((float) old('grand_total', $isEdit ? $msuWorkOrder->grand_total : 0), 2, '.', '') }}">
                </div>
            </div>
        </div>
    </div>

    <div class="mb-4">
        <button type="submit" class="btn btn-primary">{{ $isEdit ? 'Update WO' : 'Create WO' }}</button>
        <a href="{{ route('msu-work-orders.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<template id="wo-item-row-template">
    <tr class="wo-item-row">
        <td><input type="text" name="items[__INDEX__][scope_of_work]" class="form-control" placeholder="Scope of work..." required></td>
        <td><input type="text" name="items[__INDEX__][work_description]" class="form-control" placeholder="Describe work..." required></td>
        <td><input type="text" name="items[__INDEX__][hsn]" class="form-control hsn-input" placeholder="HSN"></td>
        <td>
            <select name="items[__INDEX__][unit_id]" class="form-select unit-select">
                <option value="">Select Unit</option>
                @foreach(($units ?? collect()) as $unit)
                    <option value="{{ $unit->id }}">{{ $unit->unit_name }}</option>
                @endforeach
            </select>
        </td>
        <td><input type="number" step="0.001" min="0.001" name="items[__INDEX__][quantity]" class="form-control qty-input" value="1" required></td>
        <td><input type="number" step="0.01" min="0" name="items[__INDEX__][price]" class="form-control price-input" value="0" required></td>
        <td><input type="text" class="form-control gross-value" value="0.00" readonly tabindex="-1"></td>
        <td><input type="number" step="0.01" min="0" name="items[__INDEX__][tax]" class="form-control tax-input" value="0"></td>
        <td><input type="text" class="form-control tax-value" value="0.00" readonly tabindex="-1"></td>
        <td><input type="text" class="form-control net-value" value="0.00" readonly tabindex="-1"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger remove-item-row"><i class="bx bx-trash"></i></button></td>
    </tr>
</template>

<template id="wo-charge-row-template">
    <tr class="wo-charge-row">
        <td>
            <select name="charges[__INDEX__][charge_name]" class="form-select charge-select" required>
                <option value="" data-amount="0">Select Charge</option>
                @foreach(($miscellaneousCharges ?? collect()) as $charge)
                    <option value="{{ $charge->charge_name }}" data-amount="{{ number_format((float) $charge->amount, 2, '.', '') }}">
                        {{ $charge->code ? $charge->code.' - ' : '' }}{{ $charge->charge_name }}
                    </option>
                @endforeach
            </select>
        </td>
        <td><input type="number" step="0.01" min="0" name="charges[__INDEX__][amount]" class="form-control charge-amount" value="0.00" required></td>
        <td><input type="number" step="0.01" min="0" name="charges[__INDEX__][tax_percentage]" class="form-control charge-tax" value="0.00"></td>
        <td><input type="text" class="form-control charge-tax-val" value="0.00" readonly tabindex="-1"></td>
        <td><input type="text" class="form-control charge-total" value="0.00" readonly tabindex="-1"></td>
        <td><button type="button" class="btn btn-sm btn-outline-danger remove-charge-row"><i class="bx bx-trash"></i></button></td>
    </tr>
</template>

