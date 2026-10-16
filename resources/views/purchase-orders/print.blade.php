<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Purchase Order - {{ $purchaseOrder->po_no }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 20px;
        }
        .header-box {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header h2 {
            margin: 0 0 10px 0;
            color: #111;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .meta-table td {
            border: none;
            padding: 4px 8px;
            vertical-align: top;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .items-table th, .items-table td {
            border: 1px solid #ccc;
            padding: 6px 8px;
            text-align: left;
            vertical-align: top;
            font-size: 11px;
        }
        .items-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            color: #111;
            text-align: center;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .summary-box {
            margin-top: 20px;
            float: right;
            width: 350px;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
        }
        .summary-table th, .summary-table td {
            padding: 5px 8px;
            border: 1px solid #eee;
            text-align: right;
        }
        .summary-table th {
            text-align: left;
            background-color: #f9f9f9;
        }
        .grand-total {
            font-size: 14px;
            font-weight: bold;
            background-color: #eef;
        }
        .clear {
            clear: both;
        }
        .terms-box {
            margin-top: 30px;
            border-top: 1px solid #ccc;
            padding-top: 10px;
        }
    </style>
</head>
<body>
    @php
        $vState = strtolower(trim($purchaseOrder->vendor->state ?? ''));
        $cState = strtolower(trim(\App\Models\Company::query()->value('state') ?? ''));
        $intraState = (! $vState || ! $cState || $vState === $cState);
        $totQty = 0; $totSub = 0; $totDisc = 0; $totTaxable = 0;
        $totCgst = 0; $totSgst = 0; $totIgst = 0; $totGrand = 0;
    @endphp
    <div class="header">
        <h2>PURCHASE ORDER: {{ $purchaseOrder->po_no }}</h2>
    </div>
    <table class="meta-table">
        <tr>
            <td style="width: 50%;">
                <strong>Supplier Details:</strong><br>
                <strong>{{ $purchaseOrder->vendor->name ?? '-' }}</strong><br>
                @if(!empty($purchaseOrder->vendor->phone) || !empty($purchaseOrder->vendor->email))
                    Contact: {{ $purchaseOrder->vendor->phone ?? '' }} {{ $purchaseOrder->vendor->email ?? '' }}<br>
                @endif
                State: <strong>{{ strtoupper($purchaseOrder->vendor->state ?? 'N/A') }}</strong> 
                <em>({{ $intraState ? 'Intra-State GST' : 'Inter-State IGST' }})</em>
            </td>
            <td style="width: 50%; text-align: right;">
                <strong>Order Date:</strong> {{ $purchaseOrder->purchase_date?->format('d/m/Y') ?? ($purchaseOrder->order_date ? \Carbon\Carbon::parse($purchaseOrder->order_date)->format('d/m/Y') : '-') }}<br>
                <strong>Estimated Delivery:</strong> {{ $purchaseOrder->delivery_date?->format('d/m/Y') ?? '-' }}<br>
                <strong>GRN Store:</strong> {{ $purchaseOrder->grn_store ?? ($purchaseOrder->store->department_name ?? '-') }}<br>
                <strong>Minimum Advance Payment:</strong> {{ number_format((float) ($purchaseOrder->minimum_advance_payment_percentage ?? 0), 2) }}%
            </td>
        </tr>
    </table>

    <table class="items-table">
        <thead>
            <tr>
                <th>S. No.</th>
                <th>Item Name</th>
                <th>Price</th>
                <th>Discount</th>
                <th>HSN Code</th>
                <th>UOM</th>
                <th>GST (%)</th>
                <th>Quantity</th>
                <th>Rate</th>
                <th>GST Amount</th>
                @if ($intraState)
                    <th>Central Tax (CGST)</th>
                    <th>State Tax (SGST)</th>
                @else
                    <th>Integrated Tax (IGST)</th>
                @endif
                <th>EPR</th>
                <th>MRP</th>
                <th>Total Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($purchaseOrder->items as $idx => $item)
                @php
                    $qty = (int) $item->quantity;
                    $price = (float) $item->price;
                    $disc = (float) ($item->discount_amount ?: $item->discount);
                    $baseAmt = (float) ($item->base_amount ?: ($qty * $price));
                    $taxable = (float) ($item->taxable_amount ?: max(0, $baseAmt - $disc));
                    $rateAfterDisc = $qty > 0 ? ($taxable / $qty) : max(0, $price - $disc);
                    $gstAmt = (float) ($item->gst_amount ?: (($taxable * ((float) ($item->gst_percent ?: 18))) / 100));
                    $cgstAmt = (float) ($item->cgst_amount ?: ($intraState ? ($gstAmt / 2) : 0));
                    $sgstAmt = (float) ($item->sgst_amount ?: ($intraState ? ($gstAmt - $cgstAmt) : 0));
                    $igstAmt = (float) ($item->igst_amount ?: (! $intraState ? $gstAmt : 0));
                    $lineTot = (float) ($item->total ?: ($taxable + $gstAmt));

                    $totQty += $qty;
                    $totSub += $baseAmt;
                    $totDisc += $disc;
                    $totTaxable += $taxable;
                    $totCgst += $cgstAmt;
                    $totSgst += $sgstAmt;
                    $totIgst += $igstAmt;
                    $totGrand += $lineTot;
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td><strong>{{ $item->item_name ?? '-' }}</strong>@if($item->item_code)<br><small style="color: #666;">Code: {{ $item->item_code }}</small>@endif</td>
                    <td class="text-right">₹{{ number_format($price, 2) }}</td>
                    <td class="text-right">₹{{ number_format($disc, 2) }}</td>
                    <td class="text-center">{{ $item->hsn_code ?: '-' }}</td>
                    <td class="text-center">{{ $item->purchase_unit ?: ($item->unit ?: 'Nos') }}</td>
                    <td class="text-center">{{ ((float) ($item->gst_percent ?: 18)) }}%</td>
                    <td class="text-right">{{ $qty }}</td>
                    <td class="text-right">₹{{ number_format($rateAfterDisc, 2) }}</td>
                    <td class="text-right">₹{{ number_format($gstAmt, 2) }}</td>
                    @if ($intraState)
                        <td class="text-right">₹{{ number_format($cgstAmt, 2) }}</td>
                        <td class="text-right">₹{{ number_format($sgstAmt, 2) }}</td>
                    @else
                        <td class="text-right">₹{{ number_format($igstAmt, 2) }}</td>
                    @endif
                    <td class="text-right">₹{{ number_format((float) ($item->epr ?? 0), 2) }}</td>
                    <td class="text-right">₹{{ number_format((float) ($item->mrp ?? 0), 2) }}</td>
                    <td class="text-right"><strong>₹{{ number_format($lineTot, 2) }}</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary-box">
        <table class="summary-table">
            <tr><th>Total Quantity:</th><td>{{ $totQty }}</td></tr>
            <tr><th>Sub-total:</th><td>₹{{ number_format($totSub, 2) }}</td></tr>
            <tr><th>Total Discount:</th><td style="color: green;">-₹{{ number_format($totDisc, 2) }}</td></tr>
            <tr><th>Taxable Amount:</th><td>₹{{ number_format($totTaxable, 2) }}</td></tr>
            @if ($intraState)
                <tr><th>Total CGST:</th><td>₹{{ number_format($totCgst, 2) }}</td></tr>
                <tr><th>Total SGST:</th><td>₹{{ number_format($totSgst, 2) }}</td></tr>
            @else
                <tr><th>Total IGST:</th><td>₹{{ number_format($totIgst, 2) }}</td></tr>
            @endif
            <tr class="grand-total"><th>Grand Total:</th><td>₹{{ number_format($purchaseOrder->grand_total ?: $totGrand, 2) }}</td></tr>
        </table>
    </div>
    <div class="clear"></div>

    @if(!empty($purchaseOrder->payment_terms))
        <div class="terms-box">
            <strong>PO Terms & Conditions:</strong>
            <p>{{ $purchaseOrder->payment_terms }}</p>
        </div>
    @endif
</body>
</html>
