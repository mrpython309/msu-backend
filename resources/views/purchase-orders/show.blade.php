@extends('layouts/contentNavbarLayout')

@section('title', 'Purchase Order')

@section('content')
@php
    $status = $purchaseOrder->statusEnum();
    $statusValue = $status?->value ?? (string) $purchaseOrder->status;
    $isPending = $purchaseOrder->isPendingApproval();
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="mb-0">PO {{ $purchaseOrder->po_no }}</h4>
    </div>
    <div class="d-flex flex-wrap gap-2">
        @if ($status === \App\Enums\Procurement\PurchaseOrderStatus::Draft)
            @canany(['purchase-order.create', 'purchase-orders:create'])
                <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#submitPoModal">
                    Submit for Approval
                </button>
            @endcanany
        @endif

        {{-- Approve / Reject only on the show (view) page — never on edit --}}
        @if (($canActOnCurrentStep ?? false) && ! request()->routeIs('purchase-orders.edit'))
            <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#approvePoModal">
                Approve
            </button>
            <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectPoModal">
                Reject
            </button>
        @elseif ($isPending && ($awaitingAssigneeLabel ?? $purchaseOrder->assignedApprover))
            <span class="badge bg-label-warning align-self-center">
                Awaiting: {{ $awaitingAssigneeLabel ?? $purchaseOrder->assignedApprover->name }} (Level {{ $purchaseOrder->current_sequence }})
            </span>
        @endif

        @php
            $poRemaining = (float) ($paymentSummary['remaining'] ?? 0);
            $activePaymentReq = $purchaseOrder->activePaymentRequest();
            $hasAnyPaymentOrRequest = $purchaseOrder->paymentRequests()->exists() || $purchaseOrder->paidAmount() > 0 || $purchaseOrder->payments()->exists();
            $canCreatePaymentRequest = ! $hasAnyPaymentOrRequest && $poRemaining > 0 && ! $purchaseOrder->isRejectedStatus() && ($purchaseOrder->isCompletedStatus() || $purchaseOrder->allowsPayment());
            $payStatusVal = $purchaseOrder->paymentStatusEnum()->value;
        @endphp

        @if ($poRemaining <= 0 || strcasecmp($payStatusVal, 'Paid') === 0)
            <span id="po-fully-paid-badge" class="badge bg-label-success align-self-center">
                Purchase Order Fully Paid
            </span>
        @elseif ($purchaseOrder->paidAmount() > 0 || strcasecmp($payStatusVal, 'Partially Paid') === 0)
            <span class="badge bg-label-primary align-self-center">
                Payment Status: Partially Paid
            </span>
        @elseif ($activePaymentReq || $purchaseOrder->paymentRequests()->exists())
            @php
                $displayedReqStatus = $activePaymentReq ? $activePaymentReq->status : ($purchaseOrder->paymentRequests()->latest('id')->first()?->status ?? 'Requested');
            @endphp
            <span class="badge bg-label-info align-self-center">
                Payment Request: {{ $displayedReqStatus }}
            </span>
        @elseif ($canCreatePaymentRequest)
            <form action="{{ route('payment-request.store', $purchaseOrder) }}" method="POST" class="d-inline payment-req-form">
                @csrf
                <button type="submit" class="btn btn-primary btn-sm btn-payment-request-confirm" data-po="{{ $purchaseOrder->po_no }}" data-total="{{ (float) ($purchaseOrder->grand_total ?: $purchaseOrder->total_amount ?: 0) }}" data-min-adv="{{ (float) ($purchaseOrder->minimum_advance_payment_percentage ?? 0) }}">
                    <i class="bx bx-credit-card me-1"></i> Payment Request
                </button>
            </form>
        @elseif ($poRemaining > 0 && ! $purchaseOrder->isRejectedStatus() && ! $purchaseOrder->isCompletedStatus())
            <span class="badge bg-label-secondary align-self-center" title="Complete PO approval before requesting payment">
                <i class="bx bx-lock-alt"></i> Payment locked until approval
            </span>
        @endif

        @if ($purchaseOrder->isRejectedStatus() && $purchaseOrder->canBeDeleted())
            @canany(['purchase-order.delete', 'purchase-orders:delete'])
                <form method="POST" action="{{ route('purchase-orders.destroy', $purchaseOrder) }}" class="d-inline"
                      onsubmit="return confirm('Delete purchase order {{ $purchaseOrder->po_no }}? This cannot be undone.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm">
                        <i class="bx bx-trash me-1"></i> Delete
                    </button>
                </form>
            @endcanany
        @endif

        @can('purchase-order.print')
            <a class="btn btn-outline-secondary btn-sm" href="{{ route('purchase-orders.print', $purchaseOrder) }}" target="_blank">Print</a>
        @endcan
        <a class="btn btn-secondary btn-sm" href="{{ route('purchase-orders.index') }}">Back</a>
    </div>
