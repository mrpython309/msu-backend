<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    @if(!isset($isExcel) || !$isExcel)
    <title>Material Issue - {{ $materialIssue->mi_number }}</title>
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
        .normal-text-name {
            font-weight: normal;
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
        .addr { margin-top: 2px; color: #444; line-height: 1.3; }
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
    </style>
</head>
<body>
@php
    $mi = $materialIssue;
    $mr = $mi->materialRequest;
    $branch = $mr?->branch;
    $company = $mr?->company ?: $branch?->company;
    $companyName = $company?->name ?? 'Mysore Saree Udyog LLP';
    $branchTitle = $branch
        ? trim(($branch->name ?: strtoupper($companyName)))
        : strtoupper($companyName);
    $branchAddress = collect([
        $branch?->gst_number,
        $branch?->address,
        $branch?->city,
        $branch?->state,
        $branch?->country,
    ])->filter()->implode(', ');
    $issueDate = $mi->issue_date
        ? (\Illuminate\Support\Carbon::parse($mi->issue_date)->format('d-m-Y'))
        : '--';
@endphp

@if(!isset($isExcel) || !$isExcel)
<div style="position: relative;">
    <div class="title" style="text-align: center; margin-bottom: 15px; margin-top: -20px; white-space: nowrap;">MATERIAL ISSUE</div>
</div>

<table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 10px;">
    <tr>
        <td style="width: 50%; vertical-align: top;">
            <div class="section-label">Issue From</div>
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
            <div>MI No: {{ $mi->mi_number }} / {{ $issueDate }}</div>
            <div>MR No: {{ $mr?->mr_number ?? '--' }}@if($mr?->request_date) / {{ \Illuminate\Support\Carbon::parse($mr->request_date)->format('d-m-Y') }}@endif</div>
        </td>
    </tr>
</table>

<table style="width: 100%; border-collapse: collapse; margin-bottom: 25px; font-size: 10px;">
    <tr>
        <td style="width: 50%; vertical-align: top; padding-right: 20px;">
            <div class="section-label">Department</div>
            <div class="normal-text-name">{{ strtoupper($mi->department?->department_name ?? '--') }}</div>
        </td>
        <td style="width: 50%; vertical-align: top; padding-left: 20px;">
            <div class="section-label">Employee</div>
            <div class="normal-text-name">{{ strtoupper($mi->employee?->employee_name ?? '--') }}</div>
        </td>
    </tr>
</table>
@endif

<table class="items">
    <thead>
        <tr>
            <th style="width:28px">SNo</th>
            <th style="text-align: left;">Item Name</th>
            <th style="width:70px">HSN</th>
            <th style="width:70px">Unit</th>
            <th style="width:90px">Requested Qty</th>
            <th style="width:90px">Issued Qty</th>
        </tr>
    </thead>
    <tbody>
        @php
            $totalRequested = 0;
            $totalIssued = 0;
        @endphp
        @foreach($mi->items as $item)
            @php
                $issuedQty = (float) $item->issue_qty;
                $requestedQty = (float) ($item->materialRequestItem?->requested_qty ?? 0);
                $mrItem = $item->materialRequestItem;
                $unit = $mrItem?->unit?->unit_name
                    ?? ($item->itemMaster && $item->itemMaster->relationLoaded('unit') ? $item->itemMaster->unit?->unit_name : null)
                    ?? $item->itemMaster?->uom
                    ?? 'NOS';
                $hsn = '';
                if ($item->itemMaster && $item->itemMaster->relationLoaded('hsn') && $item->itemMaster->hsn) {
                    $hsn = $item->itemMaster->hsn->hsn_code ?? '';
                }
                $totalRequested += $requestedQty;
                $totalIssued += $issuedQty;
            @endphp
            <tr>
                <td class="text-center">{{ $loop->iteration }}</td>
                <td class="text-left">
                    {{ $item->itemMaster?->item_name ?? '--' }}
                    @if(!isset($isExcel) || !$isExcel)
                        <br><span style="font-size: 8px; color: #555;">{{ $item->itemMaster?->item_code ?? '' }}</span>
                    @endif
                </td>
                <td class="text-center">{{ $hsn }}</td>
                <td class="text-center">{{ strtoupper($unit) }}</td>
                <td class="text-right">{{ number_format($requestedQty, 3) }}</td>
                <td class="text-right">{{ number_format($issuedQty, 3) }}</td>
            </tr>
        @endforeach
        @if(isset($isExcel) && $isExcel)
        <tr>
            <th colspan="4" style="text-align: right;">Total:</th>
            <th style="text-align: right;">{{ number_format($totalRequested, 3) }}</th>
            <th style="text-align: right;">{{ number_format($totalIssued, 3) }}</th>
        </tr>
        @endif
    </tbody>
</table>

@if(!isset($isExcel) || !$isExcel)
@if($mi->remarks)
<div style="margin-top: 12px; font-size: 10px;">
    <strong>Remarks:</strong> {{ $mi->remarks }}
</div>
@endif

<div style="page-break-inside: avoid;">
    <div class="footer-box">
        <div class="footer-company">{{ strtoupper($branchTitle) }}</div>
        <table>
            <tr class="footer-inner">
                <td class="disclaimer">
                    This is computer generated Material Issue, Hence Signature is not required.
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
