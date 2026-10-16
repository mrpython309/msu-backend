@extends('layouts/contentNavbarLayout')

@section('title', 'Vendor Comparison')

@section('content')

<div id="compare-alert" class="alert d-none alert-dismissible" role="alert"></div>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">Vendor Quotation Comparison</h4>
    </div>
    <a href="{{ route('rfqs.show', $rfq) }}" class="btn btn-secondary btn-sm">Back to RFQ</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table mb-0 align-middle" id="vendor-compare-table">
            <thead>
                <tr>
                    <th>Vendor</th>
                    <th class="text-end">Original Quote</th>
                    <th class="text-end">Negotiated Quote</th>
                    <th class="text-end">Savings</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="vendor-compare-body">
                @forelse ($quotations as $quotation)
                    @php
                        $effective = $quotation->effectiveQuote();
                        $isLowest = $lowestEffective !== null && abs($effective - (float) $lowestEffective) < 0.005;
                        $negotiatedDisplay = $quotation->hasNegotiatedPrice()
                            ? (float) $quotation->negotiated_total_cost
                            : $quotation->originalQuote();
                    @endphp
                    <tr
                        data-quotation-id="{{ $quotation->id }}"
                        class="{{ $quotation->is_selected ? 'table-success' : ($isLowest ? 'table-warning' : '') }}"
                    >
                        <td>
                            <strong>{{ $quotation->vendor->name ?? '--' }}</strong>
                            @if ($isLowest)
                                <span class="badge bg-label-warning ms-1">Lowest</span>
                            @endif
                            <div class="small text-muted">{{ $quotation->contact_person }}</div>
                        </td>
                        <td class="text-end">{{ number_format($quotation->originalQuote(), 2) }}</td>
                        <td class="text-end">
                            <strong>{{ number_format($negotiatedDisplay, 2) }}</strong>
                            @if (! $quotation->hasNegotiatedPrice())
                                <div class="small text-muted">Same as original</div>
                            @endif
                        </td>
                        <td class="text-end">{{ number_format($quotation->savingsAmount(), 2) }}</td>
                        <td>
                            <div class="d-flex flex-wrap gap-1">
                                @if (! $quotations->contains('is_selected', true))
                                    @can('quotation.negotiate')
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-primary btn-negotiate"
                                            data-quotation-id="{{ $quotation->id }}"
                                            data-url="{{ route('vendor-quotations.negotiation.show', $quotation) }}"
                                            data-save-url="{{ route('vendor-quotations.negotiation.save', $quotation) }}"
                                            @disabled($quotation->isNegotiationLocked())
                                            title="{{ $quotation->isNegotiationLocked() ? 'Locked after vendor selection or PO creation' : 'Negotiate with vendor' }}"
                                        >
                                            Negotiate
                                        </button>
                                    @endcan
                                @endif
                                @canany(['quotation.select-vendor', 'quotations:select-vendor', 'quotations.select-vendor', 'quotation:select-vendor', 'vendor-quotations.select', 'vendor-quotations:select', 'select-vendor', 'select vendor', 'quotation.select', 'quotations:select', 'rfqs:select-vendor', 'rfq.select-vendor'])
                                    @if (! $quotation->is_selected)
                                        @if (! $quotations->contains('is_selected', true))
                                            <form method="POST" action="{{ route('vendor-quotations.select', $quotation) }}" class="d-inline">
                                                @csrf
                                                <button class="btn btn-sm btn-primary" type="submit">Select Vendor</button>
                                            </form>
                                        @endif
                                    @elseif ($quotation->is_selected)
                                        <span class="badge bg-label-success align-self-center">Ready for PO</span>
                                    @endif
                                @endcanany
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr class="compare-empty">
                        <td colspan="5" class="text-center text-muted py-4">No quotations received yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Negotiation Modal --}}