</div>

@if (! empty($workflowError ?? null))
    <div class="alert alert-danger">
        <strong>Approval workflow could not start:</strong> {{ $workflowError }}
        <div class="mt-1 small">Configure a Purchase Order approval template for this category under Settings → Approval Templates.</div>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif
@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
<div id="po-payment-alert" class="d-none"></div>

<div class="card mb-4">
    <div class="card-header"><h5 class="mb-0">Purchase Order Details</h5></div>
    <div class="card-body row g-3">
        <div class="col-md-3">
            <small class="text-muted d-block">PO Number</small>
            <strong>{{ $purchaseOrder->po_no }}</strong>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">Purchase Date</small>
            <strong>{{ $purchaseOrder->purchase_date?->format('d M Y') ?? '--' }}</strong>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">Estimated Delivery</small>
            <strong>{{ $purchaseOrder->delivery_date?->format('d M Y') ?? '--' }}</strong>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">Status</small>
            <span class="badge bg-label-{{ $status?->badgeClass() ?? 'secondary' }}">{{ $status?->label() ?? $statusValue }}</span>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">Supplier</small>
            <strong>{{ $purchaseOrder->vendor->name ?? '--' }}</strong>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">PO Type</small>
            <strong>{{ $purchaseOrder->po_type ?? '--' }}</strong>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">GRN Store</small>
            <strong>{{ $purchaseOrder->grn_store ?? '--' }}</strong>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">Minimum Advance Payment</small>
            <strong>{{ number_format((float) ($purchaseOrder->minimum_advance_payment_percentage ?? 0), 2) }}%</strong>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">Department</small>
            <strong>{{ $purchaseOrder->department_name ?? $purchaseOrder->department->department_name ?? '--' }}</strong>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">Store</small>
            <strong>{{ $purchaseOrder->store->department_name ?? '--' }}</strong>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">Source</small>
            <strong>{{ $purchaseOrder->sourceEnum()?->label() ?? ($purchaseOrder->quotation_id ? 'From Quotation' : '--') }}</strong>
        </div>
        @if ($purchaseOrder->purchaseRequisition)
        <div class="col-md-3">
            <small class="text-muted d-block">Purchase Requisition</small>
            <strong>{{ $purchaseOrder->purchaseRequisition->pr_no }}</strong>
        </div>
        @endif
        <div class="col-md-3">
            <small class="text-muted d-block">Indent</small>
            <strong>{{ $purchaseOrder->indent->indent_no ?? ($purchaseOrder->purchaseRequisition->indent->indent_no ?? '--') }}</strong>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">Created By</small>
            <strong>{{ $purchaseOrder->createdBy->name ?? '--' }}</strong>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">Currency</small>
            <strong>{{ $purchaseOrder->currency ?? 'INR' }}</strong>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">Grand Total</small>
            <strong>{{ number_format((float) $purchaseOrder->grand_total, 2) }} {{ $purchaseOrder->currency }}</strong>
        </div>
        <div class="col-12">
            <small class="text-muted d-block">Notes</small>
            <div>{{ $purchaseOrder->notes ?: '--' }}</div>
        </div>
        @if ($purchaseOrder->remarks)
            <div class="col-12">
                <small class="text-muted d-block">Remarks</small>
                <div>{{ $purchaseOrder->remarks }}</div>
            </div>
        @endif
    </div>
</div>

