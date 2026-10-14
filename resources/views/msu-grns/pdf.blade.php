<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    @if(!isset($isExcel) || !$isExcel)
    <title>Goods Receipt Note - {{ $grn->grn_number }}</title>
    @endif
    <style>
        @page { margin: 12mm 10mm; }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 10px;
            color: #333;
            margin: 0;
            line-height: 1.4;
        }
        table { width: 100%; border-collapse: collapse; }
        .title {
            text-align: center;
            font-size: 22px;
            font-weight: bold;
            letter-spacing: 0.5px;
            margin: -20px 0 10px;
            color: #000;
        }
        .header-meta td { vertical-align: top; }
        .section-label {
            font-weight: bold;
            font-size: 13px;
            color: #888;
            margin-bottom: 4px;
        }
        .company-name {
            font-weight: bold;
            font-size: 12px;
            color: #000;
            margin-bottom: 2px;
        }
        .party-name { 
            font-weight: bold;
            font-size: 11px;
            color: #333;
            margin-bottom: 2px;
        }
        .addr { margin-top: 2px; color: #444; line-height: 1.3;}
        .items {
            margin-top: 15px;
            width: 100%;
        }
        .items th {
            padding: 8px 4px;
            font-size: 10px;
            text-align: center;
            font-weight: bold;
            color: #444;
        }
        .items td {
            padding: 6px 4px;
            font-size: 10px;
            vertical-align: top;
            text-align: center;
            color: #333;
        }
        .items td:nth-child(2), .items th:nth-child(2) {
            text-align: left;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .summary-table {
            border: 1px solid #000;
            width: 100%;
        }
        .summary-table td {
            padding: 4px 6px;
            border-bottom: 1px solid #000;
        }
        .summary-table .col-label {
            border-right: 1px solid #000;
            color: #333;
        }
        .summary-table .col-val {
            text-align: right;
            font-weight: bold;
        }
        .footer-box {
            border: 1px solid #000;
            margin-top: 15px;
        }
        .footer-company {
            text-align: center;
            font-weight: bold;
            font-size: 11px;
            border-bottom: 1px solid #000;
            padding: 6px 4px;
            text-transform: uppercase;
        }
        .footer-inner td {
            vertical-align: bottom;
            padding: 6px;
        }
        .disclaimer {
            font-size: 8px;
            width: 60%;
            vertical-align: top !important;
        }
        .signatory {
            text-align: right;
            font-size: 9px;
            width: 40%;
            padding-bottom: 15px !important;
        }
        .remarks {
            border: 1px solid #000;
            padding: 4px 6px;
            min-height: 80px;
        }
    </style>
</head>
<body>
@php
    $vendor = $grn->vendor;
    $po = $grn->purchaseOrder;
    $branch = $po?->branch;
    $companyName = $branch && $branch->company ? $branch->company->name : 'Mysore Saree Udyog LLP';
    $branchTitle = $branch
        ? trim(($branch->name ?: strtoupper($companyName) . ' - BNGLR SHOWROOM'))
        : strtoupper($companyName) . ' - BNGLR SHOWROOM';
    $branchAddress = collect([
        $branch?->gst_number,
        $branch?->address,
        $branch?->city,
        $branch?->state,
        $branch?->country,
    ])->filter()->implode(', ');
    $vendorAddress = collect([
        $vendor?->address,
        $vendor?->city,
        $vendor?->state,
        $vendor?->country,
    ])->filter()->implode(', ');
    $shipping = $po?->shippingDetail;
    $shippingTitle = $shipping?->shipping_name ?: '--';
@endphp

@if(!isset($isExcel) || !$isExcel)


<!-- Title & Barcode Section -->
<div style="position: relative;">
    <div class="title" style="text-align: center; margin-bottom: 15px; margin-top: -20px; white-space: nowrap; text-transform: uppercase;">GOODS RECEIPT NOTE</div>
</div>

<!-- Invoice To & GRN/PO Info Section -->
<table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 10px;">
    <tr>
        <td style="width: 50%; vertical-align: top;">
            <div class="section-label">Invoice To</div>
            <div class="company-name">{{ $companyName }}</div>
            <div class="party-name">{{ strtoupper($branchTitle) }}</div>
            <div class="addr">
                @if($branch?->gst_number){{ $branch->gst_number }}<br>@endif
                {{ $branchAddress ?: '--' }}
            </div>
            <div class="addr">Contact Person : {{ $branch?->contact_person ?: '--' }}</div>
            <div class="addr">Contact No : {{ $branch?->contact_number ?: '--' }}</div>
            @if($branch?->email)
                <div class="addr">EmailID : {{ $branch->email }}</div>
            @endif
        </td>
        <td style="width: 50%; text-align: right; vertical-align: top; line-height: 1.4; font-weight: bold; color: #777;">
            <div>GRN : {{ $grn->grn_number }} / {{ $grn->grn_date?->format('d-m-Y') ?? '--' }}</div>
            <div>PO No: {{ $grn->purchaseOrder?->po_number ?? '--' }} / {{ $grn->purchaseOrder?->po_date?->format('d-m-Y') ?? '--' }}</div>
        </td>
    </tr>
</table>

<!-- Supplier and Shipping Address Section -->
<table style="width: 100%; border-collapse: collapse; margin-bottom: 25px; font-size: 10px;">
    <tr>
        <!-- Left: Supplier -->
        <td style="width: 50%; vertical-align: top; padding-right: 20px;">
            <div class="section-label">Supplier</div>
            <div class="company-name">
                {{ strtoupper($vendor?->name ?? '--') }}
                @if($vendor?->company_code)
                    ({{ $vendor->company_code }})
                @endif
            </div>
            <div class="addr">
                @if($vendor?->gst_number){{ $vendor->gst_number }}<br>@endif
                {{ $vendorAddress ?: '--' }}
            </div>
            <div class="addr">Contact Person : {{ $vendor?->contact_person ?: '--' }}</div>
            <div class="addr">Contact No : {{ $vendor?->phone_number ?: '--' }}</div>
            @if($vendor?->email)
                <div class="addr">EmailID : {{ $vendor->email }}</div>
            @endif
        </td>
        
        <!-- Right: Shipping Address -->
        <td style="width: 50%; vertical-align: top; padding-left: 20px;">
            <div class="section-label">Shipping Address</div>
            <div class="company-name">{{ strtoupper($shippingTitle) }}</div>
            <div class="addr">
                @if($shipping?->gst_number){{ $shipping->gst_number }}<br>@endif
                {{ $shipping?->address ?: '--' }}
            </div>
            <div class="addr">Contact Person : {{ $shipping?->contact_person ?: '--' }}</div>
            <div class="addr">Contact No : {{ $shipping?->contact_no ?: '--' }}</div>
            @if($shipping?->email)
                <div class="addr">EmailID : {{ $shipping->email }}</div>
            @endif
        </td>
    </tr>
</table>

<table style="width: 100%; border-collapse: collapse; margin-bottom: 35px; font-size: 10px; font-weight: bold; color: #333;">
    <tr>
        <td style="width: 50%; vertical-align: top;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 110px; padding-bottom: 4px;">Vendor Invoice No</td>
                    <td style="width: 10px; padding-bottom: 4px;">:</td>
                    <td style="font-weight: normal; padding-bottom: 4px;">{{ $grn->vendor_invoice_number ?: '--' }}</td>
                </tr>
                <tr>
                    <td>Vendor Invoice Date</td>
                    <td>:</td>
                    <td style="font-weight: normal;">{{ $grn->vendor_invoice_date?->format('d-m-Y') ?? '--' }}</td>
                </tr>
            </table>
        </td>
        <td style="width: 50%; vertical-align: top; padding-left: 20px;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 70px; padding-bottom: 4px;">Department</td>
                    <td style="width: 10px; padding-bottom: 4px;">:</td>
                    <td style="font-weight: normal; padding-bottom: 4px;">{{ $grn->department?->department_name ?? '--' }}</td>
                </tr>
                <tr>
                    <td>Employee</td>
                    <td>:</td>
                    <td style="font-weight: normal;">{{ $grn->employeeMaster?->employee_name ?? '--' }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>
@endif

<table class="items">
    <thead>
        <tr>
            <th style="width:28px">SN</th>
            <th style="text-align: left;">Item Name</th>
            <th style="width:50px">HSN</th>
            <th class="text-right">Qty</th>
            <th class="text-right">Rate</th>
            <th class="text-right">Gross Value</th>
            <th class="text-right">GST(%)</th>
            <th class="text-right">GST Value</th>
            <th class="text-right">Net Value</th>
        </tr>
    </thead>
    <tbody>
        @php
            $grandGross = 0;
            $grandTax = 0;
            $grandNet = 0;
        @endphp
        @foreach($grn->items as $item)
            @php
                $received = (float) $item->received_quantity;
                $poItem = $item->purchaseOrderItem;
                $rate = $poItem ? (float) $poItem->price : 0;
                $taxPct = $poItem ? (float) $poItem->tax : 0;
                $master = $item->relationLoaded('itemMaster') ? $item->itemMaster : null;
                $unit = 'NOS';
                if ($master) {
                    if ($master->relationLoaded('unit') && $master->unit) {
                        $unit = $master->unit->unit_name ?: ($master->uom ?: 'NOS');
                    } else {
                        $unit = $master->uom ?: 'NOS';
                    }
                }
                $gross = round($received * $rate, 2);
                $taxVal = round($gross * $taxPct / 100, 2);
                $net = round($gross + $taxVal, 2);

                $grandGross += $gross;
                $grandTax += $taxVal;
                $grandNet += $net;
            @endphp
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td class="text-left">
                    {{ $item->item_code }} - {{ $item->item_name }}
                </td>
                <td class="text-center">{{ $item->hsn ?: '' }}</td>
                <td class="text-right">{{ number_format($received, 3) }}<br><span style="font-size: 7px;">({{ strtoupper($unit) }})</span></td>
                <td class="text-right">{{ number_format($rate, 2) }}</td>
                <td class="text-right">{{ number_format($gross, 2) }}</td>
                <td class="text-right">{{ number_format($taxPct, 2) }}</td>
                <td class="text-right">{{ number_format($taxVal, 2) }}</td>
                <td class="text-right">{{ number_format($net, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

@if(!isset($isExcel) || !$isExcel)
<div style="page-break-inside: avoid;">
<table class="bottom-wrap">
    <tr>
        <td style="width:60%; padding-right: 10px;">
            <div class="section-label" style="font-size: 11px;">Other Terms & Conditions</div>
            <div style="margin-top: 4px;">
                <strong>Payment Terms And Condition:</strong><br>
                {{ $po?->paymentTerm?->term_name ?? 'N/A' }}
            </div>
            
            <div class="remarks" style="margin-top: 15px;">
                <div class="remarks-title">Remarks</div>
                <div>{{ $grn->remarks ?: '--' }}</div>
            </div>
        </td>
        <td style="width:40%;">
            @php
                $miscCharges = (float) ($grn->miscellaneous_charges ?? 0);
                $miscTax = (float) ($grn->miscellaneous_tax ?? 0);
                
                $totalTaxAmount = $grandTax + $miscTax;
                
                $sgst = $totalTaxAmount / 2;
                $cgst = $totalTaxAmount / 2;
                
                $finalTotal = $grandGross + $miscCharges + $totalTaxAmount;
            @endphp
            <table style="border: 1px solid #000;">
                <tr>
                    <td style="padding: 4px; border-bottom: 1px solid #ccc; font-weight: bold; width: 60%">Total Value (Items)</td>
                    <td style="padding: 4px; border-bottom: 1px solid #ccc; text-align: right; width: 40%">{{ number_format($grandGross, 2) }}</td>
                </tr>
                @if($miscCharges > 0 && $grn->purchaseOrder && $grn->purchaseOrder->charges->count() > 0)
                    @foreach($grn->purchaseOrder->charges as $charge)
                    <tr>
                        <td style="padding: 4px; border-bottom: 1px solid #ccc; font-weight: bold;">{{ $charge->charge_name }}</td>
                        <td style="padding: 4px; border-bottom: 1px solid #ccc; text-align: right;">{{ number_format((float) $charge->amount, 2) }}</td>
                    </tr>
                    @endforeach
                @elseif($miscCharges > 0 || $miscTax > 0)
                <tr>
                    <td style="padding: 4px; border-bottom: 1px solid #ccc; font-weight: bold;">Miscellaneous Charges</td>
                    <td style="padding: 4px; border-bottom: 1px solid #ccc; text-align: right;">{{ number_format($miscCharges, 2) }}</td>
                </tr>
                @endif
                <tr>
                    <td style="padding: 4px; border-bottom: 1px solid #ccc; font-weight: bold;">Total Miscellaneous Values</td>
                    <td style="padding: 4px; border-bottom: 1px solid #ccc; text-align: right;">{{ number_format($miscCharges, 2) }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px; border-bottom: 1px solid #ccc; font-weight: bold;">SGST</td>
                    <td style="padding: 4px; border-bottom: 1px solid #ccc; text-align: right;">{{ number_format($sgst, 2) }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px; border-bottom: 1px solid #ccc; font-weight: bold;">CGST</td>
                    <td style="padding: 4px; border-bottom: 1px solid #ccc; text-align: right;">{{ number_format($cgst, 2) }}</td>
                </tr>

                <tr>
                    <td style="padding: 4px; border-bottom: 1px solid #ccc; font-weight: bold; font-size: 11px;">Grand Total</td>
                    <td style="padding: 4px; border-bottom: 1px solid #ccc; text-align: right; font-weight: bold; font-size: 11px;">{{ number_format($finalTotal, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="2" style="padding: 4px;">
                        <span style="font-weight: bold;">In Words:</span><br>
                        {{ \App\Support\AmountInWords::indianRupees((float)$finalTotal) }}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<div class="footer-box">
    <div class="footer-company">{{ strtoupper($branchTitle) }}</div>
    <table>
        <tr class="footer-inner">
            <td class="disclaimer">
                This is computer generated Goods Receipt Note, Hence Signature is not required.
            </td>
            <td class="signatory">
                Authorized Signatory
            </td>
        </tr>
    </table>
</div>
</div>
@endif
</body>
</html>