<div class="modal fade" id="negotiateModal" tabindex="-1" aria-labelledby="negotiateModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="negotiateModalLabel">Vendor Negotiation</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="negotiate-form-errors" class="alert alert-danger d-none"></div>
                <div id="negotiate-locked-banner" class="alert alert-warning d-none">
                    Negotiations are locked because a purchase order has been generated.
                </div>

                <form id="negotiate-form">
                    @csrf
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">RFQ Number</label>
                            <input type="text" class="form-control" id="neg_rfq_no" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Vendor Name</label>
                            <input type="text" class="form-control" id="neg_vendor" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Original Quotation</label>
                            <input type="text" class="form-control" id="neg_original" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Current Negotiated Price</label>
                            <input type="text" class="form-control" id="neg_current" readonly>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Net Negotiated Price <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="0" class="form-control bg-light" id="neg_new_price" name="negotiated_total_cost" readonly required title="Auto-calculated from item-wise rates below">
                        </div>
                        <div class="col-12 mt-3 mb-2" id="neg_items_container">
                            <label class="form-label d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-primary">Item-wise Negotiation Rates</span>
                                <small class="text-muted">Entering rate per item auto-calculates total price</small>
                            </label>
                            <div class="table-responsive border rounded">
                                <table class="table table-sm table-bordered mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Item Description</th>
                                            <th class="text-center" style="width: 110px;">Qty & Unit</th>
                                            <th class="text-end" style="width: 120px;">Original Rate</th>
                                            <th class="text-end" style="width: 120px;">Original Total</th>
                                            <th style="width: 160px;">Negotiated Rate <span class="text-danger">*</span></th>
                                            <th class="text-end" style="width: 130px;">Negotiated Total</th>
                                        </tr>
                                    </thead>
                                    <tbody id="negotiate-items-body">
                                        <tr><td colspan="6" class="text-center text-muted py-2">Loading items...</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Buyer Remarks</label>
                            <textarea class="form-control" id="neg_buyer_remarks" name="buyer_remarks" rows="2" maxlength="2000"></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Vendor Response</label>
                            <textarea class="form-control" id="neg_vendor_response" name="vendor_response" rows="2" maxlength="2000"></textarea>
                        </div>
                    </div>

                    <h6 class="mb-2">Negotiation History</h6>
                    <div class="table-responsive border rounded">
                        <table class="table table-sm mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Round</th>
                                    <th class="text-end">Old Price</th>
                                    <th class="text-end">New Price</th>
                                    <th>Buyer Remarks</th>
                                    <th>Vendor Response</th>
                                    <th>Negotiated By</th>
                                    <th>Negotiated Date</th>
                                </tr>
                            </thead>
                            <tbody id="negotiate-history-body">
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-3">No negotiation rounds yet.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </form>
            </div>
            <div class="modal-footer sticky-bottom bg-white border-top">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="negotiate-form" class="btn btn-primary" id="negotiate-save-btn">
                    <span class="btn-text">Save Negotiation</span>
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection
@section('page-script')
@php
    $compareUser = auth()->user();
    $canSelectVendor = false;
    $canNegotiateVendor = false;
    if ($compareUser) {
        try {
            try { app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions(); } catch (\Throwable) {}
            $canSelectVendor = $compareUser->hasAnyPermission([
                'quotation.select-vendor', 'quotations:select-vendor', 'quotations.select-vendor',
                'quotation:select-vendor', 'vendor-quotations.select', 'vendor-quotations:select',
                'select-vendor', 'select vendor', 'quotation.select', 'quotations:select',
                'rfqs:select-vendor', 'rfq.select-vendor',
            ]) || $compareUser->hasAnyRole(['superadmin', 'Super Admin', 'Purchase Officer', 'Vendor Selector']);
        } catch (\Throwable) {
            $canSelectVendor = $compareUser->can('quotation.select-vendor') || $compareUser->can('quotations:select-vendor') || $compareUser->can('select-vendor') || $compareUser->can('select vendor');
        }

        try {
            $canNegotiateVendor = $compareUser->can('quotation.negotiate')
                || $compareUser->hasAnyRole(['superadmin', 'Super Admin']);
        } catch (\Throwable) {
            $canNegotiateVendor = $compareUser->can('quotation.negotiate');
        }
    }
