@extends('layouts/contentNavbarLayout')

@section('title', 'RFQ Details')

@section('vendor-style')
<style>
    .rfq-vendor-card {
        border: 1px solid #e7e7ff;
        border-radius: 0.75rem;
        box-shadow: 0 0.25rem 1rem rgba(34, 48, 62, 0.06);
        height: 100%;
        transition: box-shadow 0.2s ease, transform 0.2s ease;
        background: #fff;
    }
    .rfq-vendor-card:hover {
        box-shadow: 0 0.5rem 1.25rem rgba(34, 48, 62, 0.1);
        transform: translateY(-2px);
    }
    .rfq-vendor-card .card-body {
        padding: 1.25rem;
    }
    .rfq-vendor-card .vendor-name {
        font-size: 1.05rem;
        font-weight: 650;
        color: #384551;
        margin-bottom: 0.85rem;
    }
    .rfq-vendor-meta {
        display: grid;
        gap: 0.45rem;
        margin-bottom: 1rem;
    }
    .rfq-vendor-meta .meta-row {
        display: flex;
        gap: 0.5rem;
        font-size: 0.875rem;
        line-height: 1.35;
    }
    .rfq-vendor-meta .meta-label {
        min-width: 7.5rem;
        color: #a1acb8;
        flex-shrink: 0;
    }
    .rfq-vendor-meta .meta-value {
        color: #566a7f;
        word-break: break-word;
    }
    .rfq-vendor-stats {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.65rem 1rem;
        padding-top: 0.85rem;
        border-top: 1px solid #f0f2f4;
        margin-bottom: 1rem;
    }
    .rfq-vendor-stats .stat-label {
        display: block;
        font-size: 0.72rem;
        color: #a1acb8;
        text-transform: uppercase;
        letter-spacing: 0.02em;
    }
    .rfq-vendor-stats .stat-value {
        font-weight: 600;
        color: #384551;
        font-size: 0.9rem;
    }
    .rfq-status-pending { background: #fff3cd; color: #856404; }
    .rfq-status-invited { background: #cfe2ff; color: #084298; }
    .rfq-status-quoted { background: #e2d5f5; color: #5b2c8a; }
    .rfq-status-selected { background: #d1e7dd; color: #0f5132; }
    .rfq-status-rejected { background: #f8d7da; color: #842029; }
</style>
@endsection

@section('content')
@php
    $indent = $rfq->purchaseRequisition?->indent;
    $items = $indent?->items ?? collect();
    $quotationsByVendor = $quotationsByVendor ?? collect();
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="mb-0">{{ $rfq->rfq_no }}</h4>
        <span class="badge bg-label-info">{{ $rfq->status->value ?? $rfq->status }}</span>
    </div>
    <div class="d-flex gap-2 flex-wrap">
        @if ($isDraft ?? false)
            @canany(['rfq.send', 'rfq.edit', 'rfqs:edit', 'rfq.create', 'rfqs:create'])
                @if ($rfq->rfqVendors->isNotEmpty())
                    <form method="POST" action="{{ route('rfqs.send', $rfq) }}" class="d-inline">
                        @csrf
                        @foreach ($rfq->rfqVendors as $pivot)
                            <input type="hidden" name="vendor_ids[]" value="{{ $pivot->vendor_id }}">
                        @endforeach
                        <button type="submit" class="btn btn-primary btn-sm">Send RFQ</button>
                    </form>
                @else
                    <span class="btn btn-primary btn-sm disabled" title="Add vendors when creating the RFQ">Send RFQ</span>
                @endif
            @endcanany
        @else
            @canany(['quotation.add', 'quotations:create'])
                @if (($canRecordQuotation ?? true) && !($hasPoFromQuotation ?? false) && !($hasSelectedVendor ?? false))
                    <a class="btn btn-primary btn-sm" href="{{ route('rfqs.quotations.create', $rfq) }}">
                        <i class="bx bx-plus me-1"></i> Record Quotation
                    </a>
                @endif
            @endcanany
        @endif
        @unless (($hasSelectedVendor ?? false) || ($hasPoFromQuotation ?? false) || ($isDraft ?? false))
            @if ($rfq->quotations->count() > 1)
                @canany(['quotation.compare', 'quotations:compare'])
                    <a class="btn btn-outline-primary btn-sm" href="{{ route('rfqs.compare', $rfq) }}">Vendor Comparison</a>
                @endcanany
            @endif
        @endunless
        @if ($rfq->canExportBeforeQuotations())
            @canany(['rfq.view', 'rfqs:view'])
                <a class="btn btn-outline-danger btn-sm" href="{{ route('rfqs.pdf', $rfq) }}">
                    <i class="bx bxs-file-pdf me-1"></i> Download PDF
                </a>
                <a class="btn btn-outline-success btn-sm" href="{{ route('rfqs.excel', $rfq) }}">
                    <i class="bx bx-export me-1"></i> Download Excel
                </a>
            @endcanany
        @endif
        <a class="btn btn-secondary btn-sm" href="{{ route('rfqs.index') }}">Back</a>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card mb-4">
    <div class="card-header"><h5 class="mb-0">Basic Information</h5></div>
    <div class="card-body row g-3">
        <div class="col-md-3">
            <small class="text-muted d-block">RFQ No.</small>
            <strong>{{ $rfq->rfq_no }}</strong>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">RFQ Date</small>
            <strong>{{ $rfq->rfq_date?->format('d M Y') ?? $rfq->created_at?->format('d M Y') }}</strong>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">Purchase Requisition</small>
            <strong>{{ $rfq->purchaseRequisition->pr_no ?? '--' }}</strong>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">Department</small>
            <strong>{{ $indent?->department?->department_name ?? '--' }}</strong>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">Priority</small>
            <strong>{{ $rfq->priority ?: ($indent?->priority ?: '--') }}</strong>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">Closing Date</small>
            <strong>{{ $rfq->closes_at?->format('d M Y H:i') ?? '--' }}</strong>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">Expected Delivery</small>
            <strong>{{ $rfq->expected_delivery_date?->format('d M Y') ?? '--' }}</strong>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">Delivery Store</small>
            <strong>{{ $rfq->delivery_store ?: '--' }}</strong>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">Currency</small>
            <strong>{{ $rfq->currency ?: 'INR' }}</strong>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Vendor Information</h5>
    </div>
    <div class="card-body">
        @if ($rfq->rfqVendors->isEmpty())
            <div class="text-center text-muted py-4">No vendors associated with this RFQ.</div>
        @else
            <div class="d-flex flex-column gap-3">
                @foreach ($rfq->rfqVendors as $pivot)
                    @php
                        $vendor = $pivot->vendor;
                        $quotation = $quotationsByVendor->get($pivot->vendor_id);
                        $rawStatus = (string) ($pivot->status ?? 'Pending');

                        if ($quotation?->is_selected) {
                            $displayStatus = 'Selected';
                        } elseif ($quotation || strcasecmp($rawStatus, 'Quoted') === 0) {
                            $displayStatus = 'Quoted';
                        } elseif (strcasecmp($rawStatus, 'Rejected') === 0) {
                            $displayStatus = 'Rejected';
                        } elseif (strcasecmp($rawStatus, 'Invited') === 0) {
                            $displayStatus = 'Invited';
                        } else {
                            $displayStatus = 'Pending';
                        }

                        $statusClass = match (strtolower($displayStatus)) {
                            'pending' => 'rfq-status-pending',
                            'invited' => 'rfq-status-invited',
                            'quoted' => 'rfq-status-quoted',
                            'selected' => 'rfq-status-selected',
                            'rejected' => 'rfq-status-rejected',
                            default => 'rfq-status-pending',
                        };

                        $contactPerson = $quotation?->contact_person ?: ($vendor->name ?? '--');
                        $email = $quotation?->contact_email ?: ($vendor->email ?? '--');
                        $phone = $quotation?->contact_phone ?: ($vendor->phone_number ?? '--');
                        $location = collect([$vendor->city ?? null, $vendor->state ?? null])->filter()->implode(', ') ?: '--';
                        $address = collect([
                            $vendor->address ?? null,
                            $vendor->city ?? null,
                            $vendor->state ?? null,
                            $vendor->country ?? null,
                        ])->filter()->implode(', ') ?: '--';
                        $gst = $vendor->gst_number ?: null;
                        $quoteAmount = $quotation?->effectiveQuote();
                        $originalAmount = $quotation?->originalQuote();
                        $hasNegotiated = $quotation?->hasNegotiatedPrice() ?? false;
                        $quoteDate = $quotation?->quoted_at ?? $quotation?->created_at;
                    @endphp
                    <div class="card rfq-vendor-card border-0 w-100">
                        <div class="card-body">
                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                                <div class="vendor-name mb-0">{{ $loop->iteration }}. {{ $vendor->name ?? 'Vendor #'.$pivot->vendor_id }}</div>
                                <span class="badge rounded-pill {{ $statusClass }}">{{ $displayStatus }}</span>
                            </div>

                            <div class="rfq-vendor-meta mb-3">
                                <div class="meta-row">
                                    <span class="meta-label">Contact Person</span>
                                    <span class="meta-value">{{ $contactPerson }}</span>
                                </div>
                                <div class="meta-row">
                                    <span class="meta-label">Email</span>
                                    <span class="meta-value">{{ $email }}</span>
                                </div>
                                <div class="meta-row">
                                    <span class="meta-label">Phone</span>
                                    <span class="meta-value">{{ $phone }}</span>
                                </div>
                                <div class="meta-row">
                                    <span class="meta-label">Location</span>
                                    <span class="meta-value">{{ $location }}</span>
                                </div>
                                <div class="meta-row">
                                    <span class="meta-label">Address</span>
                                    <span class="meta-value">{{ $address }}</span>
                                </div>
                                @if ($gst)
                                    <div class="meta-row">
                                        <span class="meta-label">GST Number</span>
                                        <span class="meta-value">{{ $gst }}</span>
                                    </div>
                                @endif
                            </div>

                            <div class="rfq-vendor-stats">
                                <div>
                                    <span class="stat-label">{{ $hasNegotiated ? 'Negotiated Amount' : 'Quotation Amount' }}</span>
                                    <span class="stat-value">
                                        {{ $quotation ? number_format((float) $quoteAmount, 2) : '--' }}
                                    </span>
                                    @if ($hasNegotiated)
                                        <small class="d-block text-muted">Original: {{ number_format((float) $originalAmount, 2) }}</small>
                                    @endif
                                </div>
                                <div>
                                    <span class="stat-label">Quotation Date</span>
                                    <span class="stat-value">{{ $quoteDate?->format('d M Y') ?? '--' }}</span>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap gap-2">
                                @if ($quotation)
                                    <a href="{{ route('rfqs.quotations.show', [$rfq, $quotation]) }}" class="btn btn-sm btn-outline-primary">
                                        View Quotation
                                    </a>
                                @else
                                    <button type="button" class="btn btn-sm btn-outline-secondary" disabled>View Quotation</button>
                                @endif
                                @unless (($hasSelectedVendor ?? false) || ($hasPoFromQuotation ?? false) || ($isDraft ?? false))
                                    @if ($rfq->quotations->count() > 1)
                                        @canany(['quotation.compare', 'quotations:compare'])
                                            <a href="{{ route('rfqs.compare', $rfq) }}" class="btn btn-sm btn-outline-secondary">
                                                Compare
                                            </a>
                                        @endcanany
                                    @endif
                                @endunless
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>

<div class="card mb-4">
    <div class="card-header"><h5 class="mb-0">Item Details</h5></div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Code</th>
                    <th>HSN</th>
                    <th>Qty</th>
                    <th>Unit</th>
                    <th>Manufacturer</th>
                    <th>Remarks</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($items as $item)
                    <tr>
                        <td>{{ $item->item_name }}</td>
                        <td>IND-{{ str_pad((string) $item->id, 6, '0', STR_PAD_LEFT) }}</td>
                        <td>--</td>
                        <td>{{ $item->quantity }}</td>
                        <td>{{ $item->unit ?? '--' }}</td>
                        <td>{{ $item->manufacturer ?: '--' }}</td>
                        <td>{{ $item->item_remarks ?: '--' }}</td>
                        <td>
                            <button type="button" class="btn btn-sm btn-outline-info btn-view-history" data-item-name="{{ $item->item_name }}" data-item-code="IND-{{ str_pad((string) $item->id, 6, '0', STR_PAD_LEFT) }}">
                                <i class="bx bx-history me-1"></i> View Previous Item History
                            </button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center text-muted">No items.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if ($rfq->attachments->isNotEmpty())
<div class="card mb-4">
    <div class="card-header"><h5 class="mb-0">Attachments</h5></div>
    <ul class="list-group list-group-flush">
        @foreach ($rfq->attachments as $file)
            <li class="list-group-item d-flex justify-content-between align-items-center">
                <span>{{ $file->original_name }}</span>
                <div class="d-flex gap-1">
                    <a class="btn btn-sm btn-outline-primary" href="{{ route('rfqs.attachments.show', [$rfq, $file]) }}" target="_blank">View</a>
                    <a class="btn btn-sm btn-outline-secondary" href="{{ route('rfqs.attachments.show', [$rfq, $file, 'download']) }}">Download</a>
                </div>
            </li>
        @endforeach
    </ul>
</div>
@endif
@include('procurement._item-history-modal')
@endsection
