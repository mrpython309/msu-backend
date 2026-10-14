<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Work Order Invoice - {{ $msuWorkInvoice->wi_number }}</title>
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
            border-bottom: 1px solid #ccc;
        }
        .items td {
            padding: 6px 4px;
            font-size: 10px;
            vertical-align: top;
            text-align: center;
            color: #333;
            border-bottom: 1px solid #eee;
        }
        .items td:nth-child(2), .items th:nth-child(2) {
            text-align: left;
        }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .bottom-wrap td { vertical-align: top; }
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
        .disclaimer { font-size: 9px; color: #666; width: 65%; }
        .signatory { text-align: right; font-weight: bold; width: 35%; padding-bottom: 15px !important; }
    </style>
</head>
<body>
@php
    $vendor = $msuWorkInvoice->vendor;
    $wo = $msuWorkInvoice->workOrder;
    $branch = $msuWorkInvoice->branch;
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
    
    $shipping = $wo?->shippingDetail;
    $shippingTitle = $shipping?->shipping_name ?: '--';
@endphp

<!-- Title Section -->
<div style="position: relative;">
    <div class="title" style="text-align: center; margin-bottom: 15px; margin-top: -20px; white-space: nowrap; text-transform: uppercase;">WORK ORDER INVOICE</div>
</div>

<!-- Invoice To & WI/WO Info Section -->
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
            <div>WI No: {{ $msuWorkInvoice->wi_number }}</div>
            <div>WO No: {{ $wo?->wo_number ?? '--' }} / {{ $wo?->wo_date?->format('d-m-Y') ?? '--' }}</div>
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

<table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 10px;">
    <tr>
        <td style="width: 50%; vertical-align: top;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 120px; padding-bottom: 4px; font-weight: bold;">Vendor Invoice No</td>
                    <td style="width: 10px; padding-bottom: 4px;">:</td>
                    <td style="font-weight: normal; padding-bottom: 4px;">{{ $msuWorkInvoice->invoice_number ?: '--' }}</td>
                </tr>
                <tr>
                    <td style="width: 120px; padding-bottom: 4px; font-weight: bold;">Vendor Invoice Date</td>
                    <td style="width: 10px; padding-bottom: 4px;">:</td>
                    <td style="font-weight: normal; padding-bottom: 4px;">{{ $msuWorkInvoice->invoice_date?->format('d-m-Y') ?? '--' }}</td>
                </tr>
                <tr>
                    <td style="width: 120px; padding-bottom: 4px; font-weight: bold;">Contract Period</td>
                    <td style="width: 10px; padding-bottom: 4px;">:</td>
                    <td style="font-weight: normal; padding-bottom: 4px;">
                        {{ $wo?->contract_period_from ? $wo->contract_period_from->format('d-m-Y') : '--' }} to {{ $wo?->contract_period_to ? $wo->contract_period_to->format('d-m-Y') : '--' }}
                    </td>
                </tr>
            </table>
        </td>
        <td style="width: 50%; vertical-align: top; padding-left: 20px;">
            <table style="width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="width: 70px; padding-bottom: 4px; font-weight: bold;">Employee</td>
                    <td style="width: 10px; padding-bottom: 4px;">:</td>
                    <td style="font-weight: normal; padding-bottom: 4px;">{{ $wo?->employeeMaster?->employee_name ?? '--' }}</td>
                </tr>
            </table>
        </td>
    </tr>
</table>

@if($wo?->subject)
<div style="border: 1px solid #333; padding: 6px 10px; margin-bottom: 15px; font-weight: bold; font-size: 11px;">
    Subject: <span style="font-weight: normal; margin-left: 10px;">{{ $wo->subject }}</span>
</div>
@endif

<table class="items">
    <thead>
        <tr>
            <th style="width:28px">SN</th>
            <th style="text-align: left;">Scope of Work</th>
            <th style="text-align: left;">Description</th>
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
        @foreach($msuWorkInvoice->items as $item)
            @php
                $qty = (float) $item->quantity;
                $rate = (float) $item->price;
                $taxPct = (float) $item->tax;
                $gross = $item->grossValue();
                $taxVal = round($gross * $taxPct / 100, 2);
                $net = round($gross + $taxVal, 2);

                $woItem = $item->workOrderItem;
                $scope = $woItem ? $woItem->scope_of_work : '--';

                $grandGross += $gross;
                $grandTax += $taxVal;
                $grandNet += $net;
            @endphp
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td class="text-left">{{ $scope }}</td>
                <td class="text-left" style="color: #666;">{{ $item->work_description }}</td>
                <td class="text-center">{{ $woItem?->hsn ?: '--' }}</td>
                <td class="text-right">{{ number_format($qty, 3) }}</td>
                <td class="text-right">{{ number_format($rate, 2) }}</td>
                <td class="text-right">{{ number_format($gross, 2) }}</td>
                <td class="text-right">{{ number_format($taxPct, 2) }}</td>
                <td class="text-right">{{ number_format($taxVal, 2) }}</td>
                <td class="text-right">{{ number_format($net, 2) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<div style="page-break-inside: avoid;">
<table class="bottom-wrap" style="width: 100%; margin-top: 20px;">
    <tr>
        <td style="width:60%; padding-right: 10px; vertical-align: top;">
            <div class="section-label" style="font-size: 11px;">Other Terms & Conditions</div>
            <div style="margin-top: 4px;">
                <strong>Payment Terms And Condition:</strong><br>
                {{ $wo?->paymentTerm?->term_name ?? 'N/A' }}
            </div>
            
            <div class="remarks" style="margin-top: 15px;">
                <div class="remarks-title" style="font-weight: bold; margin-bottom: 4px;">Remarks</div>
                <div>{{ $msuWorkInvoice->notes ?: '--' }}</div>
            </div>
        </td>
        <td style="width:40%; vertical-align: top;">
            @php
                $sgst = (float) $msuWorkInvoice->sgst;
                $cgst = (float) $msuWorkInvoice->cgst;
                $igst = (float) $msuWorkInvoice->igst;
                $miscCharges = (float) $msuWorkInvoice->miscellaneous_charges;
                $finalTotal = (float) $msuWorkInvoice->grand_total;
            @endphp
            <table style="border: 1px solid #000; width: 100%; border-collapse: collapse;">
                <tr>
                    <td style="padding: 4px; border-bottom: 1px solid #ccc; font-weight: bold; width: 60%">Total Value (Items)</td>
                    <td style="padding: 4px; border-bottom: 1px solid #ccc; text-align: right; width: 40%">{{ number_format($grandGross, 2) }}</td>
                </tr>
                @if($miscCharges > 0 && $wo && $wo->charges->count() > 0)
                    @foreach($wo->charges as $charge)
                    <tr>
                        <td style="padding: 4px; border-bottom: 1px solid #ccc; font-weight: bold;">{{ $charge->charge_name }}</td>
                        <td style="padding: 4px; border-bottom: 1px solid #ccc; text-align: right;">{{ number_format((float) $charge->amount, 2) }}</td>
                    </tr>
                    @endforeach
                @elseif($miscCharges > 0)
                <tr>
                    <td style="padding: 4px; border-bottom: 1px solid #ccc; font-weight: bold;">Miscellaneous Charges</td>
                    <td style="padding: 4px; border-bottom: 1px solid #ccc; text-align: right;">{{ number_format($miscCharges, 2) }}</td>
                </tr>
                @endif
                <tr>
                    <td style="padding: 4px; border-bottom: 1px solid #ccc; font-weight: bold;">Total Miscellaneous Values</td>
                    <td style="padding: 4px; border-bottom: 1px solid #ccc; text-align: right;">{{ number_format($miscCharges, 2) }}</td>
                </tr>
                @if($igst > 0)
                <tr>
                    <td style="padding: 4px; border-bottom: 1px solid #ccc; font-weight: bold;">IGST</td>
                    <td style="padding: 4px; border-bottom: 1px solid #ccc; text-align: right;">{{ number_format($igst, 2) }}</td>
                </tr>
                @else
                <tr>
                    <td style="padding: 4px; border-bottom: 1px solid #ccc; font-weight: bold;">SGST</td>
                    <td style="padding: 4px; border-bottom: 1px solid #ccc; text-align: right;">{{ number_format($sgst, 2) }}</td>
                </tr>
                <tr>
                    <td style="padding: 4px; border-bottom: 1px solid #ccc; font-weight: bold;">CGST</td>
                    <td style="padding: 4px; border-bottom: 1px solid #ccc; text-align: right;">{{ number_format($cgst, 2) }}</td>
                </tr>
                @endif

                <tr>
                    <td style="padding: 4px; border-bottom: 1px solid #ccc; font-weight: bold; font-size: 11px;">Grand Total</td>
                    <td style="padding: 4px; border-bottom: 1px solid #ccc; text-align: right; font-weight: bold; font-size: 11px;">{{ number_format($finalTotal, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="2" style="padding: 4px;">
                        <span style="font-weight: bold;">In Words:</span><br>
                        {{ $amountInWords }}
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>

<div class="footer-box">
    <div class="footer-company">{{ strtoupper($branchTitle) }}</div>
    <table style="width: 100%;">
        <tr class="footer-inner">
            <td class="disclaimer">
                This is a computer generated Work Order Invoice, Hence Signature is not required.
            </td>
            <td class="signatory">
                Authorized Signatory
            </td>
        </tr>
    </table>
</div>
</div>
</body>
</html>
