{{-- Shared Make Payment Modal --}}
<div class="modal fade" id="makePaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form id="po-payment-form" class="modal-content" enctype="multipart/form-data" novalidate>
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Make Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="po-payment-form-errors" class="alert alert-danger d-none"></div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label text-muted">Purchase Order Number</label>
                        <input type="text" class="form-control" id="pay_po_no" readonly>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label text-muted">Vendor</label>
                        <input type="text" class="form-control" id="pay_vendor" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted">Total Amount</label>
                        <input type="text" class="form-control" id="pay_total" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Total Paid <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0.01" name="payment_amount" id="pay_paid"
                               class="form-control" required placeholder="Enter paid amount" value="0">
                        <small class="text-muted" id="pay_already_paid_hint"></small>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted">Remaining Balance</label>
                        <input type="text" class="form-control fw-semibold" id="pay_remaining" readonly>
                    </div>
                </div>

                <hr>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" id="payment_date" class="form-control" value="{{ now()->toDateString() }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                        <select name="payment_method" id="payment_method" class="form-select" required>
                            <option value="">Select method</option>
                            @foreach ($paymentMethods as $method)
                                <option value="{{ $method }}">{{ $method }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label">Payment Proof</label>
                        <input type="file" name="attachment" id="payment_attachment" class="form-control"
                               accept=".pdf,.xls,.xlsx,.doc,.docx,.jpg,.jpeg,.png,.csv,.txt,.zip">
                        <small class="text-muted">PDF, Excel, Word, Image, CSV, TXT, ZIP (max 10 MB)</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary" id="po-payment-submit-btn">
                    <span class="btn-text">Save Payment</span>
                    <span class="spinner-border spinner-border-sm d-none" role="status"></span>
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Generate Payment Request Interactive Modal --}}
<div class="modal fade" id="generatePaymentRequestModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bx bx-credit-card me-2"></i>Generate Payment Request</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="gen-pay-req-error" class="alert alert-danger d-none"></div>
                
                <div class="mb-3">
                    <label class="form-label text-muted">Purchase Order Number</label>
                    <input type="text" id="gen_req_po_no" class="form-control fw-bold" readonly>
                </div>
                <div class="row g-3 mb-3">
                    <div class="col-md-4">
                        <label class="form-label text-muted">PO Grand Total</label>
                        <input type="text" id="gen_req_total_display" class="form-control" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Mandatory Min. Advance <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0" max="100" id="gen_req_percent" class="form-control text-primary fw-bold" placeholder="0.00">
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Request Amount (₹) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" min="0" id="gen_req_amount" class="form-control text-primary fw-bold" placeholder="0.00">
                    </div>
                    <div class="col-12">
                        <small class="text-muted d-block" id="gen_req_hint">Enter either percentage or amount; the other will calculate automatically.</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btn-submit-pay-req-modal">
                    <i class="bx bx-check me-1"></i> Confirm Request
                </button>
            </div>
        </div>
    </div>
</div>
