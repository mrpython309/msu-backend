@extends('layouts/contentNavbarLayout')

@section('title', 'View Quotation')

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="mb-1">Quotation — {{ $quotation->vendor->name ?? 'Vendor' }}</h4>
    </div>
    <div class="d-flex gap-2">
        @unless ($quotation->isNegotiationLocked() || $rfq->selectedQuotation()->exists())
            @canany(['quotation.compare', 'quotations:compare'])
                <a href="{{ route('rfqs.compare', $rfq) }}" class="btn btn-outline-primary btn-sm">Compare</a>
            @endcanany
        @endunless
        <a href="{{ route('rfqs.show', $rfq) }}" class="btn btn-secondary btn-sm">Back to RFQ</a>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h5 class="mb-0">Vendor &amp; Quote Summary</h5></div>
    <div class="card-body row g-3">
        <div class="col-md-3">
            <small class="text-muted d-block">Vendor</small>
            <strong>{{ $quotation->vendor->name ?? '--' }}</strong>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">Contact Person</small>
            <strong>{{ $quotation->contact_person ?: '--' }}</strong>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">Email</small>
            <strong>{{ $quotation->contact_email ?: ($quotation->vendor->email ?? '--') }}</strong>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">Phone</small>
            <strong>{{ $quotation->contact_phone ?: ($quotation->vendor->phone_number ?? '--') }}</strong>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">Quoted At</small>
            <strong>{{ $quotation->quoted_at?->format('d M Y H:i') ?? $quotation->created_at?->format('d M Y H:i') ?? '--' }}</strong>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">Price</small>
            <strong>{{ number_format((float) $quotation->price, 2) }}</strong>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">Tax / Shipping</small>
            <strong>{{ number_format((float) $quotation->tax, 2) }} / {{ number_format((float) $quotation->shipping, 2) }}</strong>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">Original Total</small>
            <strong>{{ number_format($quotation->originalQuote(), 2) }}</strong>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">{{ $quotation->hasNegotiatedPrice() ? 'Negotiated Total' : 'Total Cost' }}</small>
            <strong>{{ number_format($quotation->effectiveQuote(), 2) }}</strong>
            @if ($quotation->hasNegotiatedPrice())
                <div class="small text-success">Savings: {{ number_format($quotation->savingsAmount(), 2) }}</div>
            @endif
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">Delivery Days</small>
            <strong>{{ $quotation->delivery_days ?? '--' }}</strong>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">Warranty</small>
            <strong>{{ $quotation->warranty ?: '--' }}</strong>
        </div>
        @if ($quotation->notes)
            <div class="col-12">
                <small class="text-muted d-block">Notes</small>
                <strong>{{ $quotation->notes }}</strong>
            </div>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header"><h5 class="mb-0">Quoted Items</h5></div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Unit</th>
                    <th>Unit Price</th>
                    <th>Line Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($quotation->items as $item)
                    <tr>
                        <td>{{ $item->item_name }}</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ $item->unit ?? '--' }}</td>
                        <td>{{ number_format((float) $item->unit_price, 2) }}</td>
                        <td>{{ number_format((float) ($item->total ?? ($item->quantity * $item->unit_price)), 2) }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center text-muted">No line items.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
