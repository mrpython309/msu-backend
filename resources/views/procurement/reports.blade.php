@extends('layouts/contentNavbarLayout')

@section('title', 'Procurement Reports')

@section('content')
<div class="card">
    <div class="card-header">
        <h4 class="mb-0">Procurement Reports</h4>
    </div>
    <div class="list-group list-group-flush">
        @foreach ([
            'indents' => 'Indent Report',
            'pending-approvals' => 'Pending Approvals',
            'purchase-requisitions' => 'Purchase Requisition Report',
            'rfqs' => 'RFQ Report',
            'purchase-orders' => 'Purchase Order Report',
            'payments' => 'Payment Report',
            'vendor-performance' => 'Vendor Performance Report',
            'grns' => 'GRN Report',
            'asset-creation' => 'Asset Creation Report',
        ] as $key => $label)
            <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-center"
               href="{{ route('procurement.reports.show', $key) }}">
                <span>{{ $label }}</span>
                <i class="bx bx-chevron-right"></i>
            </a>
        @endforeach
    </div>
</div>
@endsection
