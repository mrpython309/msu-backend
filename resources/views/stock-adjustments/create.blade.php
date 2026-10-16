@extends('layouts/contentNavbarLayout')

@section('title', 'Create Stock Adjustment')

@section('content')
<!-- Select2 CSS -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    .select2-container { width: 100% !important; }
</style>
    <h4 class="fw-bold mb-3">
        <span class="text-muted fw-light">Inventory / Stock Adjustments /</span> New Adjustment
    </h4>

    <div class="card mb-4">
        <div class="card-body">
            <form action="{{ route('stock-adjustments.store') }}" method="POST">
                @csrf
                
                <div class="row mb-3">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="adjustment_date">Adjustment Date <span class="text-danger">*</span></label>
                        <input type="date" class="form-control @error('adjustment_date') is-invalid @enderror" id="adjustment_date" name="adjustment_date" value="{{ old('adjustment_date', date('Y-m-d')) }}" required>
                        @error('adjustment_date') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="item-autocomplete">Item <span class="text-danger">*</span></label>
                        @php
                            $oldItemId = old('msu_item_master_id');
                            $oldItemLabel = '';
                            if ($oldItemId) {
                                $found = collect($items)->firstWhere('id', $oldItemId);
                                if ($found) {
                                    $oldItemLabel = ($found->item_code ? $found->item_code . ' - ' : '') . $found->item_name;
                                }
                            }
                        @endphp
                        <input type="text" class="form-control @error('msu_item_master_id') is-invalid @enderror item-autocomplete" id="item-autocomplete" list="item-options" placeholder="Type to search item..." value="{{ $oldItemLabel }}" autocomplete="off" required>
                        <input type="hidden" name="msu_item_master_id" id="msu_item_master_id" value="{{ $oldItemId }}">
                        <datalist id="item-options">
                            @foreach($items as $item)
                                @php
                                    $label = ($item->item_code ? $item->item_code . ' - ' : '') . $item->item_name;
                                @endphp
                                <option value="{{ $label }}" data-id="{{ $item->id }}" data-stock="{{ $item->current_stock }}"></option>
                            @endforeach
                        </datalist>
                        <div class="text-danger small mt-1 d-none item-error"></div>
                        <small class="text-muted d-none" id="item-avail-stock"></small>
                        @error('msu_item_master_id') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="adjustment_type">Adjustment Type <span class="text-danger">*</span></label>
                        <select class="form-select @error('adjustment_type') is-invalid @enderror" id="adjustment_type" name="adjustment_type" required>
                            <option value="increment" {{ old('adjustment_type') == 'increment' ? 'selected' : '' }}>Increment (Add Stock)</option>
                            <option value="decrement" {{ old('adjustment_type') == 'decrement' ? 'selected' : '' }}>Decrement (Remove Stock)</option>
                        </select>
                        @error('adjustment_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="quantity">Quantity <span class="text-danger">*</span></label>
                        <input type="number" step="0.001" min="0.001" class="form-control @error('quantity') is-invalid @enderror" id="quantity" name="quantity" value="{{ old('quantity') }}" required>
                        @error('quantity') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        <small class="text-muted d-none" id="qty-warning">Quantity cannot exceed available stock.</small>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="unit_display">Unit</label>
                        <input type="text" class="form-control" id="unit_display" value="" readonly tabindex="-1">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="price_display">Price</label>
                        <input type="text" class="form-control" id="price_display" value="0.00" readonly tabindex="-1">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="total_display">Total</label>
                        <input type="text" class="form-control" id="total_display" value="0.00" readonly tabindex="-1">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="gst_percentage_display">GST Percentage</label>
                        <input type="text" class="form-control" id="gst_percentage_display" value="0.00" readonly tabindex="-1">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="gst_amount_display">GST Amount</label>
                        <input type="text" class="form-control" id="gst_amount_display" value="0.00" readonly tabindex="-1">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label" for="grand_total_display">Grand Total</label>
                        <input type="text" class="form-control fw-bold" id="grand_total_display" value="0.00" readonly tabindex="-1">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="employee_master_id">Employee</label>
                        <select class="form-select select2 @error('employee_master_id') is-invalid @enderror" id="employee_master_id" name="employee_master_id">
                            <option value="">Select Employee</option>
                            @foreach($employees as $employee)
                                <option value="{{ $employee->id }}" {{ old('employee_master_id') == $employee->id ? 'selected' : '' }}>
                                    {{ $employee->employee_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('employee_master_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label" for="department_id">Department</label>
                        <select class="form-select select2 @error('department_id') is-invalid @enderror" id="department_id" name="department_id">
                            <option value="">Select Department</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" {{ old('department_id') == $department->id ? 'selected' : '' }}>
                                    {{ $department->department_name }}
                                </option>
                            @endforeach
                        </select>
                        @error('department_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="remarks">Remarks</label>
                    <textarea class="form-control @error('remarks') is-invalid @enderror" id="remarks" name="remarks" rows="3">{{ old('remarks') }}</textarea>
                    @error('remarks') <div class="invalid-feedback">{{ $message }}</div> @enderror
                </div>

                <button type="submit" class="btn btn-primary me-2">Save Adjustment</button>
                <a href="{{ route('stock-adjustments.index') }}" class="btn btn-outline-secondary">Cancel</a>
            </form>
        </div>
    </div>
@endsection

@section('page-script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        var currentStock = 0;
        var itemPricing = @json($itemPricing ?? []);

        if (typeof $.fn.select2 === 'function') {
            $('#employee_master_id, #department_id').select2({
                placeholder: 'Select',
                allowClear: true,
                width: '100%'
            });
        }

        $('.item-autocomplete').on('input', function() {
            var val = $(this).val();
            var $hidden = $('#msu_item_master_id');
            var $errorMsg = $('.item-error');
            var $availStock = $('#item-avail-stock');

            if (!val) {
                $hidden.val('');
                $errorMsg.addClass('d-none');
                $(this).removeClass('is-invalid');
                $availStock.addClass('d-none');
                currentStock = 0;
                validateQuantity();
                recalculateAmounts();
                return;
            }

            var safeVal = val.replace(/"/g, '\\"');
            var match = $('#item-options option[value="' + safeVal + '"]');
            
            if (match.length) {
                var id = match.data('id');
                var stock = parseFloat(match.data('stock')) || 0;
                
                $(this).removeClass('is-invalid');
                $errorMsg.addClass('d-none');
                $hidden.val(id);
                currentStock = stock;
                
                $availStock.removeClass('d-none').text('Available Stock: ' + currentStock.toFixed(3));
                validateQuantity();
                recalculateAmounts();
            } else {
                $hidden.val('');
                currentStock = 0;
                $availStock.addClass('d-none');
                recalculateAmounts();
            }
        });

        $('#adjustment_type, #quantity').on('change input', function() {
            validateQuantity();
            recalculateAmounts();
        });

        $('#adjustment_date').on('change', function() {
            recalculateAmounts();
        });

        function pricingFor(itemId, dateStr) {
            var meta = itemPricing[itemId] || itemPricing[String(itemId)];
            if (!meta) {
                return { unit: '', price: 0, gst: 0 };
            }

            var chosen = null;
            (meta.rates || []).forEach(function(rate) {
                if (!rate.effective_from || rate.effective_from <= dateStr) {
                    if (!chosen || rate.effective_from > chosen.effective_from || (rate.effective_from === chosen.effective_from && rate.id > chosen.id)) {
                        chosen = rate;
                    }
                }
            });

            var price = chosen ? parseFloat(chosen.rate) || 0 : 0;
            var gst = (chosen && chosen.tax !== null && chosen.tax !== undefined)
                ? parseFloat(chosen.tax) || 0
                : (meta.hsn_tax !== null && meta.hsn_tax !== undefined ? parseFloat(meta.hsn_tax) || 0 : 0);

            return { unit: meta.unit || '', price: price, gst: gst };
        }

        function money(value) {
            return (Math.round((parseFloat(value) || 0) * 100) / 100).toFixed(2);
        }

        function recalculateAmounts() {
            var itemId = $('#msu_item_master_id').val();
            var dateStr = $('#adjustment_date').val() || '';
            var qty = parseFloat($('#quantity').val()) || 0;
            var pricing = pricingFor(itemId, dateStr);
            var total = qty * pricing.price;
            var gstAmount = total * pricing.gst / 100;
            var grandTotal = total + gstAmount;

            $('#unit_display').val(itemId ? pricing.unit : '');
            $('#price_display').val(money(pricing.price));
            $('#total_display').val(money(total));
            $('#gst_percentage_display').val(money(pricing.gst));
            $('#gst_amount_display').val(money(gstAmount));
            $('#grand_total_display').val(money(grandTotal));
        }

        function validateQuantity() {
            var type = $('#adjustment_type').val();
            var qty = parseFloat($('#quantity').val()) || 0;
            var stock = currentStock;
            
            var $qtyInput = $('#quantity');
            var $warning = $('#qty-warning');
            
            if (type === 'decrement' && $('#msu_item_master_id').val() !== '') {
                $qtyInput.attr('max', stock);
                if (qty > stock) {
                    $qtyInput.addClass('is-invalid');
                    $warning.removeClass('d-none').addClass('text-danger');
                } else {
                    $qtyInput.removeClass('is-invalid');
                    $warning.addClass('d-none');
                }
            } else {
                $qtyInput.removeAttr('max');
                $qtyInput.removeClass('is-invalid');
                $warning.addClass('d-none');
            }
        }

        // Initialize state if old input exists
        if ($('.item-autocomplete').val()) {
            $('.item-autocomplete').trigger('input');
        } else {
            recalculateAmounts();
        }
    });
</script>
@endsection
