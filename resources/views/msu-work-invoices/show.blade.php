@extends('layouts/contentNavbarLayout')

@section('title', 'Work Invoice ' . $msuWorkInvoice->wi_number)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Work Invoice — {{ $msuWorkInvoice->wi_number }}</h4>
    <div>
        <a href="{{ route('msu-work-invoices.pdf', $msuWorkInvoice) }}" target="_blank" class="btn btn-primary btn-sm">
            <i class="bx bxs-file-pdf me-1"></i> PDF
        </a>
        <a href="{{ route('msu-work-orders.index') }}" class="btn btn-secondary btn-sm ms-2">Back</a>
    </div>
</div>

@if(session('success'))
<div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-3 mb-3"><small class="text-muted d-block">WI Number</small><strong>{{ $msuWorkInvoice->wi_number }}</strong></div>
            <div class="col-md-3 mb-3"><small class="text-muted d-block">WI Date</small><strong>{{ $msuWorkInvoice->invoice_date?->format('d-m-Y') }}</strong></div>
            <div class="col-md-3 mb-3"><small class="text-muted d-block">Status</small>
                @php
                    $status = $msuWorkInvoice->workOrder?->statusEnum()
                        ?? $msuWorkInvoice->statusEnum();
                @endphp
                <span class="badge bg-label-{{ $status->badgeClass() }}">{{ $status->label() }}</span>
            </div>
            <div class="col-md-3 mb-3"><small class="text-muted d-block">WO Number</small><strong>{{ $msuWorkInvoice->workOrder?->wo_number ?? '--' }}</strong></div>
            <div class="col-md-3 mb-3"><small class="text-muted d-block">Vendor</small><strong>{{ $msuWorkInvoice->vendor?->name ?? '--' }}</strong></div>
            <div class="col-md-3 mb-3"><small class="text-muted d-block">Branch</small><strong>{{ $msuWorkInvoice->branch?->name ?? '--' }}</strong></div>
            <div class="col-md-3 mb-3"><small class="text-muted d-block">Vendor Invoice Number</small><strong>{{ $msuWorkInvoice->invoice_number ?: '--' }}</strong></div>
            <div class="col-md-12 mb-0"><small class="text-muted d-block">Remarks</small><strong>{{ $msuWorkInvoice->notes ?: '--' }}</strong></div>
        </div>
    </div>
</div>

@if($msuWorkInvoice->document_path)
<div class="card mb-4">
    <div class="card-header"><h5 class="mb-0">Uploaded Document</h5></div>
    <div class="card-body">
        <a href="{{ route('msu-work-invoices.download-document', $msuWorkInvoice->id) }}" target="_blank" class="btn btn-primary">
            <i class="bx bx-download me-1"></i> View / Download Document
        </a>
    </div>
</div>
@endif

<div class="card mb-4">
    <div class="card-header"><h5 class="mb-0">Invoice Items</h5></div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Work Description</th>
                    <th>HSN</th>
                    <th class="text-end">Received Qty</th>
                    <th class="text-end">Rate</th>
                    <th class="text-end">Gross Value</th>
                    <th class="text-end">Tax %</th>
                    <th class="text-end">Tax Value</th>
                    <th class="text-end">Net Value</th>
                </tr>
            </thead>
            <tbody>
                @foreach($msuWorkInvoice->items as $item)
                @php
                    $qty = (float) $item->quantity;
                    $rate = (float) $item->price;
                    $taxPct = (float) $item->tax;
                    $gross = $item->grossValue();
                    $taxVal = $gross * ($taxPct / 100);
                    $net = (float) $item->net_value;
                @endphp
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $item->work_description }}</td>
                    <td>{{ $item->workOrderItem?->hsn ?: '--' }}</td>
                    <td class="text-end">{{ number_format($qty, 3) }}</td>
                    <td class="text-end">{{ number_format($rate, 2) }}</td>
                    <td class="text-end">{{ number_format($gross, 2) }}</td>
                    <td class="text-end">{{ number_format($taxPct, 2) }}</td>
                    <td class="text-end">{{ number_format($taxVal, 2) }}</td>
                    <td class="text-end">{{ number_format($net, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<div class="row">
    <div class="col-md-6 offset-md-6">
        <div class="card mb-4">
            <div class="card-header"><h5 class="mb-0">Summary</h5></div>
            <div class="card-body">
                <table class="table table-borderless mb-0">
                    <tr>
                        <td class="ps-0 py-1"><strong>Total Value (Items)</strong></td>
                        <td class="text-end pe-0 py-1">{{ number_format($msuWorkInvoice->total_value, 2) }}</td>
                    </tr>
                    @php
                        $wo = $msuWorkInvoice->workOrder;
                        $invoiceMiscTotal = (float) $msuWorkInvoice->miscellaneous_charges;
                    @endphp
                    @if($invoiceMiscTotal > 0)
                        @if($wo && $wo->charges && $wo->charges->count() > 0)
                            @foreach($wo->charges as $charge)
                            <tr>
                                <td class="ps-0 py-1"><strong>{{ $charge->charge_name }}</strong></td>
                                <td class="text-end pe-0 py-1">{{ number_format((float) $charge->amount, 2) }}</td>
                            </tr>
                            @endforeach
                        @else
                            <tr>
                                <td class="ps-0 py-1"><strong>Miscellaneous Charges</strong></td>
                                <td class="text-end pe-0 py-1">{{ number_format($invoiceMiscTotal, 2) }}</td>
                            </tr>
                        @endif
                    @endif
                    <tr>
                        <td class="ps-0 py-1"><strong>Total Miscellaneous Values</strong></td>
                        <td class="text-end pe-0 py-1">{{ number_format($invoiceMiscTotal, 2) }}</td>
                    </tr>
                    @if((float) $msuWorkInvoice->igst > 0)
                        <tr>
                            <td class="ps-0 py-1"><strong>IGST</strong></td>
                            <td class="text-end pe-0 py-1">{{ number_format($msuWorkInvoice->igst, 2) }}</td>
                        </tr>
                    @else
                        <tr>
                            <td class="ps-0 py-1"><strong>SGST</strong></td>
                            <td class="text-end pe-0 py-1">{{ number_format($msuWorkInvoice->sgst, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="ps-0 py-1"><strong>CGST</strong></td>
                            <td class="text-end pe-0 py-1">{{ number_format($msuWorkInvoice->cgst, 2) }}</td>
                        </tr>
                    @endif
                    <tr class="border-top">
                        <td class="ps-0 py-2"><strong>Grand Total</strong></td>
                        <td class="text-end pe-0 py-2"><strong>{{ number_format($msuWorkInvoice->grand_total, 2) }}</strong></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