{{-- PO Terms & Condition and Supplier Details --}}
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#viewCollapseTerms" aria-expanded="false" style="cursor: pointer;">
        <h5 class="mb-0"><i class="bx bx-file me-2"></i>PO Terms & Condition</h5>
        <i class="bx bx-chevron-down"></i>
    </div>
    <div id="viewCollapseTerms" class="collapse">
        <div class="card-body border-top">
            <p class="mb-0">{{ $purchaseOrder->payment_terms ?: 'No standard payment terms specified.' }}</p>
        </div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center" data-bs-toggle="collapse" data-bs-target="#viewCollapseSupplier" aria-expanded="false" style="cursor: pointer;">
        <h5 class="mb-0"><i class="bx bx-user-check me-2"></i>Supplier Details</h5>
        <i class="bx bx-chevron-down"></i>
    </div>
    <div id="viewCollapseSupplier" class="collapse">
        <div class="card-body border-top">
            <div class="row g-3">
                <div class="col-md-4">
                    <small class="text-muted d-block">Supplier Name</small>
                    <strong>{{ $purchaseOrder->vendor->name ?? '--' }}</strong>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block">Contact Phone / Email</small>
                    <strong>{{ $purchaseOrder->vendor->phone ?? ($purchaseOrder->vendor->email ?? '--') }}</strong>
                </div>
                <div class="col-md-4">
                    <small class="text-muted d-block">Supplier State & GST Rules</small>
                    @php
                        $vState = strtolower(trim($purchaseOrder->vendor->state ?? ''));
                        $cState = strtolower(trim(\App\Models\Company::query()->value('state') ?? ''));
                        $isIntra = (! $vState || ! $cState || $vState === $cState);
                    @endphp
                    <strong>{{ strtoupper($purchaseOrder->vendor->state ?? 'N/A') }}</strong>
                    <span class="badge bg-{{ $isIntra ? 'info' : 'warning' }} ms-1">{{ $isIntra ? 'Intra-State (CGST + SGST)' : 'Inter-State (IGST)' }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

@if (!empty($vendorQuotations) && $vendorQuotations->isNotEmpty())
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Vendor Quotations</h5>
        @if ($purchaseOrder->rfqNo())
            <span class="text-muted small">RFQ No: {{ $purchaseOrder->rfqNo() }}</span>
        @endif
    </div>
    <div class="table-responsive">
        <table class="table mb-0">
            <thead class="table-light">
                <tr>
                    <th>Vendor Name</th>
                    <th>Quoted Price</th>
                    <th>Negotiated Price</th>
                    <th>Effective Total</th>
                    <th>Delivery Days</th>
                    <th>Warranty</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($vendorQuotations as $quotation)
                    @php
                        $isSelected = (int) $quotation->id === (int) $purchaseOrder->quotation_id
                            || (int) $quotation->vendor_id === (int) $purchaseOrder->vendor_id
                            || $quotation->is_selected;
                    @endphp
                    <tr class="{{ $isSelected ? 'table-success' : '' }}">
                        <td><strong>{{ $quotation->vendor->name ?? '--' }}</strong></td>
                        <td>{{ number_format((float) $quotation->originalQuote(), 2) }} {{ $purchaseOrder->currency ?? '' }}</td>
                        <td>
                            @if ($quotation->hasNegotiatedPrice())
                                {{ number_format((float) $quotation->negotiated_total_cost, 2) }} {{ $purchaseOrder->currency ?? '' }}
                            @else
                                <span class="text-muted">No negotiation</span>
                            @endif
                        </td>
                        <td><strong>{{ number_format((float) $quotation->effectiveQuote(), 2) }} {{ $purchaseOrder->currency ?? '' }}</strong></td>
                        <td>{{ $quotation->delivery_days ? $quotation->delivery_days . ' days' : '--' }}</td>
                        <td>{{ $quotation->warranty ?: '--' }}</td>
                        <td>
                            @if ($isSelected)
                                <span class="badge bg-label-success">Selected for PO</span>
                            @else
                                <span class="badge bg-label-secondary">Not Selected</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif

<div class="card mb-4">
    <div class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="mb-0">Asset Details</h5>
    </div>
    <div class="card-body">
        @forelse ($purchaseOrder->items as $index => $item)
            <div class="border rounded mb-3 {{ $loop->last ? 'mb-0' : '' }}">
                <div class="bg-light border-bottom px-3 py-2 d-flex justify-content-between align-items-center">
                    <strong>Asset {{ $index + 1 }}: {{ $item->item_name }}</strong>
                    <button type="button" class="btn btn-sm btn-outline-info btn-view-history" data-item-name="{{ $item->item_name }}" data-item-code="{{ $item->item_code ?? '' }}">
                        <i class="bx bx-history me-1"></i> View Previous Item History
                    </button>
                </div>
                <div class="p-3">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <small class="text-muted d-block">Asset Name</small>
                            <strong>{{ $item->item_name ?: '--' }}</strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Asset Code</small>
                            <strong>{{ $item->item_code ?: '--' }}</strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">HSN Code</small>
                            <strong>{{ $item->hsn_code ?: '--' }}</strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Asset Type</small>
                            <strong>{{ $item->resolvedItemType()?->value ?? ($item->getRawOriginal('item_type') ?: '--') }}</strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Asset Category</small>
                            <strong>{{ $item->item_category_name ?: '--' }}</strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Asset Sub Category</small>
                            <strong>{{ $item->item_sub_category_name ?: '--' }}</strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Purchase Unit</small>
                            <strong>{{ $item->purchase_unit ?: ($item->unit ?: 'Nos') }}</strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Purchase Quantity</small>
                            <strong>{{ $item->quantity }}</strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Purchase Rate</small>
                            <strong>{{ number_format((float) $item->price, 2) }} {{ $purchaseOrder->currency ?? 'INR' }}</strong>
                        </div>
                        @if ($item->discount)
                        <div class="col-md-4">
                            <small class="text-muted d-block">Discount</small>
                            <strong>{{ number_format((float) $item->discount, 2) }}{{ $item->discount_type === 'percentage' ? '%' : '' }}</strong>
                        </div>
                        @endif
                        <div class="col-md-4">
                            <small class="text-muted d-block">Line Total</small>
                            <strong>{{ number_format((float) $item->total, 2) }} {{ $purchaseOrder->currency ?? 'INR' }}</strong>
                        </div>
                        <div class="col-12">
                            <small class="text-muted d-block">Remarks</small>
                            <div>{{ $item->remarks ?: '--' }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-muted mb-0">No assets on this purchase order.</p>
        @endforelse

        <div class="border-top pt-3 mt-3">
            <div class="row g-3">
                @if ((float) $purchaseOrder->tax > 0)
                <div class="col-md-4">
                    <small class="text-muted d-block">Tax</small>
                    <strong>{{ number_format((float) $purchaseOrder->tax, 2) }} {{ $purchaseOrder->currency ?? 'INR' }}</strong>
                </div>
                @endif
                @if ((float) $purchaseOrder->shipping > 0)
                <div class="col-md-4">
                    <small class="text-muted d-block">Shipping</small>
                    <strong>{{ number_format((float) $purchaseOrder->shipping, 2) }} {{ $purchaseOrder->currency ?? 'INR' }}</strong>
                </div>
                @endif
                @if ((float) $purchaseOrder->discount > 0)
                <div class="col-md-4">
                    <small class="text-muted d-block">Order Discount</small>
                    <strong>{{ number_format((float) $purchaseOrder->discount, 2) }}{{ $purchaseOrder->discount_type === 'percentage' ? '%' : '' }}</strong>
                </div>
                @endif
                <div class="col-md-4">
                    <small class="text-muted d-block">Grand Total</small>
                    <strong class="fs-5">{{ number_format((float) $purchaseOrder->grand_total, 2) }} {{ $purchaseOrder->currency ?? 'INR' }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Purchase Order Items Table (14 Columns - Client Reference) --}}
@php
    $vState = strtolower(trim($purchaseOrder->vendor->state ?? ''));
    $cState = strtolower(trim(\App\Models\Company::query()->value('state') ?? ''));
    $intraState = (! $vState || ! $cState || $vState === $cState);
    $totQty = 0; $totSub = 0; $totDisc = 0; $totTaxable = 0;
    $totCgst = 0; $totSgst = 0; $totIgst = 0; $totGrand = 0;
@endphp
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="bx bx-table me-2"></i>Purchase Order Items & Tax Summary Table</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-striped mb-0 text-nowrap">
                <thead class="table-light">
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
                            <th>Central Tax (CGST) Amount</th>
                            <th>State Tax (SGST) Amount</th>
                        @else
                            <th>Integrated Tax (IGST) Amount</th>
                        @endif
                        <th>EPR</th>
                        <th>MRP</th>
                        <th>Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($purchaseOrder->items as $idx => $item)
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
                            <td>{{ $idx + 1 }}</td>
                            <td><strong>{{ $item->item_name }}</strong></td>
                            <td>₹{{ number_format($price, 2) }}</td>
                            <td>₹{{ number_format($disc, 2) }}</td>
                            <td>{{ $item->hsn_code ?: '--' }}</td>
                            <td>{{ $item->purchase_unit ?: ($item->unit ?: 'Nos') }}</td>
                            <td>{{ ((float) ($item->gst_percent ?: 18)) }}%</td>
                            <td>{{ $qty }}</td>
                            <td>₹{{ number_format($rateAfterDisc, 2) }}</td>
                            <td>₹{{ number_format($gstAmt, 2) }}</td>
                            @if ($intraState)
                                <td>₹{{ number_format($cgstAmt, 2) }}</td>
                                <td>₹{{ number_format($sgstAmt, 2) }}</td>
                            @else
                                <td>₹{{ number_format($igstAmt, 2) }}</td>
                            @endif
                            <td>₹{{ number_format((float) ($item->epr ?? 0), 2) }}</td>
                            <td>₹{{ number_format((float) ($item->mrp ?? 0), 2) }}</td>
                            <td class="fw-bold text-primary">₹{{ number_format($lineTot, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="16" class="text-center text-muted py-3">No items found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-light">
        <div class="row justify-content-end">
            <div class="col-md-6 col-lg-4">
                <table class="table table-borderless table-sm mb-0">
                    <tbody>
                        <tr><th class="text-end">Total Quantity:</th><td class="text-end fw-bold">{{ $totQty }}</td></tr>
                        <tr><th class="text-end">Sub-total:</th><td class="text-end fw-bold">₹{{ number_format($totSub, 2) }}</td></tr>
                        <tr><th class="text-end">Total Discount:</th><td class="text-end text-success fw-bold">-₹{{ number_format($totDisc, 2) }}</td></tr>
                        <tr><th class="text-end">Taxable Amount:</th><td class="text-end fw-bold">₹{{ number_format($totTaxable, 2) }}</td></tr>
                        @if ($intraState)
                            <tr><th class="text-end">Total CGST:</th><td class="text-end fw-bold">₹{{ number_format($totCgst, 2) }}</td></tr>
                            <tr><th class="text-end">Total SGST:</th><td class="text-end fw-bold">₹{{ number_format($totSgst, 2) }}</td></tr>
                        @else
                            <tr><th class="text-end">Total IGST:</th><td class="text-end fw-bold">₹{{ number_format($totIgst, 2) }}</td></tr>
                        @endif
                        <tr class="border-top"><th class="text-end fs-5">Grand Total:</th><td class="text-end fs-5 fw-bold text-primary">₹{{ number_format($purchaseOrder->grand_total ?: $totGrand, 2) }}</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@php
    $summary = $paymentSummary ?? [
        'total' => $purchaseOrder->orderTotalAmount(),
        'paid' => $purchaseOrder->paidAmount(),
        'remaining' => $purchaseOrder->remainingAmount(),
        'status' => $purchaseOrder->paymentStatusEnum()->value,
        'status_badge' => $purchaseOrder->paymentStatusEnum()->badgeClass(),
        'currency' => $purchaseOrder->currency ?: 'INR',
    ];
@endphp

{{-- Payment Summary --}}
<div class="card mb-4">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Payment Summary</h5>
        <span id="summary-payment-status" class="badge bg-label-{{ $summary['status_badge'] }}">{{ $summary['status'] }}</span>
    </div>
    <div class="card-body row g-3">
        <div class="col-md-4">
            <small class="text-muted d-block">Total Purchase Order Amount</small>
            <strong class="fs-5">₹<span id="summary-total">{{ number_format($summary['total'], 2) }}</span></strong>
        </div>
        <div class="col-md-4">
            <small class="text-muted d-block">Total Paid Amount</small>
            <strong class="fs-5 text-success">₹<span id="summary-paid">{{ number_format($summary['paid'], 2) }}</span></strong>
        </div>
        <div class="col-md-4">
            <small class="text-muted d-block">Remaining Balance</small>
            <strong class="fs-5 text-danger">₹<span id="summary-remaining">{{ number_format($summary['remaining'], 2) }}</span></strong>
        </div>
    </div>
</div>

{{-- Payment History (read-only) --}}
<div class="card mb-4">
    <div class="card-header"><h5 class="mb-0">Payment History</h5></div>
    <div class="table-responsive">
        <table class="table mb-0 align-middle">
            <thead>
                <tr>
                    <th>Payment Date</th>
                    <th>Payment Amount</th>
                    <th>Payment Method</th>
                    <th>Payment Proof</th>
                    <th>Created By</th>
                    <th>Created At</th>
                </tr>
            </thead>
            <tbody id="po-payment-history-body">
                @forelse ($purchaseOrder->payments as $payment)
                    <tr>
                        <td>{{ $payment->payment_date?->format('d M Y') }}</td>
                        <td>{{ number_format((float) $payment->payment_amount, 2) }}</td>
                        <td>{{ $payment->payment_method }}</td>
                        <td>
                            @if ($payment->hasAttachment())
                                <a class="btn btn-sm btn-outline-primary me-1"
                                   href="{{ route('purchase-orders.payments.attachment', [$payment, 'view']) }}"
                                   target="_blank">View</a>
                                <a class="btn btn-sm btn-outline-secondary"
                                   href="{{ route('purchase-orders.payments.attachment', [$payment, 'download']) }}">Download</a>
                            @else
                                <span class="text-muted">No Attachment</span>
                            @endif
                        </td>
                        <td>{{ $payment->createdBy->name ?? '--' }}</td>
                        <td>{{ $payment->created_at?->format('d M Y h:i A') }}</td>
                    </tr>
                @empty
                    <tr id="po-payment-history-empty">
                        <td colspan="6" class="text-center text-muted py-4">No payments recorded yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if ($purchaseOrder->grns->isNotEmpty())
<div class="card mb-4">
    <div class="card-header"><h5 class="mb-0">Linked GRNs</h5></div>
    <div class="card-body">
        @foreach ($purchaseOrder->grns as $grn)
            <a href="{{ route('grns.show', $grn) }}" class="badge bg-label-success me-1">{{ $grn->grn_no }}</a>
        @endforeach
    </div>
</div>
@endif

{{-- Approval Timeline --}}
@if (! empty($timeline))
<div class="card mb-4">
    <div class="card-header"><h5 class="mb-0">Approval Timeline</h5></div>
    <div class="card-body p-0">
        <ul class="list-group list-group-flush">
            @foreach ($timeline as $row)
            @php
                $icon = match ($row['state']) {
                    'done'      => 'ti ti-circle-check text-success',
                    'current'   => 'ti ti-clock text-warning',
                    'rejected'  => 'ti ti-circle-x text-danger',
                    'sent_back' => 'ti ti-arrow-back-up text-secondary',
                    default     => 'ti ti-circle text-muted',
                };
            @endphp
            <li class="list-group-item d-flex align-items-start gap-3 py-3">
                <span class="{{ $icon }} fs-5 mt-1"></span>
                <div class="flex-grow-1">
                    <div class="fw-semibold">
                        {{ $row['label'] }}
                        @if ($row['state'] === 'current')
                            <span class="badge bg-warning ms-1">Awaiting</span>
                        @endif
                    </div>
                    @if ($row['actor'])
                        <small class="text-muted">{{ $row['actor'] }}</small>
                    @endif
                    @if ($row['remarks'])
                        <div><small class="text-secondary fst-italic">"{{ $row['remarks'] }}"</small></div>
                    @endif
                    @if ($row['acted_at'])
                        <small class="text-muted">{{ \Carbon\Carbon::parse($row['acted_at'])->format('d M Y h:i A') }}</small>
                    @endif
                </div>
            </li>
            @endforeach
        </ul>
    </div>
</div>
@endif

{{-- Submit Modal --}}
<div class="modal fade" id="submitPoModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('purchase-orders.submit', $purchaseOrder) }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Submit for Approval</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Remarks (optional)</label>
                        <textarea name="remarks" class="form-control" rows="3" placeholder="Add a note..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning">Submit for Approval</button>
                </div>
            </div>
        </form>
    </div>
</div>

@if ($canActOnCurrentStep ?? false)
{{-- Approve Modal --}}
<div class="modal fade" id="approvePoModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('purchase-orders.approve', $purchaseOrder) }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title">Approve Purchase Order</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <p class="mb-0">Are you sure you want to approve this purchase order?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">Approve</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Reject Modal --}}
<div class="modal fade" id="rejectPoModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('purchase-orders.reject', $purchaseOrder) }}">
            @csrf
            <div class="modal-content">
                <div class="modal-header"><h5 class="modal-title text-danger">Reject Purchase Order</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Remarks <span class="text-danger">*</span></label>
                        <textarea name="remarks" class="form-control" rows="3" required placeholder="Enter mandatory remarks for rejection..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject</button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@include('purchase-orders._payment-modal', ['paymentMethods' => $paymentMethods ?? \App\Models\PurchaseOrderPayment::METHODS])
@include('procurement._item-history-modal')
@endsection

@section('page-script')
@include('purchase-orders._payment-scripts')
@endsection