@endphp
<script>
document.addEventListener('DOMContentLoaded', function () {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const modalEl = document.getElementById('negotiateModal');
    const form = document.getElementById('negotiate-form');
    const errorBox = document.getElementById('negotiate-form-errors');
    const lockedBanner = document.getElementById('negotiate-locked-banner');
    const saveBtn = document.getElementById('negotiate-save-btn');
    const historyBody = document.getElementById('negotiate-history-body');
    const tbody = document.getElementById('vendor-compare-body');
    const alertBox = document.getElementById('compare-alert');
    const canSelect = @json($canSelectVendor);
    const canNegotiate = @json($canNegotiateVendor);

    let saveUrl = null;
    let submitting = false;
    const modal = modalEl ? new bootstrap.Modal(modalEl) : null;

    function money(n) {
        return Number(n || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function esc(v) {
        return String(v ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }

    function showAlert(type, message) {
        if (!alertBox) return;
        alertBox.className = 'alert alert-' + type + ' alert-dismissible';
        alertBox.innerHTML = esc(message) + '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        alertBox.classList.remove('d-none');
        window.scrollTo({ top: 0, behavior: 'smooth' });
        clearTimeout(alertBox._hideTimer);
        alertBox._hideTimer = setTimeout(function () {
            alertBox.classList.add('d-none');
            alertBox.innerHTML = '';
        }, 1000);
    }

    function renderHistory(rows) {
        if (!rows || !rows.length) {
            historyBody.innerHTML = '<tr><td colspan="7" class="text-center text-muted py-3">No negotiation rounds yet.</td></tr>';
            return;
        }

        historyBody.innerHTML = rows.map(function (row) {
            return '<tr>' +
                '<td>' + esc(row.round) + '</td>' +
                '<td class="text-end">' + money(row.old_price) + '</td>' +
                '<td class="text-end">' + money(row.new_price) + '</td>' +
                '<td>' + esc(row.buyer_remarks || '--') + '</td>' +
                '<td>' + esc(row.vendor_response || '--') + '</td>' +
                '<td>' + esc(row.negotiated_by || '--') + '</td>' +
                '<td>' + esc(row.negotiated_date || '--') + '</td>' +
            '</tr>';
        }).join('');
    }

    function fillModal(data) {
        const q = data.quotation || {};
        document.getElementById('neg_rfq_no').value = q.rfq_no || '';
        document.getElementById('neg_vendor').value = q.vendor_name || '';
        document.getElementById('neg_original').value = money(q.original_quote);
        document.getElementById('neg_current').value = money(q.current_negotiated_price);
        document.getElementById('neg_new_price').value = q.negotiated_quote != null ? q.negotiated_quote : q.original_quote;
        document.getElementById('neg_buyer_remarks').value = q.buyer_remarks || '';
        document.getElementById('neg_vendor_response').value = q.vendor_response || '';
        renderHistory(data.history || []);

        const locked = !!(data.locked || q.locked);
        renderNegItems(q.items || [], locked);

        lockedBanner.classList.toggle('d-none', !locked);
        ['neg_new_price', 'neg_buyer_remarks', 'neg_vendor_response'].forEach(function (id) {
            document.getElementById(id).disabled = locked;
        });
        // Always keep Save visible; only disable when locked.
        saveBtn.classList.remove('d-none');
        saveBtn.disabled = locked;
        saveBtn.title = locked ? 'Negotiations are locked after PO creation' : 'Save Negotiation';
    }

    function renderNegItems(items, locked) {
        const tbodyItems = document.getElementById('negotiate-items-body');
        if (!tbodyItems) return;
        if (!items || !items.length) {
            tbodyItems.innerHTML = '<tr><td colspan="6" class="text-center text-muted py-2">No items found.</td></tr>';
            return;
        }
        let initialSum = 0;
        tbodyItems.innerHTML = items.map(function(item) {
            const rate = item.negotiated_unit_price != null ? Number(item.negotiated_unit_price) : Number(item.unit_price || 0);
            const qty = Number(item.quantity || 1);
            const tot = rate * qty;
            initialSum += tot;
            return '<tr>' +
                '<td>' + esc(item.item_name) + '<input type="hidden" class="neg-item-id" value="' + esc(item.id) + '"></td>' +
                '<td class="text-center">' + esc(item.quantity) + ' ' + esc(item.unit) + '</td>' +
                '<td class="text-end">' + money(item.unit_price) + '</td>' +
                '<td class="text-end">' + money(item.total) + '</td>' +
                '<td><input type="number" step="0.01" min="0" class="form-control form-control-sm neg-item-rate" data-qty="' + qty + '" value="' + rate.toFixed(2) + '" ' + (locked ? 'disabled' : '') + '></td>' +
                '<td class="text-end fw-bold neg-item-total">' + money(tot) + '</td>' +
            '</tr>';
        }).join('');
        const totInput = document.getElementById('neg_new_price');
        if (totInput && initialSum > 0) totInput.value = initialSum.toFixed(2);
    }

    document.getElementById('negotiate-items-body')?.addEventListener('input', function(e) {
        if (e.target.classList.contains('neg-item-rate')) {
            const input = e.target;
            const qty = parseFloat(input.getAttribute('data-qty')) || 1;
            const rate = parseFloat(input.value) || 0;
            const totalCell = input.closest('tr').querySelector('.neg-item-total');
            if (totalCell) totalCell.textContent = money(qty * rate);
            
            let sum = 0;
            document.querySelectorAll('.neg-item-rate').forEach(function(el) {
                const q = parseFloat(el.getAttribute('data-qty')) || 1;
                const r = parseFloat(el.value) || 0;
                sum += (q * r);
            });
            const totInput = document.getElementById('neg_new_price');
            if (totInput) totInput.value = sum.toFixed(2);
        }
    });

    function renderCompareRows(rows) {
        if (!rows || !rows.length) {
            tbody.innerHTML = '<tr class="compare-empty"><td colspan="5" class="text-center text-muted py-4">No quotations received yet.</td></tr>';
            return;
        }

        const anySelected = rows.some(function (r) { return r.is_selected; });

        tbody.innerHTML = rows.map(function (row) {
            const rowClass = row.is_selected ? 'table-success' : (row.is_lowest ? 'table-warning' : '');
            let actions = '<div class="d-flex flex-wrap gap-1">';

            if (canNegotiate && !anySelected) {
                actions += '<button type="button" class="btn btn-sm btn-outline-primary btn-negotiate"' +
                    ' data-quotation-id="' + row.id + '"' +
                    ' data-url="' + esc(row.negotiation_url) + '"' +
                    ' data-save-url="' + esc(row.save_url) + '"' +
                    (row.locked ? ' disabled title="Locked after vendor selection or PO creation"' : ' title="Negotiate with vendor"') +
                    '>Negotiate</button>';
            }

            if (canSelect) {
                if (!row.is_selected) {
                    actions += '<form method="POST" action="' + esc(row.select_url) + '" class="d-inline">' +
                        '<input type="hidden" name="_token" value="' + esc(csrf) + '">' +
                        '<button class="btn btn-sm btn-primary" type="submit">Select Vendor</button></form>';
                } else {
                    actions += '<span class="badge bg-label-success align-self-center">Ready for PO</span>';
                }
            }

            actions += '</div>';

            return '<tr data-quotation-id="' + row.id + '" class="' + rowClass + '">' +
                '<td><strong>' + esc(row.vendor_name) + '</strong>' +
                    (row.is_lowest ? ' <span class="badge bg-label-warning ms-1">Lowest</span>' : '') +
                    '<div class="small text-muted">' + esc(row.contact_person || '') + '</div></td>' +
                '<td class="text-end">' + money(row.original_quote) + '</td>' +
                '<td class="text-end"><strong>' + money(row.negotiated_quote) + '</strong>' +
                    (!row.has_negotiated ? '<div class="small text-muted">Same as original</div>' : '') +
                '</td>' +
                '<td class="text-end">' + money(row.savings) + '</td>' +
                '<td>' + actions + '</td>' +
            '</tr>';
        }).join('');
    }

    function openNegotiate(btn) {
        const url = btn.getAttribute('data-url');
        saveUrl = btn.getAttribute('data-save-url');
        errorBox.classList.add('d-none');
        errorBox.innerHTML = '';

        fetch(url, { headers: { Accept: 'application/json' } })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (!data.success) {
                    showAlert('danger', data.message || 'Unable to load negotiation.');
                    return;
                }
                fillModal(data);
                modal.show();
            })
            .catch(function () {
                showAlert('danger', 'Unable to load negotiation details.');
            });
    }

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-negotiate');
        if (!btn || btn.disabled) return;
        openNegotiate(btn);
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        if (submitting || !saveUrl) return;

        submitting = true;
        saveBtn.disabled = true;
        saveBtn.querySelector('.btn-text').classList.add('d-none');
        saveBtn.querySelector('.spinner-border').classList.remove('d-none');
        errorBox.classList.add('d-none');
        errorBox.innerHTML = '';

        const itemsList = [];
        document.querySelectorAll('#negotiate-items-body tr').forEach(function(tr) {
            const idInput = tr.querySelector('.neg-item-id');
            const rateInput = tr.querySelector('.neg-item-rate');
            if (idInput && rateInput) {
                itemsList.push({
                    id: idInput.value,
                    negotiated_unit_price: rateInput.value || 0
                });
            }
        });

        const payload = {
            negotiated_total_cost: document.getElementById('neg_new_price').value,
            buyer_remarks: document.getElementById('neg_buyer_remarks').value,
            vendor_response: document.getElementById('neg_vendor_response').value,
            items: itemsList,
        };

        fetch(saveUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrf,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify(payload),
        })
            .then(async function (r) {
                const data = await r.json();
                if (!r.ok || !data.success) {
                    const msg = data.message
                        || (data.errors ? Object.values(data.errors).flat().join('<br>') : null)
                        || 'Failed to save negotiation.';
                    errorBox.innerHTML = msg;
                    errorBox.classList.remove('d-none');
                    return;
                }

                renderCompareRows(data.rows || []);
                fillModal({
                    quotation: data.quotation,
                    history: data.history,
                    locked: data.quotation?.locked,
                });
                showAlert('success', data.message || 'Negotiation saved.');
                modal.hide();
            })
            .catch(function () {
                errorBox.textContent = 'Network error while saving negotiation.';
                errorBox.classList.remove('d-none');
            })
            .finally(function () {
                submitting = false;
                saveBtn.disabled = false;
                saveBtn.querySelector('.btn-text').classList.remove('d-none');
                saveBtn.querySelector('.spinner-border').classList.add('d-none');
            });
    });
});
</script>
@endsection
