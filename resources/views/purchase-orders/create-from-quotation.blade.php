@extends('layouts/contentNavbarLayout')

@section('title', 'Create Purchase Order')

@section('content')
@php
    $indent = $quotation->rfq?->purchaseRequisition?->indent;
    $indentItems = $indent?->items ?? collect();
    $quoteItems = $quotation->items ?? collect();

    $originalTotal = $quotation->originalQuote();
    $effectiveTotal = $quotation->effectiveQuote();
    $scale = ($originalTotal > 0 && $quotation->hasNegotiatedPrice())
        ? ($effectiveTotal / $originalTotal)
        : 1.0;

    $indentSumQty = (int) $indentItems->sum('quantity');
    $indentUnitPrice = $indentSumQty > 0
        ? (((float) $quotation->price * $scale) / $indentSumQty)
        : ((float) $quotation->price * $scale);

    $rows = $quoteItems->isNotEmpty()
        ? $quoteItems->values()->map(fn ($qi) => [
            'quotation_item_id' => $qi->id,
            'indent_item_id' => $qi->indent_item_id,
            'item_name' => $qi->item_name,
            'qty' => $qi->quantity,
            'unit' => $qi->unit ?? 'Nos',
            'rate' => round((float) $qi->unit_price * $scale, 2),
            'line_total' => round((float) $qi->total * $scale, 2),
            'manufacturer' => $qi->indentItem?->manufacturer,
            'category' => $qi->indentItem?->category,
        ])
        : $indentItems->values()->map(fn ($ii) => [
            'quotation_item_id' => null,
            'indent_item_id' => $ii->id,
            'item_name' => $ii->item_name,
            'qty' => $ii->quantity,
            'unit' => $ii->unit ?? 'Nos',
            'rate' => round((float) $indentUnitPrice, 2),
            'line_total' => round(((float) $ii->quantity) * (float) $indentUnitPrice, 2),
            'manufacturer' => $ii->manufacturer,
            'category' => $ii->category,
        ]);

    $itemsTotal = $rows->sum(fn ($row) => (float) $row['line_total']);

    $autoDeliveryDate = $quotation->delivery_days
        ? now()->addDays((int) $quotation->delivery_days)->toDateString()
        : null;

    $departmentName = $indent?->department?->department_name;
    $poType = $quotation->rfq?->purchaseRequisition?->id ? 'with_pr' : 'without_pr';
    $scaledTax = round((float) ($quotation->tax ?? 0) * $scale, 2);
    $scaledShipping = round((float) ($quotation->shipping ?? 0) * $scale, 2);
@endphp

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Create Purchase Order</h4>
    <a class="btn btn-secondary btn-sm" href="{{ route('rfqs.show', $quotation->rfq) }}">Back to RFQ</a>
</div>

