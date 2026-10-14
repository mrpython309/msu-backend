@extends('layouts/contentNavbarLayout')

@section('title', 'Create Work Invoice')

@section('page-style')
<style>
#wi-items-table {
    width: max-content;
    min-width: 100%;
}
#wi-items-table thead th {
    vertical-align: middle;
    white-space: nowrap;
}
#wi-items-table .received-qty {
    min-width: 110px;
    width: 110px;
}
#wi-items-table th.billed-rate-col,
#wi-items-table td.billed-rate-col {
    min-width: 160px;
    width: 160px;
    white-space: nowrap;
}
#wi-items-table .rate-input {
    width: 150px;
    min-width: 150px;
    max-width: 150px;
    text-align: right;
}
#wi-items-table input[type=number]::-webkit-outer-spin-button,
#wi-items-table input[type=number]::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
    display: none;
}
#wi-items-table input[type=number] {
    -moz-appearance: textfield;
    appearance: textfield;
}
</style>
@endsection

@section('content')
<h4 class="mb-3">
    <span class="text-muted fw-light"><a href="{{ route('msu-work-orders.index') }}">Work Orders</a> /</span> Create Invoice
</h4>

@if ($errors->any())
<div class="alert alert-danger">
    <ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
</div>
@endif

<form method="POST" action="{{ route('msu-work-invoices.store') }}" id="msu-work-invoice-form" enctype="multipart/form-data">
    @csrf
    <input type="hidden" name="msu_work_order_id" value="{{ $workOrder->id }}">
    <input type="hidden" name="vendor_id" value="{{ $workOrder->vendor_id }}">
    <input type="hidden" name="branch_id" value="{{ $workOrder->branch_id }}">

    <div class="card mb-4">
        <div class="card-header"><h5 class="mb-0">Invoice Details</h5></div>
        <div class="card-body">
            <div class="row row-cols-1 row-cols-md-4 g-3">
                <div class="col">
                    <label class="form-label">WI Number</label>
                    <input type="text" class="form-control" value="{{ $previewWiNumber ?? '' }}" disabled>
                </div>
                
                <div class="col">
                    <label class="form-label">Work Order</label>
                    <input type="text" class="form-control" value="{{ $workOrder->wo_number }}" disabled>
                </div>
                <div class="col">
                    <label class="form-label">Vendor</label>
                    <input type="text" class="form-control" value="{{ $workOrder->vendor?->name }}" disabled>
                </div>
                <div class="col">
                    <label class="form-label">Vendor Invoice No</label>
                    <input type="text" name="invoice_number" class="form-control" value="{{ old('invoice_number') }}">
                </div>
                <div class="col">
                    <label class="form-label">Invoice Date<span class="text-danger">*</span></label>
                    <input type="date" name="invoice_date" id="invoice_date" class="form-control" required
                           value="{{ old('invoice_date', now()->format('Y-m-d')) }}">
                </div>
                <div class="col">
                    <label class="form-label">Document Upload</label>
                    <input type="file" name="document_path" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-12">
                    <label class="form-label">Notes / Remarks</label>
                    <textarea name="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h5 class="mb-0">Work Details</h5>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle" id="wi-items-table">
                    <thead>
                        <tr>
                            <th style="min-width:200px">Work Description</th>
                            <th style="min-width:100px">HSN/SAC</th>
                            <th class="text-end text-nowrap" style="min-width:110px">WO Qty</th>
                            <th class="text-end text-nowrap" style="min-width:150px">Previously Billed</th>
                            <th class="text-end text-nowrap" style="min-width:120px">Prev. Value</th>
                            <th class="text-end text-nowrap" style="min-width:100px">Balance</th>
                            <th class="text-end text-nowrap" style="min-width:150px">Billed Qty<span class="text-danger">*</span></th>
                            <th class="text-end text-nowrap billed-rate-col">Billed Rate<span class="text-danger">*</span></th>
                            <th class="text-end text-nowrap" style="min-width:110px">Gross Value</th>
                            <th class="text-end text-nowrap" style="min-width:80px">Tax %</th>
                            <th class="text-end text-nowrap" style="min-width:110px">Tax Value</th>
                            <th class="text-end text-nowrap" style="min-width:110px">Net Value</th>
                            <th width="60"></th>
                        </tr>
                    </thead>
                    <tbody id="wi-items-body">
                        @php $openLineCount = 0; @endphp
                        @foreach($workOrder->items as $idx => $item)
                            @php
                                $ordered = (float) $item->quantity;
                                $received = $item->billedQuantity();
                                $balance = $item->remainingQuantity();
                                $rate = (float) $item->price;
                                $taxPct = (float) $item->tax;
                                $previouslyBilledValue = $item->billedGrossAmount();
                                $remainingAmount = $item->remainingAmount();
                                $hasQuantityBalance = $balance > 0.0000001;
                                $hasPriceBalance = $remainingAmount > 0.009;
                            @endphp
                            @if(! $hasQuantityBalance && ! $hasPriceBalance)
                                @continue
                            @endif
                            @php
                                $openLineCount++;
                                $priceOnly = ! $hasQuantityBalance;
                                $maxQty = $priceOnly ? 0 : $balance;
                                $maxRate = $rate;
                                if ($priceOnly && $ordered > 0) {
                                    $maxRate = min($rate, round($remainingAmount / $ordered, 2));
                                }
                                $suggestedQty = $priceOnly ? 0 : $balance;
                                $suggestedRate = $priceOnly ? $maxRate : $rate;
                                $defaultQty = $priceOnly ? 0 : (float) old("items.$idx.quantity", $suggestedQty);
                                $defaultQty = min($maxQty, max(0, $defaultQty));
                                $defaultRate = min($maxRate, max(0, (float) old("items.$idx.price", $suggestedRate)));
                                $billingQty = $priceOnly ? $ordered : $defaultQty;
                                $gross = min($remainingAmount, $billingQty * $defaultRate);
                                $taxVal = $gross * ($taxPct / 100);
                                $net = $gross + $taxVal;
                            @endphp
                            <tr class="wi-item-row"
                                data-price-only="{{ $priceOnly ? '1' : '0' }}"
                                data-max-qty="{{ number_format($maxQty, 3, '.', '') }}"
                                data-max-rate="{{ number_format($maxRate, 2, '.', '') }}"
                                data-billing-qty="{{ number_format($ordered, 3, '.', '') }}"
                                data-remaining-amount="{{ number_format($remainingAmount, 2, '.', '') }}">
                                <td>
                                    {{ $item->work_description }}
                                    <input type="hidden" name="items[{{ $idx }}][msu_work_order_item_id]" value="{{ $item->id }}">
                                </td>
                                <td>{{ $item->hsn ?: '--' }}</td>
                                <td class="text-end">{{ number_format($ordered, 3) }}</td>
                                <td class="text-end">{{ number_format($received, 3) }}</td>
                                <td class="text-end">{{ number_format($previouslyBilledValue, 2) }}</td>
                                <td class="text-end">{{ number_format($balance, 3) }}</td>
                                <td>
                                    <input type="number" step="0.001" min="0" max="{{ number_format($maxQty, 3, '.', '') }}"
                                           name="items[{{ $idx }}][quantity]" class="form-control text-end received-qty {{ $priceOnly ? 'bg-light' : '' }}"
                                           value="{{ number_format($defaultQty, 3, '.', '') }}"
                                           @if($priceOnly) readonly tabindex="-1" title="Quantity is already fully billed and cannot be entered again." @endif>
                                </td>
                                <td class="billed-rate-col">
                                    <input type="text" inputmode="decimal" autocomplete="off"
                                           name="items[{{ $idx }}][price]" class="form-control rate-input"
                                           value="{{ number_format($defaultRate, 2, '.', '') }}"
                                           title="Maximum rate: {{ number_format($maxRate, 2, '.', '') }}">
                                </td>
                                <td class="text-end gross-value">{{ number_format($gross, 2, '.', '') }}</td>
                                <td class="text-end tax-input">{{ number_format($taxPct, 2, '.', '') }}</td>
                                <td class="text-end tax-value">{{ number_format($taxVal, 2, '.', '') }}</td>
                                <td class="text-end net-value">{{ number_format($net, 2, '.', '') }}</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger remove-wi-item"><i class="bx bx-trash"></i></button>
                                </td>
                            </tr>
                        @endforeach
                        @if($openLineCount === 0)
                            <tr>
                                <td colspan="13" class="text-center text-muted py-4">No quantity or price balance left to invoice.</td>
                            </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mb-4">
        <button type="submit" class="btn btn-primary">Submit Invoice</button>
        <a href="{{ route('msu-work-orders.show', $workOrder) }}" class="btn btn-secondary">Cancel</a>
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
        initWIForm(window.jQuery);
    }

    function initWIForm($) {
        // Event delegation for quantity change
        function lineAmounts($row) {
            var priceOnly = String($row.attr('data-price-only')) === '1';
            var maxQty = parseFloat($row.attr('data-max-qty')) || 0;
            var woMaxRate = parseFloat($row.attr('data-max-rate')) || 0;
            var orderedQty = parseFloat($row.attr('data-billing-qty')) || 0;
            var remainingAmount = parseFloat($row.attr('data-remaining-amount')) || 0;
            var $qty = $row.find('.received-qty');
            var $rate = $row.find('.rate-input');
            var qty = priceOnly ? 0 : (parseFloat($qty.val()) || 0);
            var rate = parseFloat($rate.val()) || 0;
            var billingQty = priceOnly ? orderedQty : qty;
            var maxRate = woMaxRate;

            if (priceOnly) {
                qty = 0;
                $qty.val('0.000');
            } else if (qty > maxQty) {
                qty = maxQty;
                billingQty = qty;
                $qty.val(qty.toFixed(3));
            }

            if (billingQty > 0) {
                maxRate = Math.min(woMaxRate, remainingAmount / billingQty);
            }
            $rate.attr('max', maxRate.toFixed(2));

            if (rate > maxRate + 0.0001) {
                rate = maxRate;
                $rate.val(rate.toFixed(2));
            }

            var gross = Math.min(remainingAmount, billingQty * rate);
            if (gross < 0 || !isFinite(gross)) gross = 0;
            var taxPct = parseFloat($row.find('.tax-input').text()) || 0;
            var taxVal = gross * (taxPct / 100);

            return { gross: gross, taxVal: taxVal, net: gross + taxVal };
        }

        function paintRow($row) {
            var amounts = lineAmounts($row);
            $row.find('.gross-value').text(amounts.gross.toFixed(2));
            $row.find('.tax-value').text(amounts.taxVal.toFixed(2));
            $row.find('.net-value').text(amounts.net.toFixed(2));
        }

        $('#wi-items-body').on('input', '.received-qty, .rate-input', function() {
            paintRow($(this).closest('tr'));
        });

        $('#msu-work-invoice-form').on('submit', function() {
            $('#wi-items-body .wi-item-row').each(function() {
                paintRow($(this));
            });
        });

        // Event delegation for removing an item row
        $('#wi-items-body').on('click', '.remove-wi-item', function() {
            var $row = $(this).closest('tr');
            $row.remove();
            
            // Check if there are no items left
            if ($('.wi-item-row').length === 0) {
                $('#wi-items-body').html('<tr id="wi-items-empty"><td colspan="13" class="text-center text-muted py-4">No items in this Invoice.</td></tr>');
            }
        });
    }

    boot();
})();
</script>
@endsection
