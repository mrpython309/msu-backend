@extends('layouts/contentNavbarLayout')

@section('title', 'Create GRN')

@section('page-style')
<style>
#grn-items-table thead th {
    vertical-align: middle;
    white-space: nowrap;
}
#grn-items-table .received-qty {
    min-width: 110px;
}
#grn-items-table .item-flag-cell {
    min-width: 110px;
    text-align: center;
}
#grn-items-table .item-is-group:disabled {
    opacity: 0.35;
    cursor: not-allowed;
}
</style>
@endsection

@section('content')
<h4 class="mb-3">Create Goods Receipt Note</h4>

@if ($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ route('msu-grns.store') }}" id="msu-grn-form">
    @csrf

    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">GRN Details</h5></div>
        <div class="card-body">
            <div class="row row-cols-1 row-cols-md-4 g-3">
                <div class="col">
                    <label class="form-label">GRN Number</label>
                    <input type="text" id="grn_number_preview" class="form-control" value="{{ $previewGrnNumber ?? '' }}" disabled>
                </div>
                <div class="col">
                    <label class="form-label">GRN Date <span class="text-danger">*</span></label>
                    <input type="date" name="grn_date" id="grn_date" class="form-control" required
                           value="{{ old('grn_date', now()->format('Y-m-d')) }}">
                </div>
                <div class="col">
                    <label class="form-label">Branch <span class="text-danger">*</span></label>
                    @php
                        $selectedBranchId = old('branch_id');
                        if ($selectedBranchId && ! ($branches ?? collect())->contains('id', (int) $selectedBranchId)) {
                            $selectedBranchId = '';
                        }
                    @endphp
                    <select name="branch_id" id="branch_id" class="form-select select2 @error('branch_id') is-invalid @enderror" required>
                        <option value="" data-code="BRN" data-company-code="{{ $companyCode ?? 'MSULLP' }}">Select Branch</option>
                        @foreach(($branches ?? collect()) as $branch)
                            <option value="{{ $branch->id }}"
                                data-code="{{ $branch->code ?: $branch->name }}"
                                data-company-code="{{ $branch->company?->code ?: ($companyCode ?? 'MSULLP') }}"
                                {{ (string) $selectedBranchId === (string) $branch->id ? 'selected' : '' }}>
                                {{ $branch->code ? $branch->code.' - ' : '' }}{{ $branch->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('branch_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col">
                    <label class="form-label">PO Number <span class="text-danger">*</span></label>
                    <select name="msu_purchase_order_id" id="msu_purchase_order_id" class="form-select select2 @error('msu_purchase_order_id') is-invalid @enderror" required disabled>
                        <option value="">Select Branch first</option>
                        @foreach($purchaseOrders as $po)
                            <option value="{{ $po->id }}"
                                data-branch-id="{{ $po->branch_id }}"
                                data-vendor="{{ $po->vendor?->name }}"
                                data-department="{{ $po->department_id }}"
                                data-employee="{{ $po->employee_master_id }}"
                                {{ (string) old('msu_purchase_order_id') === (string) $po->id ? 'selected' : '' }}>
                                {{ $po->po_number }}
                                @if($po->vendor)
                                    — {{ $po->vendor->name }}
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('msu_purchase_order_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col">
                    <label class="form-label">Vendor</label>
                    <input type="text" id="vendor_name_display" class="form-control" value="" disabled>
                </div>
                <div class="col">
                    <label class="form-label">Department</label>
                    <select name="department_id" id="department_id" class="form-select select2">
                        <option value="">Select Department</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" {{ (string) old('department_id') === (string) $department->id ? 'selected' : '' }}>
                                {{ $department->department_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col">
                    <label class="form-label">Employee</label>
                    <select name="employee_master_id" id="employee_master_id" class="form-select select2">
                        <option value="">Select Employee</option>
                        @foreach($employees as $employee)
                            <option value="{{ $employee->id }}" {{ (string) old('employee_master_id') === (string) $employee->id ? 'selected' : '' }}>
                                {{ $employee->employee_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col">
                    <label class="form-label">Vendor Invoice No <span class="text-danger">*</span></label>
                    <input type="text" name="vendor_invoice_number" class="form-control" value="{{ old('vendor_invoice_number') }}" required>
                </div>
                <div class="col">
                    <label class="form-label">Vendor Invoice Date <span class="text-danger">*</span></label>
                    <input type="date" name="vendor_invoice_date" class="form-control" value="{{ old('vendor_invoice_date') }}" required>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-12">
                    <label class="form-label">Remarks</label>
                    <textarea name="remarks" class="form-control" rows="2">{{ old('remarks') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">GRN Item Details</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle" id="grn-items-table">
                    <thead>
                        <tr>
                            <th style="min-width:100px">Item Code</th>
                            <th style="min-width:200px">Item Name</th>
                            <th style="min-width:100px">HSN</th>
                            <th class="text-end text-nowrap" style="min-width:110px">Ordered Qty</th>
                            <th class="text-end text-nowrap" style="min-width:150px">Previously Received</th>
                            <th class="text-end text-nowrap" style="min-width:100px">Balance</th>
                            <th class="text-end text-nowrap" style="min-width:150px">Received Qty<span class="text-danger">*</span></th>
                            <th class="text-end text-nowrap" style="min-width:100px">Rate</th>
                            <th class="text-end text-nowrap" style="min-width:110px">Gross Value</th>
                            <th class="text-end text-nowrap" style="min-width:80px">GST %</th>
                            <th class="text-end text-nowrap" style="min-width:110px">GST Value</th>
                            <th class="text-end text-nowrap" style="min-width:110px">Net Value</th>
                            <th class="text-center text-nowrap" style="min-width:90px">CAPEX</th>
                            <th class="text-center text-nowrap" style="min-width:120px">Group Asset</th>
                            <th width="60"></th>
                        </tr>
                    </thead>
                    <tbody id="grn-items-body">
                        <tr id="grn-items-empty">
                            <td colspan="15" class="text-center text-muted py-4">Select a branch and PO number to load items.</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mb-4">
        <button type="submit" class="btn btn-primary">Submit</button>
        <a href="{{ route('msu-grns.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
</form>
@endsection

@section('page-script')
<script>
(function() {
    function boot() {
        if (typeof window.jQuery === 'undefined') {
            setTimeout(boot, 50);
            return;
        }
        initGrnForm(window.jQuery);
    }

    function initGrnForm($) {
        var poItemsUrl = @json(url('/msu-grns/po-items'));
        var previewUrl = @json(route('msu-grns.preview-grn-number'));
        var formCompanyId = @json($formCompanyId ?? session('global_company_id'));
        var oldPoId = @json(old('msu_purchase_order_id'));
        var $poSelect = $('#msu_purchase_order_id');
        var allPoOptions = $poSelect.find('option').clone();

        function initSearchableSelect($el) {
            if (!$el || !$el.length || typeof $.fn.select2 !== 'function') return;
            $el.each(function() {
                var $select = $(this);
                if ($select.hasClass('select2-hidden-accessible')) {
                    $select.select2('destroy');
                }
                $select.select2({
                    placeholder: 'Select',
                    allowClear: !$select.prop('required'),
                    width: '100%',
                    minimumResultsForSearch: 0
                });
            });
        }

        function clearPoItems() {
            $('#vendor_name_display').val('');
            $('#grn-items-body').html('<tr id="grn-items-empty"><td colspan="15" class="text-center text-muted py-4">Select a branch and PO number to load items.</td></tr>');
        }

        function filterPurchaseOrders(preserveSelected) {
            var branchId = String($('#branch_id').val() || '');
            var selectedPo = preserveSelected ? String($poSelect.val() || oldPoId || '') : '';

            $poSelect.empty();
            if (!branchId) {
                $poSelect.append('<option value="">Select Branch first</option>');
                $poSelect.prop('disabled', true).val('');
                initSearchableSelect($poSelect);
                clearPoItems();
                return;
            }

            $poSelect.prop('disabled', false);
            $poSelect.append('<option value="">Select PO</option>');
            var matched = 0;
            allPoOptions.each(function() {
                var $opt = $(this);
                var value = String($opt.attr('value') || '');
                if (!value) return;
                if (String($opt.attr('data-branch-id') || '') === branchId) {
                    var $clone = $opt.clone();
                    if (selectedPo && selectedPo === value) {
                        $clone.prop('selected', true);
                    }
                    $poSelect.append($clone);
                    matched++;
                }
            });

            if (!matched) {
                $poSelect.append('<option value="" disabled>No POs available for this branch</option>');
            }

            if (!selectedPo || String($poSelect.val() || '') !== selectedPo) {
                $poSelect.val('');
                clearPoItems();
            }
            initSearchableSelect($poSelect);
        }

        function refreshGrnNumber() {
            $.get(previewUrl, {
                branch_id: $('#branch_id').val() || '',
                msu_purchase_order_id: $('#msu_purchase_order_id').val() || '',
                grn_date: $('#grn_date').val() || '',
                company_id: formCompanyId || ''
            }, function(res) {
                if (res.success && res.grn_number) {
                    $('#grn_number_preview').val(res.grn_number);
                }
            });
        }

        function renderItems(items, poData) {
            var $body = $('#grn-items-body');
            $body.empty();
            if (!items || !items.length) {
                $body.append('<tr id="grn-items-empty"><td colspan="15" class="text-center text-muted py-4">No open quantities on this PO.</td></tr>');
                return;
            }
            items.forEach(function(item, index) {
                var disabled = parseFloat(item.balance_quantity) <= 0;
                var rate = parseFloat(item.price || 0);
                var taxPct = parseFloat(item.tax || 0);
                var defaultQty = disabled ? 0 : parseFloat(item.balance_quantity);
                var gross = defaultQty * rate;
                var taxVal = gross * taxPct / 100;
                var net = gross + taxVal;

                var row = '<tr class="grn-item-row">' +
                    '<td>' + (item.item_code || '--') +
                    '<input type="hidden" name="items[' + index + '][msu_purchase_order_item_id]" value="' + item.id + '">' +
                    '</td>' +
                    '<td>' + (item.item_name || '--') + '</td>' +
                    '<td>' + (item.hsn || '') + '<input type="hidden" name="items[' + index + '][hsn]" value="' + (item.hsn || '') + '"></td>' +
                    '<td class="text-end">' + Number(item.ordered_quantity).toFixed(3) + '</td>' +
                    '<td class="text-end">' + Number(item.previously_received_quantity).toFixed(3) + '</td>' +
                    '<td class="text-end">' + Number(item.balance_quantity).toFixed(3) + '</td>' +
                    '<td><input type="number" step="0.001" min="0" max="' + item.balance_quantity + '" ' +
                    'name="items[' + index + '][received_quantity]" class="form-control text-end received-qty" ' +
                    'value="' + defaultQty.toFixed(3) + '" ' +
                    (disabled ? 'readonly' : '') + '></td>' +
                    '<td class="text-end rate-input">' + rate.toFixed(2) + '</td>' +
                    '<td class="text-end gross-value">' + gross.toFixed(2) + '</td>' +
                    '<td class="text-end tax-input">' + taxPct.toFixed(2) + '</td>' +
                    '<td class="text-end tax-value">' + taxVal.toFixed(2) + '</td>' +
                    '<td class="text-end net-value">' + net.toFixed(2) + '</td>' +
                    '<td class="item-flag-cell">' +
                        '<input type="hidden" name="items[' + index + '][is_capex]" value="0">' +
                        '<input type="checkbox" class="form-check-input item-is-capex" name="items[' + index + '][is_capex]" value="1" title="Checked items are created as assets. Unchecked items stay Opex and do not create assets.">' +
                    '</td>' +
                    '<td class="item-flag-cell">' +
                        '<input type="hidden" name="items[' + index + '][is_group_asset]" value="0">' +
                        '<input type="checkbox" class="form-check-input item-is-group" name="items[' + index + '][is_group_asset]" value="1" disabled title="Available when CAPEX is checked and received quantity is more than 1. Checked creates one asset for the full quantity.">' +
                    '</td>' +
                    '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger remove-grn-item"><i class="bx bx-trash"></i></button></td>' +
                    '</tr>';
                $body.append(row);
            });

            if (poData && parseFloat(poData.miscellaneous_charges) > 0 && !poData.has_previous_misc_charges) {
                var miscCharges = parseFloat(poData.miscellaneous_charges) || 0;
                var miscTax = parseFloat(poData.miscellaneous_tax) || 0;
                var miscNet = miscCharges + miscTax;

                var miscRow = '<tr class="grn-misc-charges-row bg-light">' +
                    '<td colspan="7" class="text-end"><strong>Miscellaneous Charges</strong></td>' +
                    '<td class="text-end"></td>' +
                    '<td class="text-end"><strong>' + miscCharges.toFixed(2) + '</strong></td>' +
                    '<td class="text-end"></td>' +
                    '<td class="text-end"><strong>' + miscTax.toFixed(2) + '</strong></td>' +
                    '<td class="text-end"><strong>' + miscNet.toFixed(2) + '</strong></td>' +
                    '<td></td>' +
                    '<td></td>' +
                    '<td></td>' +
                    '</tr>';
                $body.append(miscRow);
            }

            function syncItemFlags($row) {
                var qty = parseFloat($row.find('.received-qty').val()) || 0;
                var capex = $row.find('.item-is-capex').is(':checked');
                var $group = $row.find('.item-is-group');
                var enabled = qty > 1 && capex;
                $group.prop('disabled', !enabled);
                if (!enabled) {
                    $group.prop('checked', false);
                }
            }

            $('#grn-items-body .grn-item-row').each(function() {
                syncItemFlags($(this));
            });

            $('#grn-items-body').off('input', '.received-qty').on('input', '.received-qty', function() {
                var $row = $(this).closest('tr');
                var qty = parseFloat($(this).val()) || 0;
                var rate = parseFloat($row.find('.rate-input').text()) || 0;
                var taxPct = parseFloat($row.find('.tax-input').text()) || 0;

                var gross = qty * rate;
                var taxVal = gross * taxPct / 100;
                var net = gross + taxVal;

                $row.find('.gross-value').text(gross.toFixed(2));
                $row.find('.tax-value').text(taxVal.toFixed(2));
                $row.find('.net-value').text(net.toFixed(2));
                syncItemFlags($row);
            });

            $('#grn-items-body').off('change', '.item-is-capex').on('change', '.item-is-capex', function() {
                syncItemFlags($(this).closest('tr'));
            });

            $('#grn-items-body').off('click', '.remove-grn-item').on('click', '.remove-grn-item', function() {
                $(this).closest('tr').remove();
                if ($('.grn-item-row').length === 0) {
                    $('#grn-items-body').html('<tr id="grn-items-empty"><td colspan="15" class="text-center text-muted py-4">No items in this GRN.</td></tr>');
                }
            });
        }

        function loadPoItems() {
            var branchId = String($('#branch_id').val() || '');
            var poId = $poSelect.val();
            var $opt = $poSelect.find('option:selected');

            if (!branchId) {
                clearPoItems();
                refreshGrnNumber();
                return;
            }

            if (poId && String($opt.attr('data-branch-id') || '') !== branchId) {
                Swal.fire({ icon: 'error', title: 'Selected PO does not belong to the selected branch.' });
                $poSelect.val('');
                clearPoItems();
                refreshGrnNumber();
                return;
            }

            $('#vendor_name_display').val($opt.data('vendor') || '');
            if ($opt.data('department')) {
                $('#department_id').val(String($opt.data('department'))).trigger('change');
            }
            if ($opt.data('employee')) {
                $('#employee_master_id').val(String($opt.data('employee'))).trigger('change');
            }
            refreshGrnNumber();

            if (!poId) {
                clearPoItems();
                return;
            }

            $.get(poItemsUrl + '/' + poId, { branch_id: branchId }, function(res) {
                if (!res.success) {
                    Swal.fire({ icon: 'error', title: res.message || 'Unable to load PO items' });
                    return;
                }
                if (res.po) {
                    $('#vendor_name_display').val(res.po.vendor_name || '');
                    if (res.po.department_id) $('#department_id').val(String(res.po.department_id)).trigger('change');
                    if (res.po.employee_master_id) $('#employee_master_id').val(String(res.po.employee_master_id)).trigger('change');
                }
                renderItems(res.items || [], res.po || null);
            }).fail(function(xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to load PO items';
                Swal.fire({ icon: 'error', title: msg });
                clearPoItems();
            });
        }

        initSearchableSelect($('#branch_id, #department_id, #employee_master_id'));

        $('#branch_id').on('change', function() {
            filterPurchaseOrders(false);
            refreshGrnNumber();
        });

        $('#grn_date').on('change', refreshGrnNumber);

        filterPurchaseOrders(true);
        $poSelect.on('change', loadPoItems);
        if ($poSelect.val()) {
            loadPoItems();
        } else {
            refreshGrnNumber();
        }
    }

    boot();
})();
</script>
@endsection