<div class="card mb-4">
    <div class="card-body row g-3">
        <div class="col-md-3">
            <small class="text-muted d-block">Vendor</small>
            <strong>{{ $quotation->vendor->name ?? '--' }}</strong>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">Original Quote</small>
            <strong>{{ number_format($originalTotal, 2) }}</strong>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">{{ $quotation->hasNegotiatedPrice() ? 'Negotiated Total (PO)' : 'Quoted Total' }}</small>
            <strong>{{ number_format($effectiveTotal, 2) }}</strong>
            @if ($quotation->hasNegotiatedPrice())
                <div class="small text-success">Savings: {{ number_format($quotation->savingsAmount(), 2) }}</div>
            @endif
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">Delivery</small>
            <strong>{{ $quotation->delivery_days ? $quotation->delivery_days.' days' : '--' }}</strong>
        </div>
        <div class="col-md-3">
            <small class="text-muted d-block">Warranty</small>
            <strong>{{ $quotation->warranty ?: '--' }}</strong>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('purchase-orders.store-from-quotation', $quotation) }}">
            @csrf
            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Supplier Name</label>
                    <input class="form-control" value="{{ $quotation->vendor->name ?? '--' }}" disabled>
                </div>
                <div class="col-md-4">
                    <label class="form-label">GRN Department</label>
                    <input name="grn_store" class="form-control" value="{{ old('grn_store') }}" placeholder="Select / enter department">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Purchase Order Type</label>
                    <input class="form-control" value="{{ $poType === 'with_pr' ? 'With PR' : 'Without PR' }}" disabled>
                    <input type="hidden" name="po_type" value="{{ $poType }}">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Purchase Date</label>
                    <input type="date" name="purchase_date" value="{{ now()->toDateString() }}" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Estimated Delivery Date</label>
                    <input type="date" name="delivery_date" value="{{ $autoDeliveryDate }}" class="form-control">
                    @if ($autoDeliveryDate)
                        <small class="text-muted">Auto-filled: today + {{ $quotation->delivery_days }} delivery days</small>
                    @endif
                </div>
                <div class="col-md-4">
                    <label class="form-label">Currency</label>
                    <input name="currency" value="INR" class="form-control">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Department Name</label>
                    <input class="form-control" value="{{ $departmentName ?? '--' }}" disabled>
                    <input type="hidden" name="department_name" value="{{ $departmentName }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Tax</label>
                    <input type="number" step="0.01" name="tax" value="{{ old('tax', $scaledTax) }}" class="form-control">
                    <small class="text-muted">Auto-filled from quotation{{ $quotation->hasNegotiatedPrice() ? ' (scaled to negotiated)' : '' }}</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Shipping</label>
                    <input type="number" step="0.01" name="shipping" value="{{ old('shipping', $scaledShipping) }}" class="form-control">
                    <small class="text-muted">Auto-filled from quotation{{ $quotation->hasNegotiatedPrice() ? ' (scaled to negotiated)' : '' }}</small>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Discount Type</label>
                    <select name="discount_type" class="form-select">
                        <option value="percentage" {{ old('discount_type', 'percentage') === 'percentage' ? 'selected' : '' }}>Percentage</option>
                        <option value="flat" {{ old('discount_type') === 'flat' ? 'selected' : '' }}>Flat</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Discount</label>
                    <input type="number" step="0.01" name="discount" value="{{ old('discount', 0) }}" class="form-control">
                </div>
                <div class="col-12">
                    <label class="form-label">Payment Terms</label>
                    <textarea name="payment_terms" class="form-control" rows="2" placeholder="e.g. 50% advance, balance on delivery"></textarea>
                </div>
            </div>

            <hr class="my-4">
            <h6 class="fw-bold">PO Items <small class="text-muted fw-normal">(add item codes, HSN, free qty)</small></h6>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Item Name</th>
                            <th>Item Code</th>
                            <th>HSN Code</th>
                            <th>Manufacturer</th>
                            <th>Marketer</th>
                            <th>Unit</th>
                            <th>Qty</th>
                            <th>Rate</th>
                            <th>Free Qty</th>
                            <th>Category</th>
                            <th>Sub-Category</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($rows as $i => $row)
                            <tr>
                                <td>
                                    <div class="fw-bold">{{ $row['item_name'] }}</div>
                                    <div class="small text-muted mb-1">{{ $row['category']?->category_name ?? '' }}</div>
                                    <button type="button" class="btn btn-xs btn-outline-info btn-view-history" data-item-name="{{ $row['item_name'] }}">
                                        <i class="bx bx-history me-1"></i> View Previous Item History
                                    </button>
                                    <input type="hidden" name="items[{{ $i }}][quotation_item_id]" value="{{ $row['quotation_item_id'] }}">
                                    <input type="hidden" name="items[{{ $i }}][indent_item_id]" value="{{ $row['indent_item_id'] }}">
                                </td>
                                <td>
                                    <input name="items[{{ $i }}][item_code]" class="form-control" value="{{ old('items.' . $i . '.item_code') }}">
                                </td>
                                <td>
                                    <input name="items[{{ $i }}][hsn_code]" class="form-control" value="{{ old('items.' . $i . '.hsn_code') }}">
                                </td>
                                <td>
                                    <input name="items[{{ $i }}][manufacturer]" class="form-control"
                                           value="{{ old('items.' . $i . '.manufacturer', $row['manufacturer'] ?? '') }}">
                                </td>
                                <td>
                                    <input name="items[{{ $i }}][marketer_name]" class="form-control" value="{{ old('items.' . $i . '.marketer_name') }}">
                                </td>
                                <td>{{ $row['unit'] }}</td>
                                <td>{{ $row['qty'] }}</td>
                                <td>{{ number_format((float) $row['rate'], 2) }}</td>
                                <td>
                                    <input type="number" min="0" step="1" name="items[{{ $i }}][free_qty]" class="form-control"
                                           value="{{ old('items.' . $i . '.free_qty', 0) }}">
                                </td>
                                @php
                                    $category = $row['category'];
                                    $categoryName = $category
                                        ? ($category->parent_id ? \App\Models\AssetCategory::query()->whereKey($category->parent_id)->value('category_name') : $category->category_name)
                                        : '--';
                                    $subCategoryName = $category ? ($category->parent_id ? $category->category_name : null) : null;
                                @endphp
                                <td>{{ $categoryName ?? '--' }}</td>
                                <td>{{ $subCategoryName ?? '--' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="11" class="text-center">No items found on the quotation or indent.</td></tr>
                        @endforelse
                    </tbody>
                    <tfoot>
                        <tr>
                            <th colspan="9" class="text-end">Items Total</th>
                            <th colspan="2">{{ number_format($itemsTotal, 2) }}</th>
                        </tr>
                    </tfoot>
                </table>
            </div>

            <button class="btn btn-primary mt-3" type="submit">Create Purchase Order</button>
        </form>
    </div>
</div>
@include('procurement._item-history-modal')
@endsection
