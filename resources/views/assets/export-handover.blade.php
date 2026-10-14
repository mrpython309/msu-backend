<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Asset Handover {{ $asset->asset_code }}</title>
    <style>
        @page { margin: 10mm 8mm; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #000; margin: 0; }
        table { width: 100%; border-collapse: collapse; table-layout: fixed; }
        td, th { border: 1px solid #000; padding: 4px 6px; vertical-align: middle; word-wrap: break-word; }
        .title {
            text-align: center;
            font-size: 16px;
            font-weight: bold;
            color: #9b1b1b;
            letter-spacing: 0.6px;
            padding: 7px 4px;
            background: #f3f3f3;
        }
        .lbl { font-weight: bold; width: 18%; text-transform: uppercase; white-space: nowrap; }
        .val { width: 32%; text-transform: uppercase; }
        .code { background: #d9d9d9; font-weight: bold; text-transform: uppercase; }
        .intro { padding: 8px 6px; line-height: 1.45; }
        .items th { text-align: center; font-weight: bold; font-size: 8px; text-transform: uppercase; }
        .items td { text-align: center; height: 36px; vertical-align: middle; }
        .items td.desc { text-align: left; text-transform: uppercase; }
        .sign-head { font-weight: bold; text-align: left; font-size: 8px; text-transform: uppercase; }
        .sign-space { height: 58px; }
        .ack-title { font-weight: bold; text-transform: uppercase; padding-bottom: 2px; }
        .ack { padding: 6px; line-height: 1.45; text-transform: uppercase; }
        .emp-sign { text-align: right; font-weight: bold; padding-top: 28px; }
        .remarks-box { height: 42px; vertical-align: top; }
        .inv-lbl { width: 22%; font-weight: bold; text-transform: uppercase; }
        .inv-val { text-transform: uppercase; }
    </style>
</head>
<body>
@php
    $assignment = $asset->latestAssignment;
    $custodian = $assignment?->employee ?: $asset->custodian;
    $department = $assignment?->department
        ?: $custodian?->department
        ?: $asset->department;
    $company = $handoverCompany ?? $asset->company;
    $companyName = $company?->name ?: '';
    $branchName = $asset->branch?->name
        ?: $assignment?->toBranch?->name
        ?: $assignment?->fromBranch?->name
        ?: '';

    $location = $asset->location?->location_name
        ?: $assignment?->toBranchLocation?->location_name
        ?: $assignment?->fromBranchLocation?->location_name;

    $assignedDate = $assignment?->assign_date
        ? $assignment->assign_date->format('d-M-Y')
        : '';

    $employeeName = $custodian?->employee_name ?: '';

    $uom = $asset->uom
        ?: ($asset->msuGrnItem?->itemMaster?->unit?->unit_name
            ?: ($asset->msuGrnItem?->itemMaster?->uom ?: ''));
    $invoiceNo = $asset->invoice_details ?: ($asset->msuGrnItem?->grn?->vendor_invoice_number ?: '');
    $invoiceDate = $asset->invoice_date ?: $asset->msuGrnItem?->grn?->vendor_invoice_date;
    $poNo = $asset->po_number ?: ($asset->msuGrnItem?->grn?->purchaseOrder?->po_number ?: '');
    $poDate = $asset->po_date ?: $asset->msuGrnItem?->grn?->purchaseOrder?->po_date;
    $amount = number_format((float) ($asset->purchase_cost ?? 0), 2, '.', '');
    $assetValue = str_ends_with($amount, '.00') ? substr($amount, 0, -3) : $amount;
    $otherRemarks = trim(strip_tags((string) $asset->description));

    $companyChars = max(mb_strlen(trim($companyName)), 1);
    $branchChars = max(mb_strlen(trim($branchName)), 1);
    $alignedLimit = 26;
    if ($companyChars <= $alignedLimit && $branchChars <= $alignedLimit) {
        $companyLabelPct = 18;
        $companyValuePct = 32;
        $branchLabelPct = 18;
        $branchValuePct = 32;
    } else {
        $companyLabelPct = 12;
        $branchLabelPct = 11;
        $remaining = 100 - $companyLabelPct - $branchLabelPct;
        $companyValuePct = (int) max(18, round($remaining * $companyChars / ($companyChars + $branchChars)));
        $branchValuePct = $remaining - $companyValuePct;
        if ($branchValuePct < 18) {
            $branchValuePct = 18;
            $companyValuePct = $remaining - $branchValuePct;
        }
    }
@endphp

<table>
    <tr>
        <td class="title">ASSET HANDOVER FORM</td>
    </tr>
</table>

<table style="margin-top: -1px;">
    <colgroup>
        <col style="width: {{ $companyLabelPct }}%;">
        <col style="width: {{ $companyValuePct }}%;">
        <col style="width: {{ $branchLabelPct }}%;">
        <col style="width: {{ $branchValuePct }}%;">
    </colgroup>
    <tr>
        <td class="lbl">Company</td>
        <td class="val">{{ $companyName }}</td>
        <td class="lbl">Branch</td>
        <td class="val">{{ $branchName }}</td>
    </tr>
</table>

<table style="margin-top: -1px;">
    <colgroup>
        <col style="width: 18%;">
        <col style="width: 32%;">
        <col style="width: 18%;">
        <col style="width: 32%;">
    </colgroup>
    <tr>
        <td class="lbl">Name of Employee</td>
        <td class="val">{{ $employeeName }}</td>
        <td class="lbl">Asset Code</td>
        <td class="code">{{ $asset->asset_code }}</td>
    </tr>
    <tr>
        <td class="lbl">Department</td>
        <td class="val">{{ $department?->department_name }}</td>
        <td class="lbl">Assigned Date</td>
        <td class="val">{{ $assignedDate }}</td>
    </tr>
    <tr>
        <td class="lbl">Location</td>
        <td class="val">{{ $location }}</td>
        <td class="lbl">Cost Centre</td>
        <td class="val">{{ $asset->cost_centre }}</td>
    </tr>
</table>

<table style="margin-top: -1px;">
    <tr>
        <td class="intro">
            We congratulate you on joining our Esteemed Organisation and we hereby provide you the list of properties to be handed over to you, to support you in carrying out your day to day responsibilities proficiently.
        </td>
    </tr>
</table>

<table class="items" style="margin-top: -1px;">
    <tr>
        <th style="width: 10%;">SL No</th>
        <th style="width: 40%;">Asset Description</th>
        <th style="width: 12%;">UOM</th>
        <th style="width: 10%;">Qty</th>
        <th style="width: 28%;">Asset Model No / SL No</th>
    </tr>
    <tr>
        <td>1</td>
        <td class="desc">{{ $asset->asset_name }}</td>
        <td>{{ $uom }}</td>
        <td>{{ $asset->items_count ?: 1 }}</td>
        <td>{{ $asset->serial_no }}</td>
    </tr>
</table>

<table style="margin-top: -1px;">
    <tr>
        <td class="sign-head" style="width: 50%;">Authorized Signatory<br>(Approver)</td>
        <td class="sign-head" style="width: 50%; text-align: right;">Authorized Signatory<br>(Person Responsible for Hand-over)</td>
    </tr>
    <tr>
        <td class="sign-space"></td>
        <td class="sign-space"></td>
    </tr>
</table>

<table style="margin-top: -1px;">
    <tr>
        <td class="ack-title">Acknowledgement and Declaration by Employee</td>
    </tr>
    <tr>
        <td class="ack">
            I Mr/Mrs. &nbsp;&nbsp; <strong>{{ $employeeName }}</strong> &nbsp;&nbsp; hereby acknowledge that I have received the above mentioned assets.<br>
            I understand that this asset belong to {{ $companyName }} and is under possession for carrying I hereby<br>
            assure &nbsp; that I will take care of the assets of the company to the best possible extend.
            <div class="emp-sign">Employee Signature</div>
        </td>
    </tr>
</table>

<table style="margin-top: -1px;">
    <tr>
        <td class="remarks-box">
            <strong>OTHER REMARKS:-</strong>
            @if($otherRemarks !== '')
                <div style="margin-top: 4px;">{{ $otherRemarks }}</div>
            @endif
        </td>
    </tr>
</table>

<table style="margin-top: -1px;">
    <tr>
        <td class="inv-lbl">Invoice No</td>
        <td class="inv-val">{{ $invoiceNo }}</td>
    </tr>
    <tr>
        <td class="inv-lbl">Invoice Date</td>
        <td class="inv-val">{{ $invoiceDate ? \Illuminate\Support\Carbon::parse($invoiceDate)->format('d/m/Y') : '' }}</td>
    </tr>
    <tr>
        <td class="inv-lbl">PO No</td>
        <td class="inv-val">{{ $poNo }}</td>
    </tr>
    <tr>
        <td class="inv-lbl">PO Date</td>
        <td class="inv-val">{{ $poDate ? \Illuminate\Support\Carbon::parse($poDate)->format('d/m/Y') : '' }}</td>
    </tr>
    <tr>
        <td class="inv-lbl">Asset Value</td>
        <td class="inv-val">{{ $assetValue }}</td>
    </tr>
</table>
</body>
</html>
