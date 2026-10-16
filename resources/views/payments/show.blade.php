@extends('layouts/contentNavbarLayout')

@section('title', 'Payment Request: ' . $paymentRequest->payment_request_no)

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h4 class="mb-0 d-flex align-items-center gap-2">
            {{ $paymentRequest->payment_request_no }}
            <span class="badge {{ $paymentRequest->statusBadgeClass() }} fs-6">{{ $paymentRequest->status }}</span>
        </h4>
    </div>
    <div class="d-flex align-items-center gap-2">
        @if ($purchaseOrder)
            <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="btn btn-outline-primary btn-sm">
                <i class="bx bx-cart me-1"></i> View Purchase Order ({{ $purchaseOrder->po_no }})
            </a>

        @endif

        @if ($canManagePayment)
            @if ($paymentRequest->isPending())
                <button type="button" class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#approveModal">
                    <i class="bx bx-check me-1"></i> Approve
                </button>
                <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#rejectModal">
                    <i class="bx bx-x me-1"></i> Reject
                </button>
            @elseif ($paymentRequest->isApproved() || ($paymentRequest->isPartiallyPaid() && ($purchaseOrder ? $purchaseOrder->remainingAmount() > 0 : true)))
                <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#recordPaymentModal">
                    <i class="bx bx-credit-card me-1"></i> Make Payment
                </button>
            @endif
        @endif

        <a href="{{ route('payments.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i> Back to Payments
        </a>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bx bx-error me-1"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bx bx-error me-1"></i>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row g-4">
    <div class="col-md-7">
        <div class="card h-100">
            <div class="card-header border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0">Payment Request Details</h5>
                <span class="badge {{ $paymentRequest->statusBadgeClass() }}">{{ $paymentRequest->status }}</span>
            </div>
            <div class="card-body mt-3">
                <div class="row mb-3">
                    <div class="col-sm-5 text-muted">Payment Request No.</div>
                    <div class="col-sm-7 fw-bold">{{ $paymentRequest->payment_request_no }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-5 text-muted">Requested By</div>
                    <div class="col-sm-7 fw-semibold">{{ $paymentRequest->requestedBy->name ?? 'System' }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-5 text-muted">Request Date</div>
                    <div class="col-sm-7">{{ $paymentRequest->request_date->format('d M Y, h:i A') }}</div>
                </div>
                <div class="row mb-3">
                    <div class="col-sm-5 text-muted">Amount Requested</div>
                    <div class="col-sm-7 fw-bold text-primary fs-5">
                        {{ $purchaseOrder?->currency ?? 'INR' }} {{ number_format($paymentRequest->amount, 2) }}
                    </div>
                </div>

                <hr class="my-4">
                <h6 class="text-muted text-uppercase mb-3">Purchase Order Summary</h6>

                @if ($purchaseOrder)
                    @php
                        $minAdvPct = (float) $purchaseOrder->minimum_advance_payment_percentage;
                        $minAdvAmt = ($purchaseOrder->grand_total * $minAdvPct) / 100;
                    @endphp
                    <div class="row mb-2">
                        <div class="col-sm-5 text-muted">PO Amount</div>
                        <div class="col-sm-7 fw-semibold">{{ $purchaseOrder->currency ?? 'INR' }} {{ number_format($purchaseOrder->grand_total, 2) }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-5 text-muted">Minimum Advance (%)</div>
                        <div class="col-sm-7">{{ number_format($minAdvPct, 2) }}%</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-5 text-muted">Minimum Advance Amount</div>
                        <div class="col-sm-7">{{ $purchaseOrder->currency ?? 'INR' }} {{ number_format($minAdvAmt, 2) }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-5 text-muted">Total Paid</div>
                        <div class="col-sm-7 text-success fw-semibold">{{ $purchaseOrder->currency ?? 'INR' }} {{ number_format($purchaseOrder->paidAmount(), 2) }}</div>
                    </div>
                    <div class="row mb-2">
                        <div class="col-sm-5 text-muted">Remaining Balance</div>
                        <div class="col-sm-7 text-danger fw-semibold">{{ $purchaseOrder->currency ?? 'INR' }} {{ number_format($purchaseOrder->remainingAmount(), 2) }}</div>
                    </div>
                @else
                    <div class="alert alert-warning mb-0">No Purchase Order details available.</div>
                @endif

                <hr class="my-4">
                <h6 class="text-muted text-uppercase mb-3">Execution & Status Info</h6>

                @if ($paymentRequest->approvedBy)
                    <div class="row mb-3">
                        <div class="col-sm-5 text-muted">Approved By</div>
                        <div class="col-sm-7">{{ $paymentRequest->approvedBy->name }}</div>
                    </div>
                @endif

                @if ($paymentRequest->rejectedBy)
                    <div class="row mb-3">
                        <div class="col-sm-5 text-muted">Rejected By</div>
                        <div class="col-sm-7 text-danger">{{ $paymentRequest->rejectedBy->name }}</div>
                    </div>
                @endif

                @if ($paymentRequest->paidBy)
                    <div class="row mb-3">
                        <div class="col-sm-5 text-muted">Paid & Recorded By</div>
                        <div class="col-sm-7 text-success fw-bold">{{ $paymentRequest->paidBy->name }}</div>
                    </div>
                @endif

                @if ($paymentRequest->payment_method)
                    <div class="row mb-3">
                        <div class="col-sm-5 text-muted">Payment Method</div>
                        <div class="col-sm-7 fw-semibold">{{ $paymentRequest->payment_method }}</div>
                    </div>
                @endif

                @if ($paymentRequest->payment_date)
                    <div class="row mb-3">
                        <div class="col-sm-5 text-muted">Payment Date</div>
                        <div class="col-sm-7">{{ $paymentRequest->payment_date->format('d M Y') }}</div>
                    </div>
                @endif

                @if ($paymentRequest->reference_number)
                    <div class="row mb-3">
                        <div class="col-sm-5 text-muted">Reference / Transaction No.</div>
                        <div class="col-sm-7 font-monospace text-dark bg-light p-1 rounded d-inline-block">{{ $paymentRequest->reference_number }}</div>
                    </div>
                @endif

                @if ($paymentRequest->remarks)
                    <div class="row mb-3">
                        <div class="col-sm-5 text-muted">Remarks / Notes</div>
                        <div class="col-sm-7 bg-light p-2 rounded">
                            <span class="d-block" style="white-space: pre-wrap;">{{ $paymentRequest->remarks }}</span>
                        </div>
                    </div>
                @endif

                @if ($paymentRequest->hasAttachment())
                    <div class="row mb-3">
                        <div class="col-sm-5 text-muted">Payment Proof / Receipt</div>
                        <div class="col-sm-7">
                            <a href="{{ $paymentRequest->attachmentUrl() }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                                <i class="bx bx-paperclip me-1"></i> View Attachment
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-5">
        <div class="card h-100">
            <div class="card-header border-bottom">
                <h5 class="mb-0">Linked Purchase Order Details</h5>
            </div>
            <div class="card-body mt-3">
                @if ($purchaseOrder)
                    <div class="mb-3">
                        <small class="text-muted d-block uppercase">Purchase Order Number</small>
                        <a href="{{ route('purchase-orders.show', $purchaseOrder) }}" class="fs-5 fw-bold text-primary">
                            {{ $purchaseOrder->po_no }} <i class="bx bx-link-external fs-6"></i>
                        </a>

                    </div>

                    <div class="mb-3">
                        <small class="text-muted d-block uppercase">Vendor Name</small>
                        <span class="fs-6 fw-bold">{{ $purchaseOrder->vendor->name ?? 'N/A' }}</span>
                        @if($purchaseOrder->vendor?->email)
                            <span class="d-block small text-muted">{{ $purchaseOrder->vendor->email }}</span>
                        @endif
                    </div>

                    <div class="mb-3">
                        <small class="text-muted d-block uppercase">Purchase Date</small>
                        <span>{{ $purchaseOrder->created_at->format('d M Y') }}</span>
                    </div>

                    <div class="mb-3">
                        <small class="text-muted d-block uppercase">PO Status</small>
                        <span class="badge bg-label-info">{{ $purchaseOrder->statusEnum()?->label() ?? $purchaseOrder->status }}</span>
                    </div>

                    <div class="p-3 bg-light rounded mt-4">
                        @if ($purchaseOrder->negotiated_total || ($purchaseOrder->quotation && $purchaseOrder->quotation->hasNegotiatedPrice()))
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Negotiated Value:</span>
                            <span class="fw-bold text-primary">{{ $purchaseOrder->currency ?? 'INR' }} {{ number_format($purchaseOrder->negotiated_total ?? $purchaseOrder->quotation->effectiveQuote(), 2) }}</span>
                        </div>
                        @endif
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Total Order Amount:</span>
                            <span class="fw-bold">{{ $purchaseOrder->currency ?? 'INR' }} {{ number_format($purchaseOrder->orderTotalAmount(), 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Paid Amount:</span>
                            <span class="fw-bold text-success">{{ $purchaseOrder->currency ?? 'INR' }} {{ number_format($purchaseOrder->paidAmount(), 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between border-top pt-2">
                            <span class="text-muted fw-semibold">Remaining Balance:</span>
                            <span class="fw-bold text-danger">{{ $purchaseOrder->currency ?? 'INR' }} {{ number_format($purchaseOrder->remainingAmount(), 2) }}</span>
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning">Linked Purchase Order not found or archived.</div>
                @endif
            </div>
        </div>
    </div>
</div>

@if ($canManagePayment)
    @if ($paymentRequest->isPending())
        <!-- Approve Modal -->
        <div class="modal fade" id="approveModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <form action="{{ route('payments.approve', $paymentRequest) }}" method="POST" class="modal-content">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Approve Payment Request</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to approve payment request <strong>{{ $paymentRequest->payment_request_no }}</strong> for <strong>{{ $purchaseOrder?->currency ?? 'INR' }} {{ number_format($paymentRequest->amount, 2) }}</strong>?</p>
                        <div class="mb-3">
                            <label class="form-label">Approval Remarks (Optional)</label>
                            <textarea name="remarks" class="form-control" rows="3" placeholder="Add optional instructions or approval notes..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success"><i class="bx bx-check me-1"></i> Confirm Approval</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Reject Modal -->
        <div class="modal fade" id="rejectModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <form action="{{ route('payments.reject', $paymentRequest) }}" method="POST" class="modal-content">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title text-danger">Reject Payment Request</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>You are about to reject payment request <strong>{{ $paymentRequest->payment_request_no }}</strong>. Please provide the reason below:</p>
                        <div class="mb-3">
                            <label class="form-label">Rejection Remarks <span class="text-danger">*</span></label>
                            <textarea name="remarks" class="form-control" rows="3" placeholder="Enter mandatory reason for rejection..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger"><i class="bx bx-x me-1"></i> Submit Rejection</button>
                    </div>
                </form>
            </div>
        </div>
    @elseif ($paymentRequest->isApproved() || ($paymentRequest->isPartiallyPaid() && ($purchaseOrder ? $purchaseOrder->remainingAmount() > 0 : true)))
        <!-- Record Payment Modal -->
        <div class="modal fade" id="recordPaymentModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <form action="{{ route('payments.pay', $paymentRequest) }}" method="POST" enctype="multipart/form-data" class="modal-content">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Make Payment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    @php
                        $totalAmount = $purchaseOrder ? $purchaseOrder->orderTotalAmount() : $paymentRequest->amount;
                        $alreadyPaid = $purchaseOrder ? $purchaseOrder->paidAmount() : 0;
                        $remaining = max(0, round($totalAmount - $alreadyPaid, 2));
                        
                        $minAmount = 0.01;
                        if ($purchaseOrder && $purchaseOrder->payments()->count() === 0 && (float)$purchaseOrder->minimum_advance_payment_percentage > 0) {
                            $minAmount = round(($purchaseOrder->grand_total * (float)$purchaseOrder->minimum_advance_payment_percentage) / 100, 2);
                        }

                        if ($remaining <= 0 && $paymentRequest->amount > 0 && !$purchaseOrder) {
                            $remaining = $paymentRequest->amount;
                        }
                        $defaultPay = max($minAmount, min($remaining, $paymentRequest->amount));
                    @endphp
                    <div class="modal-body">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label text-muted">Purchase Order Number</label>
                                <input type="text" class="form-control" value="{{ $purchaseOrder?->po_no ?? $paymentRequest->payment_request_no }}" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-muted">Vendor</label>
                                <input type="text" class="form-control" value="{{ $purchaseOrder?->vendor->name ?? '--' }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted">Total Amount</label>
                                <input type="text" class="form-control" value="{{ number_format((float) $totalAmount, 2) }}" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Total Paid <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" min="{{ $minAmount }}" max="{{ $remaining }}" name="payment_amount" id="req_paid"
                                       class="form-control" required placeholder="Enter paid amount (max {{ number_format((float) $remaining, 2) }})" value="{{ old('payment_amount', $defaultPay) }}">
                                @if($minAmount > 0.01)
                                    <small class="text-danger d-block">Min. Advance Required: ₹{{ number_format($minAmount, 2) }}</small>
                                @endif
                                <small class="text-muted">Already paid earlier: ₹{{ number_format((float) $alreadyPaid, 2) }}</small>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-muted">Remaining Balance</label>
                                <input type="text" class="form-control fw-semibold" id="req_remaining" value="{{ number_format((float) max(0, $remaining - $defaultPay), 2) }}" readonly>
                            </div>
                        </div>

                        <hr>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                                <input type="date" name="payment_date" class="form-control" value="{{ old('payment_date', now()->toDateString()) }}" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                                <select name="payment_method" class="form-select" required>
                                    <option value="">Select method</option>
                                    @foreach ($paymentMethods as $method)
                                        <option value="{{ $method }}" {{ old('payment_method') === $method ? 'selected' : '' }}>
                                            {{ $method }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Payment Proof</label>
                                <input type="file" name="attachment" class="form-control"
                                       accept=".pdf,.xls,.xlsx,.doc,.docx,.jpg,.jpeg,.png,.csv,.txt,.zip">
                                <small class="text-muted">PDF, Excel, Word, Image, CSV, TXT, ZIP (max 10 MB)</small>
                            </div>
                        </div>
                        <script>
                            document.addEventListener('DOMContentLoaded', function() {
                                const paidInput = document.getElementById('req_paid');
                                const remainingInput = document.getElementById('req_remaining');
                                const maxRem = {{ (float) $remaining }};
                                function updateRem() {
                                    if (!paidInput || !remainingInput) return;
                                    const val = parseFloat(paidInput.value) || 0;
                                    const rem = Math.max(0, maxRem - val);
                                    remainingInput.value = Number(rem).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                }
                                if (paidInput) {
                                    paidInput.addEventListener('input', updateRem);
                                    updateRem();
                                }
                            });
                        </script>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Payment</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endif
@endsection
