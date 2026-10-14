@extends('layouts/contentNavbarLayout')

@section('title', isset($purchaseOrder) ? 'Edit Purchase Order' : 'Create Purchase Order')

@section('vendor-style')
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
<style>
    .select2-container { width: 100% !important; }
    .po-page-title { color: #696cff; font-weight: 600; }
    .po-back-link { color: #696cff; text-decoration: none; line-height: 1; }
    .po-back-link:hover { color: #5f61e6; }
    .po-form-card {
        border: 1px solid #e7e7e7;
        border-radius: 0.5rem;
        background: #fff;
        padding: 1.25rem 1.5rem;
        margin-bottom: 1rem;
    }
    .po-form-label {
        display: block;
        margin-bottom: 0.35rem;
        font-size: 0.8125rem;
        font-weight: 500;
        color: #566a7f;
    }
    .po-form-label .req { color: #ff3e1d; }
    .po-collapse-bar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        padding: 0.85rem 1rem;
        margin-bottom: 0.65rem;
        border: 1px solid #e7e7e7;
        border-radius: 0.375rem;
        background: #f5f5f9;
        color: #697a8d;
        font-weight: 500;
        cursor: pointer;
        user-select: none;
    }
    .po-collapse-bar .bx-chevron-down { color: #696cff; font-size: 1.25rem; }
    .po-detail-divider {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin: 1.5rem 0 1.25rem;
        color: #566a7f;
        font-weight: 600;
        font-size: 0.95rem;
    }
    .po-detail-divider::before,
    .po-detail-divider::after {
        content: '';
        flex: 1;
        border-top: 1px solid #d9dee3;
    }
    .po-item-block { border: 1px solid #d9dee3; border-radius: 0.5rem; background: #fff; overflow: visible; }
    .po-item-block .po-item-header { background: #f5f5f9; border-bottom: 1px solid #d9dee3; }
    #items-container,
    #items-container .row,
    .card:has(#items-container),
    .card:has(#items-container) .card-body {
        overflow: visible;
    }
    .item-name-wrap,
    .item-suggest-anchor,
    .rate-suggest-wrap { position: relative; overflow: visible; }
    .item-suggest-dropdown {
        z-index: 2000;
        max-height: 240px;
        overflow-y: auto;
        background: #fff;
        border: 1px solid #d9dee3;
        border-radius: 0.375rem;
        box-shadow: 0 0.5rem 1.25rem rgba(34, 48, 62, 0.18);
    }
    .item-suggest-dropdown .item-suggest-option {
        display: block;
        width: 100%;
        text-align: left;
        border: 0;
        border-bottom: 1px solid #f0f2f4;
        background: #fff;
        padding: 0.55rem 0.85rem;
        cursor: pointer;
    }
    .item-suggest-dropdown .item-suggest-option:last-child { border-bottom: 0; }
    .item-suggest-dropdown .item-suggest-option:hover,
    .item-suggest-dropdown .item-suggest-option.is-active {
        background: #f5f5f9;
    }
    .item-suggest-dropdown .item-suggest-title {
        display: block;
        font-weight: 600;
        color: #566a7f;
        line-height: 1.3;
    }
    .item-suggest-dropdown .item-suggest-meta {
        display: block;
        margin-top: 0.15rem;
        font-size: 0.75rem;
        color: #a1acb8;
    }
    .item-suggest-dropdown .item-suggest-empty,
    .item-suggest-dropdown .item-suggest-loading {
        padding: 0.75rem 0.85rem;
        color: #a1acb8;
        font-size: 0.8125rem;
    }
    .free-qty-check-wrap {
        display: flex;
        align-items: flex-end;
        height: 100%;
        padding-bottom: 0.45rem;
    }
</style>
@endsection

@section('content')
@php
    $isEdit = isset($purchaseOrder);
    $poSourceValue = old('po_source', request()->query('po_source'));
    if ($poSourceValue === null && $isEdit) {
        $storedSource = $purchaseOrder->sourceEnum()?->value;
        $poSourceValue = in_array($storedSource, ['from_pr', 'from_indent'], true) ? 'from_pr' : ($storedSource ?? 'direct');
    }
    $formItems = old('items');
    if ($formItems === null && $isEdit) {
        $formItems = $purchaseOrder->items->map(fn ($item) => [
            'indent_item_id' => $item->indent_item_id,
            'item_name' => $item->item_name,
            'item_code' => $item->item_code,
            'hsn_code' => $item->hsn_code,
            'manufacturer' => $item->manufacturer,
            'marketer_name' => $item->marketer_name,
            'item_category_id' => $item->item_category_id,
            'item_sub_category_id' => $item->item_sub_category_id,
            'purchase_unit' => $item->purchase_unit ?: ($item->unit ?: 'Nos'),
            'quantity' => $item->quantity,
            'free_qty' => $item->free_qty,
            'price' => $item->price,
            'mrp' => $item->mrp,
            'discount' => $item->discount,
            'discount_type' => $item->discount_type,
            'gst_percent' => $item->gst_percent,
            'epr' => $item->epr,
            'conversion' => $item->conversion,
            'qoh' => $item->qoh,
            'estimated_delivery_date' => optional($item->estimated_delivery_date)->format('Y-m-d') ?: $item->estimated_delivery_date,
            'remarks' => $item->remarks,
        ])->values()->all();
    }
@endphp
<div class="d-flex flex-wrap align-items-center gap-2 mb-4">
    <a href="{{ route('purchase-orders.index') }}" class="po-back-link fs-3">
        <i class="bx bx-left-arrow-alt"></i>
    </a>
    <h4 class="mb-0 po-page-title">{{ $isEdit ? 'Update Purchase Order' : 'Create Purchase Order' }}</h4>
</div>

@if (session('error'))
<div class="alert alert-danger">{{ session('error') }}</div>
@endif

@if ($errors->any())
<div class="alert alert-danger">
    <strong>Please fix the following errors:</strong>
    <ul class="mb-0 mt-2">
        @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
        @endforeach
    </ul>
</div>
@endif

<form method="POST" action="{{ $isEdit ? route('purchase-orders.update', $purchaseOrder) : route('purchase-orders.store') }}" id="po-form">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif
    @php
        $selectedDeptId = old('department_id', $isEdit ? $purchaseOrder->department_id : null);
        $selectedDeptName = old('department_name', $isEdit ? ($purchaseOrder->department_name ?? $purchaseOrder->department?->department_name) : null);
        if (! $selectedDeptName && $selectedDeptId) {
            $selectedDeptName = optional($departments->firstWhere('id', (int) $selectedDeptId))->department_name;
        }
        $selectedPoType = old('po_type', $isEdit ? ($purchaseOrder->po_type ?: 'Regular') : 'Regular');
    @endphp
    <input type="hidden" name="purchase_date" value="{{ old('purchase_date', $isEdit ? optional($purchaseOrder->purchase_date)->format('Y-m-d') : now()->toDateString()) }}">
    <input type="hidden" name="quotation_id" id="quotation_id" value="{{ old('quotation_id', $isEdit ? ($purchaseOrder->quotation_id ?? '') : '') }}">
    <input type="hidden" name="minimum_advance_payment_percentage" value="{{ old('minimum_advance_payment_percentage', $isEdit ? ($purchaseOrder->minimum_advance_payment_percentage ?? 0) : '0') }}">
    <input type="hidden" name="department_id" id="department_id" value="{{ $selectedDeptId }}">
    <input type="hidden" name="department_name" id="department_name" value="{{ $selectedDeptName }}">

    <div class="po-form-card">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="po-form-label">Supplier Name <span class="req">*</span></label>
                <select name="vendor_id" id="vendor_id" class="form-select select2 @error('vendor_id') is-invalid @enderror" required>
                    <option value="">Select</option>
                    @foreach ($vendors as $vendor)
                        <option value="{{ $vendor->id }}" data-state="{{ strtolower(trim($vendor->state ?? '')) }}" {{ (string) old('vendor_id', $isEdit ? $purchaseOrder->vendor_id : null) === (string) $vendor->id ? 'selected' : '' }}>
                            {{ $vendor->name }}{{ strtolower((string) ($vendor->status ?? '')) === 'active' ? '' : ' ('.($vendor->status ?: 'inactive').')' }}
                        </option>
                    @endforeach
                </select>
                @error('vendor_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="po-form-label">GRN Store <span class="req">*</span></label>
                <select name="grn_store" id="grn_department" class="form-select select2 @error('grn_store') is-invalid @enderror" required>
                    <option value="">Select</option>
                    @foreach ($departments as $dept)
                        <option value="{{ $dept->department_name }}" data-id="{{ $dept->id }}" {{ old('grn_store', $isEdit ? $purchaseOrder->grn_store : null) === $dept->department_name ? 'selected' : '' }}>{{ $dept->department_name }}</option>
                    @endforeach
                </select>
                @error('grn_store')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="po-form-label">Department Name</label>
                <input type="text" id="department_name_display" class="form-control bg-light" value="{{ $selectedDeptName }}" placeholder="Department Name" readonly>
                @error('department_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="po-form-label">Cost Center Name</label>
                <input type="text" name="cost_center_name" id="cost_center_name" class="form-control bg-light" value="{{ old('cost_center_name', $isEdit ? ($purchaseOrder->cost_center_name ?? '') : '') }}" placeholder="Cost Center Name" readonly>
            </div>
            <div class="col-md-4">
                <label class="po-form-label">Purchase Order Type</label>
                <select name="po_type" class="form-select @error('po_type') is-invalid @enderror">
                    @foreach ($poTypes as $type)
                        <option value="{{ $type }}" {{ $selectedPoType === $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
                @error('po_type')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
                <label class="po-form-label">Estimated Delivery Date</label>
                <input type="date" name="delivery_date" id="header_delivery_date" class="form-control @error('delivery_date') is-invalid @enderror" value="{{ old('delivery_date', $isEdit ? optional($purchaseOrder->delivery_date)->format('Y-m-d') : null) }}" required>
                @error('delivery_date')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    <div class="po-collapse-bar" data-bs-toggle="collapse" data-bs-target="#collapseTerms" aria-expanded="false">
        <span>PO Terms &amp; Condition</span>
        <i class="bx bx-chevron-down"></i>
    </div>
    <div id="collapseTerms" class="collapse mb-2">
        <div class="po-form-card">
            <label class="po-form-label">Payment Terms &amp; Conditions</label>
            <textarea name="payment_terms" class="form-control" rows="3" placeholder="Enter standard payment terms, warranty, delivery conditions...">{{ old('payment_terms', $isEdit ? ($purchaseOrder->payment_terms ?? '') : '') }}</textarea>
        </div>
    </div>

    <div class="po-collapse-bar" data-bs-toggle="collapse" data-bs-target="#collapseSupplier" aria-expanded="false">
        <span>Supplier Details</span>
        <i class="bx bx-chevron-down"></i>
    </div>
    <div id="collapseSupplier" class="collapse mb-2">
        <div class="po-form-card" id="supplier-details-body">
            <p class="text-muted mb-0">Select a supplier above to link contact, address, and GST details.</p>
        </div>
    </div>

    <div class="po-detail-divider">Purchase Order Detail</div>

    <div class="bg-white rounded p-3 mb-4 border shadow-sm">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
            <div class="row g-3 flex-grow-1 m-0">
                <div class="col-md-4 col-sm-6 p-0 pe-md-2">
                    <label class="po-form-label">Purchase Order Type <span class="req">*</span></label>
                    @if ($isEdit)
                        <input type="hidden" name="po_source" value="{{ $poSourceValue }}">
                        <select id="po_source" class="form-select" disabled>
                            <option value="">Select</option>
                            <option value="from_pr" {{ in_array($poSourceValue, ['from_pr', 'from_indent'], true) ? 'selected' : '' }}>With PR</option>
                            <option value="direct" {{ $poSourceValue === 'direct' ? 'selected' : '' }}>Without PR</option>
                        </select>
                    @else
                    <select name="po_source" id="po_source" class="form-select @error('po_source') is-invalid @enderror" required>
                        <option value="">Select</option>
                        <option value="from_pr" {{ in_array(old('po_source', request()->query('po_source')), ['from_pr', 'from_indent'], true) ? 'selected' : '' }}>With PR</option>
                        <option value="direct" {{ old('po_source', request()->query('po_source', 'direct')) === 'direct' ? 'selected' : '' }}>Without PR</option>
                    </select>
                    @endif
                    @error('po_source')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="col-md-6 col-sm-6 p-0 px-md-2" id="pr-select-wrap" style="display:none;">
                    <label class="po-form-label">Purchase Requisition <span class="req">*</span></label>
                    @if ($isEdit && in_array($poSourceValue, ['from_pr', 'from_indent'], true))
                        <input type="hidden" name="pr_id" value="{{ old('pr_id', $purchaseOrder->pr_id) }}">
                    @endif
                    <select name="pr_id" id="pr_id" class="form-select @error('pr_id') is-invalid @enderror" @if($isEdit && in_array($poSourceValue, ['from_pr', 'from_indent'], true)) disabled @endif>
                        <option value="">Select purchase requisition</option>
                        @foreach ($availableRequisitions as $pr)
                            @php
                                $itemNames = $pr->indent?->items
                                    ?->pluck('item_name')
                                    ->filter()
                                    ->unique()
                                    ->values() ?? collect();
                                $itemLabel = $itemNames->isNotEmpty()
                                    ? $itemNames->take(3)->implode(', ').($itemNames->count() > 3 ? ' +'.($itemNames->count() - 3).' more' : '')
                                    : 'No assets';
                                $selectedPrId = old('pr_id', $isEdit ? $purchaseOrder->pr_id : request()->query('pr_id'));
                            @endphp
                            <option value="{{ $pr->id }}" {{ (string) $selectedPrId === (string) $pr->id ? 'selected' : '' }}>
                                {{ $pr->pr_no }} — {{ $itemLabel }}
                                @if ($pr->indent->department->department_name ?? null)
                                    ({{ $pr->indent->department->department_name }})
                                @endif
                            </option>
                        @endforeach
                    </select>
                    @error('pr_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            </div>
            
            <div class="mt-4 pt-1">
                <button type="button" class="btn btn-sm btn-outline-primary text-nowrap" id="btn-add-item">
                    <i class="bx bx-plus"></i> Add Asset
                </button>
            </div>
        </div>
    </div>

        <div id="vendor-required-hint" class="alert alert-warning py-2 mb-3" style="display:none;">
            Select a <strong>Supplier</strong> before adding indent assets or creating this purchase order.
        </div>
        <div id="items-container"></div>
        <div id="items-empty" class="text-center text-muted py-4">Add assets to this purchase order.</div>
    </div>



    <div class="d-flex flex-wrap gap-2">
        <button type="submit" name="action" value="draft" class="btn btn-outline-secondary" id="btn-save-draft">{{ $isEdit ? 'Save Changes' : 'Save Draft' }}</button>
        @if (! $isEdit || ($purchaseOrder->statusEnum() === \App\Enums\Procurement\PurchaseOrderStatus::Draft))
            <button type="submit" name="action" value="submit" class="btn btn-primary" id="btn-submit-po">Submit Purchase Order</button>
        @endif
        <a href="{{ route('purchase-orders.index') }}" class="btn btn-light">Cancel</a>
    </div>
</form>
@include('procurement._item-history-modal')
@endsection

@section('page-script')
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const poSource = document.getElementById('po_source');
    const prWrap = document.getElementById('pr-select-wrap');
    const prSelect = document.getElementById('pr_id');
    const itemsContainer = document.getElementById('items-container');
    const itemsEmpty = document.getElementById('items-empty');
    const btnAddItem = document.getElementById('btn-add-item');
    const departmentIdInput = document.getElementById('department_id');
    const departmentNameInput = document.getElementById('department_name');
    const departmentNameDisplay = document.getElementById('department_name_display');
    const costCenterInput = document.getElementById('cost_center_name');
    const grnDepartmentSelect = document.getElementById('grn_department');

    const categories = @json($categories->map(fn ($c) => ['id' => $c->id, 'name' => $c->category_name])->values());
    const subCategories = @json($subCategories->map(fn ($c) => ['id' => $c->id, 'parent_id' => $c->parent_id, 'name' => $c->category_name])->values());
    const prDataUrl = @json(route('purchase-orders.purchase-requisitions.data', ['purchaseRequisition' => '__ID__']));
    const itemSearchUrl = @json(route('purchase-orders.items.search'));
    const vendorRatesUrl = @json(route('purchase-orders.vendor-rates'));
    const oldItems = @json($formItems ?? []);

    let rowIndex = 0;

    function getSelectedVendorId() {
        return document.getElementById('vendor_id')?.value || '';
    }

    function syncDepartmentFromGrnStore() {
        if (!grnDepartmentSelect) return;
        const opt = grnDepartmentSelect.options[grnDepartmentSelect.selectedIndex];
        const deptId = opt ? (opt.dataset.id || '') : '';
        const deptName = opt && opt.value ? opt.value : '';
        if (departmentIdInput) departmentIdInput.value = deptId;
        if (departmentNameInput) departmentNameInput.value = deptName;
        if (departmentNameDisplay) departmentNameDisplay.value = deptName;
        if (costCenterInput && !costCenterInput.value) {
            costCenterInput.value = deptName;
        }
    }

    if (window.jQuery) {
        const $vendor = $('#vendor_id');
        if ($vendor.hasClass('select2-hidden-accessible')) {
            $vendor.select2('destroy');
        }
        $vendor.select2({
            theme: 'bootstrap-5',
            placeholder: 'Select',
            allowClear: true,
            width: '100%',
            dropdownParent: $(document.body)
        });

        const $grn = $('#grn_department');
        if ($grn.length) {
            if ($grn.hasClass('select2-hidden-accessible')) {
                $grn.select2('destroy');
            }
            $grn.select2({
                theme: 'bootstrap-5',
                placeholder: 'Select',
                allowClear: true,
                width: '100%',
                dropdownParent: $(document.body)
            });
            $grn.on('change', syncDepartmentFromGrnStore);
        }
    }

    if (grnDepartmentSelect) {
        grnDepartmentSelect.addEventListener('change', syncDepartmentFromGrnStore);
        syncDepartmentFromGrnStore();
    }

    function categoryOptions(selected) {
        return categories.map(function (c) {
            return '<option value="' + c.id + '"' + (String(selected || '') === String(c.id) ? ' selected' : '') + '>' + c.name + '</option>';
        }).join('');
    }

    function subCategoryOptions(parentId, selected) {
        return subCategories.filter(function (s) { return String(s.parent_id) === String(parentId); })
            .map(function (s) {
                return '<option value="' + s.id + '"' + (String(selected || '') === String(s.id) ? ' selected' : '') + '>' + s.name + '</option>';
            }).join('');
    }

    function assetTypeDisplay(value) {
        const raw = String(value || '').trim();
        if (!raw) return '';
        const lower = raw.toLowerCase();
        if (lower === 'asset' || lower === 'consumable') return '';
        return raw;
    }

    function updateEmptyState() {
        if (!itemsContainer || !itemsEmpty) return;
        const hasItems = itemsContainer.querySelectorAll('.po-item-block').length > 0;
        itemsEmpty.style.display = hasItems ? 'none' : '';
    }

    function fillFromSuggestion(block, item) {
        if (!block || !item) return;
        const nameInput = block.querySelector('.field-item-name');
        if (nameInput) nameInput.value = item.item_name || '';
        const codeInput = block.querySelector('.field-item-code');
        if (codeInput) codeInput.value = item.item_code || '';
        const hsn = block.querySelector('[name*="[hsn_code]"]');
        if (hsn) hsn.value = item.hsn_code || '';
        const unit = block.querySelector('[name*="[purchase_unit]"]');
        if (unit) unit.value = item.purchase_unit || item.unit || 'Nos';
        const itemType = block.querySelector('[name*="[item_type]"]');
        if (itemType) itemType.value = 'asset';
        const typeName = block.querySelector('.field-asset-type-name');
        if (typeName) typeName.value = assetTypeDisplay(item.asset_type_name || item.composition || item.item_type || '');
        const indentItemId = block.querySelector('[name*="[indent_item_id]"]');
        if (indentItemId && item.indent_item_id) indentItemId.value = item.indent_item_id;
        const cat = block.querySelector('.field-category');
        const sub = block.querySelector('.field-subcategory');
        if (cat && item.item_category_id) {
            cat.value = item.item_category_id;
            sub.innerHTML = '<option value="">Select sub category</option>' + subCategoryOptions(item.item_category_id, item.item_sub_category_id);
            if (item.item_sub_category_id) sub.value = item.item_sub_category_id;
        }
        const price = block.querySelector('.field-purchase-rate');
        if (price && item.price) price.value = item.price;
        const qty = block.querySelector('[name*="[quantity]"]');
        if (qty && item.quantity) qty.value = item.quantity;
    }

    function initItemNameSuggestions(inputEl, block) {
        if (!inputEl || inputEl.dataset.suggestionsReady === '1') return;
        inputEl.dataset.suggestionsReady = '1';

        let debounceTimer = null;
        let requestToken = 0;
        let dropdown = null;
        let activeIndex = -1;
        let currentItems = [];

        function positionDropdown() {
            if (!dropdown) return;
            const rect = inputEl.getBoundingClientRect();
            dropdown.style.position = 'fixed';
            dropdown.style.left = rect.left + 'px';
            dropdown.style.width = Math.max(rect.width, 260) + 'px';
            dropdown.style.top = (rect.bottom + 4) + 'px';
            dropdown.style.right = 'auto';
        }

        function ensureDropdown() {
            if (!dropdown) {
                dropdown = document.createElement('div');
                dropdown.className = 'item-suggest-dropdown';
                dropdown.setAttribute('role', 'listbox');
                document.body.appendChild(dropdown);
            }
            positionDropdown();
            return dropdown;
        }

        function hideDropdown() {
            if (dropdown) {
                dropdown.remove();
                dropdown = null;
            }
            activeIndex = -1;
            currentItems = [];
        }

        function setActiveOption(index) {
            if (!dropdown) return;
            const options = dropdown.querySelectorAll('.item-suggest-option');
            options.forEach(function (el, i) {
                el.classList.toggle('is-active', i === index);
            });
            if (index >= 0 && options[index]) {
                options[index].scrollIntoView({ block: 'nearest' });
            }
            activeIndex = index;
        }

        function selectItem(item) {
            if (!item) return;
            inputEl.value = item.item_name || '';
            fillFromSuggestion(block, item);
            hideDropdown();
        }

        function renderSuggestions(items, state) {
            const panel = ensureDropdown();
            panel.innerHTML = '';
            currentItems = items || [];
            activeIndex = -1;

            if (state === 'loading') {
                panel.innerHTML = '<div class="item-suggest-loading">Searching...</div>';
                return;
            }

            if (!currentItems.length) {
                panel.innerHTML = '<div class="item-suggest-empty">No matching assets found</div>';
                return;
            }

            currentItems.forEach(function (item, index) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'item-suggest-option';
                btn.setAttribute('role', 'option');
                btn.innerHTML =
                    '<span class="item-suggest-title">' + escHtml(item.item_name || '') + '</span>' +
                    '<span class="item-suggest-meta">' +
                        escHtml(item.item_code || 'No code') +
                        (item.purchase_unit || item.unit ? ' · ' + escHtml(item.purchase_unit || item.unit) : '') +
                    '</span>';
                btn.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    selectItem(item);
                });
                btn.addEventListener('mouseenter', function () {
                    setActiveOption(index);
                });
                panel.appendChild(btn);
            });
        }

        function fetchSuggestions(term) {
            const token = ++requestToken;
            renderSuggestions([], 'loading');

            fetch(itemSearchUrl + '?q=' + encodeURIComponent(term || '') + '&limit=15', {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            })
                .then(function (r) {
                    if (!r.ok) throw new Error('Search failed');
                    return r.json();
                })
                .then(function (data) {
                    if (token !== requestToken) return;
                    renderSuggestions(data.results || []);
                })
                .catch(function () {
                    if (token !== requestToken) return;
                    const panel = ensureDropdown();
                    panel.innerHTML = '<div class="item-suggest-empty">Unable to load suggestions</div>';
                });
        }

        inputEl.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                fetchSuggestions(inputEl.value.trim());
            }, 200);
        });

        inputEl.addEventListener('focus', function () {
            fetchSuggestions(inputEl.value.trim());
        });

        inputEl.addEventListener('keydown', function (e) {
            if (!dropdown) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (!currentItems.length) return;
                setActiveOption(Math.min(activeIndex + 1, currentItems.length - 1));
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (!currentItems.length) return;
                setActiveOption(Math.max(activeIndex - 1, 0));
            } else if (e.key === 'Enter' && activeIndex >= 0) {
                e.preventDefault();
                selectItem(currentItems[activeIndex]);
            } else if (e.key === 'Escape') {
                hideDropdown();
            }
        });

        inputEl.addEventListener('blur', function () {
            setTimeout(hideDropdown, 150);
        });

        window.addEventListener('resize', function () {
            if (dropdown) positionDropdown();
        });

        window.addEventListener('scroll', function () {
            if (dropdown) positionDropdown();
        }, true);
    }

    function initRateSuggestions(inputEl, block) {
        if (!inputEl || inputEl.dataset.rateSuggestionsReady === '1') return;
        inputEl.dataset.rateSuggestionsReady = '1';

        let debounceTimer = null;
        let requestToken = 0;
        let dropdown = null;

        function hideDropdown() {
            if (dropdown) {
                dropdown.remove();
                dropdown = null;
            }
        }

        function positionDropdown() {
            if (!dropdown) return;
            const rect = inputEl.getBoundingClientRect();
            dropdown.style.position = 'fixed';
            dropdown.style.left = rect.left + 'px';
            dropdown.style.width = Math.max(rect.width, 280) + 'px';
            dropdown.style.top = (rect.bottom + 4) + 'px';
            dropdown.style.right = 'auto';
        }

        function renderSuggestions(items, state) {
            hideDropdown();
            dropdown = document.createElement('div');
            dropdown.className = 'item-suggest-dropdown';
            dropdown.setAttribute('role', 'listbox');
            document.body.appendChild(dropdown);
            positionDropdown();

            if (state === 'loading') {
                dropdown.innerHTML = '<div class="item-suggest-loading">Loading vendor rates...</div>';
                return;
            }

            if (state === 'no-vendor') {
                dropdown.innerHTML = '<div class="item-suggest-empty">Select a supplier first to see rate suggestions</div>';
                return;
            }

            if (!items.length) {
                dropdown.innerHTML = '<div class="item-suggest-empty">No negotiated rates found for this vendor</div>';
                return;
            }

            items.forEach(function (item) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'item-suggest-option';
                btn.innerHTML =
                    '<span class="item-suggest-title">' + escHtml(item.label || item.rate) +
                        (item.source === 'negotiated_total_cost' || item.source === 'negotiated_unit_price'
                            ? ' <small class="text-muted">(Negotiated)</small>' : '') +
                    '</span>' +
                    '<span class="item-suggest-meta">' + escHtml(item.title || '') +
                        (item.meta ? ' · ' + escHtml(item.meta) : '') +
                    '</span>';
                btn.addEventListener('mousedown', function (e) {
                    e.preventDefault();
                    inputEl.value = item.rate;
                    hideDropdown();
                });
                dropdown.appendChild(btn);
            });
        }

        function fetchRates() {
            const vendorId = getSelectedVendorId();
            if (!vendorId) {
                renderSuggestions([], 'no-vendor');
                return;
            }

            const token = ++requestToken;
            const itemName = (block.querySelector('.field-item-name')?.value || '').trim();
            renderSuggestions([], 'loading');

            const url = vendorRatesUrl
                + '?vendor_id=' + encodeURIComponent(vendorId)
                + '&item_name=' + encodeURIComponent(itemName)
                + '&limit=15';

            fetch(url, {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            })
                .then(function (r) {
                    if (!r.ok) throw new Error('Failed');
                    return r.json();
                })
                .then(function (data) {
                    if (token !== requestToken) return;
                    renderSuggestions(data.results || []);
                })
                .catch(function () {
                    if (token !== requestToken) return;
                    renderSuggestions([]);
                });
        }

        inputEl.addEventListener('focus', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(fetchRates, 50);
        });

        inputEl.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(fetchRates, 250);
        });

        inputEl.addEventListener('blur', function () {
            setTimeout(hideDropdown, 150);
        });

        window.addEventListener('resize', function () {
            if (dropdown) positionDropdown();
        });
        window.addEventListener('scroll', function () {
            if (dropdown) positionDropdown();
        }, true);
    }

    function buildItemBlock(data, index) {
        data = data || {};
        const i = index;
        const directPo = isDirectPo();
        const block = document.createElement('div');
        block.className = 'po-item-block mb-3 item-row';
        block.dataset.index = String(i);

        block.innerHTML =
            '<div class="po-item-header d-flex justify-content-between align-items-center px-3 py-2">' +
                '<strong class="item-title">Asset ' + (index + 1) + '</strong>' +
                '<div class="d-flex gap-2">' +
                    '<button type="button" class="btn btn-sm btn-outline-info btn-view-history" title="View Previous Item History"><i class="bx bx-history me-1"></i> View Previous Item History</button>' +
                    '<button type="button" class="btn btn-sm btn-outline-danger btn-remove-row"><i class="bx bx-trash"></i> Remove</button>' +
                '</div>' +
            '</div>' +
            '<div class="p-3">' +
                '<input type="hidden" name="items[' + i + '][indent_item_id]" value="' + escAttr(data.indent_item_id || '') + '">' +
                '<input type="hidden" name="items[' + i + '][item_type]" value="asset">' +
                '<div class="row g-3">' +
                    // Row 1: Item Name | Free Qty | Item Code
                    '<div class="col-md-4 item-name-wrap">' +
                        '<label class="po-form-label">Item Name <span class="req">*</span></label>' +
                        '<div class="item-suggest-anchor">' +
                            '<input name="items[' + i + '][item_name]" class="form-control field-item-name" value="' + escAttr(data.item_name || '') + '" required placeholder="Select" autocomplete="off"' + (directPo ? '' : ' aria-autocomplete="list"') + '>' +
                        '</div>' +
                    '</div>' +
                    '<div class="col-md-4">' +
                        '<div class="free-qty-check-wrap">' +
                            '<div class="form-check">' +
                                '<input class="form-check-input chk-free-qty" type="checkbox" value="1" id="freeQtyCheck' + i + '" name="items[' + i + '][has_free_qty]"' + (data.has_free_qty ? ' checked' : '') + '>' +
                                '<label class="form-check-label" for="freeQtyCheck' + i + '">Free Qty</label>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                    '<div class="col-md-4">' +
                        '<label class="po-form-label">Item Code</label>' +
                        '<input name="items[' + i + '][item_code]" class="form-control field-item-code bg-light" value="' + escAttr(data.item_code || '') + '" placeholder="Item Code (Autofilled)" readonly>' +
                    '</div>' +
                    // Row 2: Manufacturer | Marketer Name | HSN Code
                    '<div class="col-md-4">' +
                        '<label class="po-form-label">Manufacturer</label>' +
                        '<input name="items[' + i + '][manufacturer]" class="form-control field-manufacturer" value="' + escAttr(data.manufacturer || '') + '" placeholder="Enter Manufacturer Name">' +
                    '</div>' +
                    '<div class="col-md-4">' +
                        '<label class="po-form-label">Marketer Name</label>' +
                        '<select name="items[' + i + '][marketer_name]" class="form-select field-marketer"><option value="">Select</option>' + (data.marketer_name ? '<option value="' + escAttr(data.marketer_name) + '" selected>' + escAttr(data.marketer_name) + '</option>' : '') + '</select>' +
                    '</div>' +
                    '<div class="col-md-4">' +
                        '<label class="po-form-label">HSN Code <span class="req">*</span></label>' +
                        '<input name="items[' + i + '][hsn_code]" class="form-control field-hsn" value="' + escAttr(data.hsn_code || '') + '" placeholder="Enter HSN Code" required>' +
                    '</div>' +
                    // Row 3: Purchase Unit | Conversion | QOH
                    '<div class="col-md-4">' +
                        '<label class="po-form-label">Purchase Unit</label>' +
                        '<select name="items[' + i + '][purchase_unit]" class="form-select field-uom"><option value="">Select</option><option value="' + escAttr(data.purchase_unit || data.unit || 'Nos') + '" selected>' + escAttr(data.purchase_unit || data.unit || 'Nos') + '</option></select>' +
                    '</div>' +
                    '<div class="col-md-4">' +
                        '<label class="po-form-label">Conversion</label>' +
                        '<input type="number" step="0.01" min="0" name="items[' + i + '][conversion]" class="form-control bg-light" value="' + (data.conversion || 0) + '" readonly>' +
                    '</div>' +
                    '<div class="col-md-4">' +
                        '<label class="po-form-label">QOH</label>' +
                        '<input type="number" step="1" min="0" name="items[' + i + '][qoh]" class="form-control bg-light" value="' + (data.qoh || 0) + '" readonly>' +
                    '</div>' +
                    // Row 4: Purchase Quantity | Free Quantity | Estimated Delivery Date
                    '<div class="col-md-4">' +
                        '<label class="po-form-label">Purchase Quantity <span class="req">*</span></label>' +
                        '<input type="number" min="0" step="1" name="items[' + i + '][quantity]" class="form-control field-qty" value="' + (data.quantity || 0) + '" required>' +
                    '</div>' +
                    '<div class="col-md-4">' +
                        '<label class="po-form-label">Free Quantity</label>' +
                        '<input type="number" min="0" step="0.01" name="items[' + i + '][free_qty]" class="form-control field-free-qty" value="' + (data.free_qty || 0) + '"' + (data.has_free_qty ? '' : ' disabled') + '>' +
                    '</div>' +
                    '<div class="col-md-4">' +
                        '<label class="po-form-label">Estimated Delivery Date</label>' +
                        '<input type="date" name="items[' + i + '][estimated_delivery_date]" class="form-control" value="' + escAttr(data.estimated_delivery_date || '') + '">' +
                    '</div>' +
                    // Row 5: Purchase Rate | Discount Type | Discount
                    '<div class="col-md-4 rate-suggest-wrap">' +
                        '<label class="po-form-label">Purchase Rate <span class="req">*</span></label>' +
                        '<input type="number" step="0.01" min="0" name="items[' + i + '][price]" class="form-control field-purchase-rate" value="' + (data.price || 0) + '" required placeholder="0">' +
                    '</div>' +
                    '<div class="col-md-4">' +
                        '<label class="po-form-label">Discount Type <span class="req">*</span></label>' +
                        '<select name="items[' + i + '][discount_type]" class="form-select field-discount-type"><option value="percentage" ' + (data.discount_type === 'fixed' ? '' : 'selected') + '>Percentage</option><option value="fixed" ' + (data.discount_type === 'fixed' ? 'selected' : '') + '>Fixed (₹)</option></select>' +
                    '</div>' +
                    '<div class="col-md-4">' +
                        '<label class="po-form-label">Discount</label>' +
                        '<input type="number" step="0.01" min="0" name="items[' + i + '][discount]" class="form-control field-discount" value="' + (data.discount || 0) + '">' +
                    '</div>' +
                    // Row 6: EPR
                    '<div class="col-md-4">' +
                        '<label class="po-form-label">EPR</label>' +
                        '<input type="number" step="0.01" min="0" name="items[' + i + '][epr]" class="form-control field-epr bg-light" value="' + (data.epr || 0) + '" readonly>' +
                    '</div>' +
                    '<div class="col-md-8"></div>' +
                    // Appended hidden/extra fields
                    '<div class="col-12 mt-4 border-top pt-3 d-none">' +
                        '<h6 class="text-muted mb-3">Additional Details</h6>' +
                        '<div class="row g-3">' +
                            '<div class="col-md-3"><label class="form-label">MRP</label><input type="number" step="0.01" min="0" name="items[' + i + '][mrp]" class="form-control field-mrp" value="' + (data.mrp || '') + '" placeholder="0.00"></div>' +
                            '<div class="col-md-3"><label class="form-label">GST (%)</label><input type="number" step="0.01" min="0" max="100" name="items[' + i + '][gst_percent]" class="form-control field-gst" value="' + (data.gst_percent !== undefined && data.gst_percent !== null && data.gst_percent !== '' ? data.gst_percent : 18) + '"></div>' +
                            '<div class="col-md-3"><label class="form-label">Asset Type</label><input type="text" class="form-control field-asset-type-name" value="' + escAttr(assetTypeDisplay(data.asset_type_name || data.composition || '')) + '" placeholder="Asset type name" autocomplete="off"></div>' +
                            '<div class="col-md-3"><label class="form-label">Asset Category</label><select name="items[' + i + '][item_category_id]" class="form-select field-category"><option value="">Select category</option>' + categoryOptions(data.item_category_id) + '</select></div>' +
                            '<div class="col-md-3"><label class="form-label">Asset Sub Category</label><select name="items[' + i + '][item_sub_category_id]" class="form-select field-subcategory"><option value="">Select sub category</option>' + subCategoryOptions(data.item_category_id, data.item_sub_category_id) + '</select></div>' +
                            '<div class="col-12"><label class="form-label">Remarks</label><textarea name="items[' + i + '][remarks]" class="form-control" rows="2">' + escHtml(data.remarks || '') + '</textarea></div>' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';

        const cat = block.querySelector('.field-category');
        const sub = block.querySelector('.field-subcategory');
        cat.addEventListener('change', function () {
            sub.innerHTML = '<option value="">Select sub category</option>' + subCategoryOptions(cat.value, null);
        });

        if (!directPo) {
            initItemNameSuggestions(block.querySelector('.field-item-name'), block);
            initRateSuggestions(block.querySelector('.field-purchase-rate'), block);
        }

        const chkFree = block.querySelector('.chk-free-qty');
        const inputFree = block.querySelector('.field-free-qty');
        if (chkFree && inputFree) {
            chkFree.addEventListener('change', function () {
                if (this.checked) {
                    inputFree.disabled = false;
                } else {
                    inputFree.disabled = true;
                    inputFree.value = 0;
                }
            });
        }

        return block;
    }

    function getVendorState() {
        const vendorEl = document.getElementById('vendor_id');
        if (!vendorEl || !vendorEl.value) return '';
        const selectedOpt = vendorEl.options[vendorEl.selectedIndex];
        return selectedOpt ? (selectedOpt.dataset.state || '').trim().toLowerCase() : '';
    }

    const companyState = (@json(strtolower(trim(\App\Models\Company::query()->value('state') ?? '')))).trim().toLowerCase();

    function isIntraState() {
        const vState = getVendorState();
        if (!vState || !companyState) return true;
        return vState === companyState;
    }

    function formatCurr(val) {
        return '₹' + (isNaN(val) ? 0 : val).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function recalculatePoSummary() {
        const tbody = document.getElementById('po-summary-tbody');
        if (!tbody) return;
        
        const itemCards = document.querySelectorAll('#items-container .po-item-block');
        const intra = isIntraState();

        document.querySelectorAll('.col-cgst, .col-sgst, .row-cgst, .row-sgst').forEach(el => el.style.display = intra ? '' : 'none');
        document.querySelectorAll('.col-igst, .row-igst').forEach(el => el.style.display = intra ? 'none' : '');

        if (itemCards.length === 0) {
            tbody.innerHTML = '<tr><td colspan="16" class="text-center text-muted py-3">No items added yet.</td></tr>';
            document.getElementById('summary-total-qty').textContent = '0';
            document.getElementById('summary-sub-total').textContent = formatCurr(0);
            document.getElementById('summary-total-discount').textContent = '-' + formatCurr(0);
            document.getElementById('summary-taxable').textContent = formatCurr(0);
            document.getElementById('summary-cgst').textContent = formatCurr(0);
            document.getElementById('summary-sgst').textContent = formatCurr(0);
            document.getElementById('summary-igst').textContent = formatCurr(0);
            document.getElementById('summary-grand-total').textContent = formatCurr(0);
            return;
        }

        let html = '';
        let totalQty = 0, subTotal = 0, totalDiscount = 0, taxableTotal = 0;
        let totalCgst = 0, totalSgst = 0, totalIgst = 0, grandTotal = 0;

        itemCards.forEach((card, idx) => {
            const name = card.querySelector('.field-item-name')?.value || '—';
            const price = parseFloat(card.querySelector('.field-purchase-rate')?.value || 0) || 0;
            const disc = parseFloat(card.querySelector('.field-discount')?.value || 0) || 0;
            const discType = card.querySelector('.field-discount-type')?.value || 'fixed';
            const hsn = card.querySelector('.field-hsn')?.value || '—';
            const uom = card.querySelector('.field-uom')?.value || 'Nos';
            const gstPct = parseFloat(card.querySelector('.field-gst')?.value || 0) || 0;
            const qty = parseInt(card.querySelector('.field-qty')?.value || 0) || 0;
            const mrp = parseFloat(card.querySelector('.field-mrp')?.value || 0) || 0;
            const epr = parseFloat(card.querySelector('.field-epr')?.value || 0) || 0;

            const baseAmount = qty * price;
            let lineDisc = 0;
            if (disc > 0) {
                lineDisc = (discType === 'percentage' || discType === 'percent' || discType === '%')
                    ? (baseAmount * disc) / 100
                    : disc;
            }
            const taxable = Math.max(0, baseAmount - lineDisc);
            const rateAfterDisc = qty > 0 ? (taxable / qty) : Math.max(0, price - lineDisc);

            const gstAmt = Math.round((taxable * (gstPct / 100)) * 100) / 100;
            let cgstAmt = 0, sgstAmt = 0, igstAmt = 0;
            if (intra) {
                cgstAmt = Math.round((gstAmt / 2) * 100) / 100;
                sgstAmt = Math.round((gstAmt - cgstAmt) * 100) / 100;
            } else {
                igstAmt = gstAmt;
            }

            const lineTotal = Math.round((taxable + gstAmt) * 100) / 100;

            totalQty += qty;
            subTotal += baseAmount;
            totalDiscount += lineDisc;
            taxableTotal += taxable;
            totalCgst += cgstAmt;
            totalSgst += sgstAmt;
            totalIgst += igstAmt;
            grandTotal += lineTotal;

            html += `<tr>
                <td>${idx + 1}</td>
                <td>${escHtml(name)}</td>
                <td>₹${price.toFixed(2)}</td>
                <td>${discType === 'percentage' || discType === 'percent' ? disc + '%' : '₹' + disc.toFixed(2)}</td>
                <td>${escHtml(hsn)}</td>
                <td>${escHtml(uom)}</td>
                <td>${gstPct}%</td>
                <td>${qty}</td>
                <td>₹${rateAfterDisc.toFixed(2)}</td>
                <td>₹${gstAmt.toFixed(2)}</td>
                ${intra ? `<td class="col-cgst">₹${cgstAmt.toFixed(2)}</td><td class="col-sgst">₹${sgstAmt.toFixed(2)}</td>` : `<td class="col-igst">₹${igstAmt.toFixed(2)}</td>`}
                <td>₹${epr.toFixed(2)}</td>
                <td>₹${mrp.toFixed(2)}</td>
                <td class="fw-bold">₹${lineTotal.toFixed(2)}</td>
            </tr>`;
        });

        tbody.innerHTML = html;
        document.getElementById('summary-total-qty').textContent = totalQty;
        document.getElementById('summary-sub-total').textContent = formatCurr(subTotal);
        document.getElementById('summary-total-discount').textContent = '-' + formatCurr(totalDiscount);
        document.getElementById('summary-taxable').textContent = formatCurr(taxableTotal);
        document.getElementById('summary-cgst').textContent = formatCurr(totalCgst);
        document.getElementById('summary-sgst').textContent = formatCurr(totalSgst);
        document.getElementById('summary-igst').textContent = formatCurr(totalIgst);
        document.getElementById('summary-grand-total').textContent = formatCurr(grandTotal);
    }

    function updateSupplierDetails() {
        const vendorEl = document.getElementById('vendor_id');
        const detailsBody = document.getElementById('supplier-details-body');
        if (!vendorEl || !detailsBody) return;
        if (!vendorEl.value) {
            detailsBody.innerHTML = '<p class="text-muted mb-0">Select a supplier above to link contact, address, and GST details.</p>';
            return;
        }
        const opt = vendorEl.options[vendorEl.selectedIndex];
        const vState = opt ? (opt.dataset.state || 'N/A').toUpperCase() : 'N/A';
        const intra = isIntraState();
        detailsBody.innerHTML = `<div class="row g-2">
            <div class="col-md-6"><p class="mb-1"><strong>Supplier Name:</strong> ${escHtml(opt.text)}</p></div>
            <div class="col-md-6"><p class="mb-1"><strong>Supplier State:</strong> ${escHtml(vState)} <span class="badge bg-${intra ? 'info' : 'warning'} ms-2">${intra ? 'Intra-State (CGST + SGST)' : 'Inter-State (IGST)'}</span></p></div>
        </div>`;
    }

    let pendingPrItems = [];
    const vendorHint = document.getElementById('vendor-required-hint');
    const btnSaveDraft = document.getElementById('btn-save-draft');
    const btnSubmitPo = document.getElementById('btn-submit-po');
    const poForm = document.getElementById('po-form');

    function isFromPr() {
        return !!poSource && (poSource.value === 'from_pr' || poSource.value === 'from_indent');
    }

    function isDirectPo() {
        return !!poSource && poSource.value === 'direct';
    }

    function hasVendor() {
        return !!getSelectedVendorId();
    }

    function syncVendorGate() {
        const vendorOk = hasVendor();
        const sourceSelected = !!(poSource && poSource.value);

        if (vendorHint) {
            vendorHint.style.display = sourceSelected && !vendorOk ? '' : 'none';
        }

        if (btnAddItem) {
            btnAddItem.disabled = !sourceSelected || !vendorOk;
            btnAddItem.style.display = sourceSelected ? '' : 'none';
        }
        if (btnSaveDraft) btnSaveDraft.disabled = !sourceSelected || !vendorOk;
        if (btnSubmitPo) btnSubmitPo.disabled = !sourceSelected || !vendorOk;

        if (!itemsContainer) return;

        if (itemsEmpty && !itemsContainer.querySelectorAll('.po-item-block').length) {
            itemsEmpty.textContent = 'Add assets to this purchase order.';
            itemsEmpty.style.display = '';
        }
    }

    function syncReadonlyFields() {
        const prSelected = isFromPr() && prSelect && prSelect.value;
        if (window.jQuery) {
            $('#vendor_id').prop('disabled', !!prSelected);
            $('#grn_department').prop('disabled', !!prSelected);
        } else {
            const v = document.getElementById('vendor_id');
            const g = document.getElementById('grn_department');
            if (v) v.disabled = !!prSelected;
            if (g) g.disabled = !!prSelected;
        }
    }

    function applyPendingPrItemsIfReady() {
        if (!isFromPr()) {
            syncVendorGate();
            return;
        }
        if (pendingPrItems.length) {
            renderItems(pendingPrItems);
        }
        syncVendorGate();
    }

    function renumberItems() {
        itemsContainer.querySelectorAll('.po-item-block').forEach(function (block, idx) {
            const title = block.querySelector('.item-title');
            if (title) title.textContent = 'Asset ' + (idx + 1);
        });
    }

    function addItem(data) {
        if (!itemsContainer) return null;
        const block = buildItemBlock(data, rowIndex++);
        itemsContainer.appendChild(block);
        renumberItems();
        updateEmptyState();
        syncVendorGate();
        recalculatePoSummary();
        return block;
    }

    function renderItems(items) {
        if (!itemsContainer) return;
        itemsContainer.innerHTML = '';
        rowIndex = 0;
        if (!items.length) {
            updateEmptyState();
            syncVendorGate();
            recalculatePoSummary();
            return;
        }
        items.forEach(function (item) { addItem(item); });
        recalculatePoSummary();
    }

    function setSupplier(vendorId, vendorName) {
        if (!vendorId) return;
        const value = String(vendorId);
        const el = document.getElementById('vendor_id');
        if (!el) return;

        if (window.jQuery) {
            const $vendor = $('#vendor_id');
            if (!$vendor.find('option[value="' + value + '"]').length && vendorName) {
                $vendor.append(new Option(vendorName, value, true, true));
            }
            $vendor.val(value).trigger('change.select2').trigger('change');
            return;
        }

        if (![...el.options].some(function (opt) { return opt.value === value; }) && vendorName) {
            const option = document.createElement('option');
            option.value = value;
            option.textContent = vendorName;
            el.appendChild(option);
        }
        el.value = value;
        el.dispatchEvent(new Event('change', { bubbles: true }));
    }

    function loadPrHeader(id, options) {
        options = options || {};
        fetch(prDataUrl.replace('__ID__', id), { headers: { Accept: 'application/json' } })
            .then(function (r) {
                if (!r.ok) throw new Error('Failed to load PR data');
                return r.json();
            })
            .then(function (data) {
                if (data.department_id && departmentIdInput) {
                    departmentIdInput.value = data.department_id;
                    if (departmentNameInput) departmentNameInput.value = data.department_name || '';
                    if (departmentNameDisplay) departmentNameDisplay.value = data.department_name || '';
                    if (costCenterInput && data.department_name) costCenterInput.value = data.department_name;
                }
                if (data.grn_store && grnDepartmentSelect) {
                    grnDepartmentSelect.value = data.grn_store;
                    if (window.jQuery) {
                        $('#grn_department').val(data.grn_store).trigger('change');
                    } else {
                        syncDepartmentFromGrnStore();
                    }
                }
                const quotIdEl = document.getElementById('quotation_id');
                if (quotIdEl) quotIdEl.value = data.quotation_id || '';

                pendingPrItems = Array.isArray(data.items) ? data.items : [];

                if (data.vendor_id) {
                    setSupplier(data.vendor_id, data.vendor_name);
                }

                // Always load negotiated purchase rates from PR/RFQ into line items.
                if (!options.skipItems) {
                    renderItems(pendingPrItems);
                }
                syncVendorGate();
                syncReadonlyFields();
            })
            .catch(function () {
                pendingPrItems = [];
                if (!options.skipItems) renderItems([]);
                syncVendorGate();
            });
    }

    function toggleSource() {
        if (!prWrap) return;
        const fromPr = isFromPr();
        prWrap.style.display = fromPr ? '' : 'none';

        if (oldItems.length) {
            if (hasVendor()) {
                renderItems(oldItems);
            } else {
                pendingPrItems = oldItems;
                renderItems([]);
            }
            if (fromPr && prSelect && prSelect.value) loadPrHeader(prSelect.value, { skipItems: true });
            syncVendorGate();
            return;
        }

        if (fromPr && prSelect && prSelect.value) {
            loadPrHeader(prSelect.value);
            return;
        }

        pendingPrItems = [];
        renderItems([]);
        syncVendorGate();
        syncReadonlyFields();
    }

    function escHtml(v) { return String(v || '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;'); }
    function escAttr(v) { return String(v || '').replace(/"/g,'&quot;'); }

    $('#vendor_id').on('change', function () {
        if (hasVendor() && isFromPr() && pendingPrItems.length && itemsContainer && !itemsContainer.querySelectorAll('.po-item-block').length) {
            applyPendingPrItemsIfReady();
        } else {
            syncVendorGate();
        }
        updateSupplierDetails();
        recalculatePoSummary();
    });

    if (poSource) {
        poSource.addEventListener('change', function () {
            const fromPr = isFromPr();
            if (prWrap) prWrap.style.display = fromPr ? '' : 'none';
            pendingPrItems = [];
            if (fromPr && prSelect && prSelect.value) {
                loadPrHeader(prSelect.value);
            } else {
                renderItems([]);
                syncVendorGate();
            }
        });
    }

    if (prSelect) {
        prSelect.addEventListener('change', function () {
            pendingPrItems = [];
            syncReadonlyFields();
            if (prSelect.value) {
                loadPrHeader(prSelect.value);
            } else {
                renderItems([]);
                syncVendorGate();
            }
        });
    }

    if (btnAddItem) {
        btnAddItem.addEventListener('click', function () {
            if (!hasVendor()) {
                syncVendorGate();
                return;
            }
            addItem({});
        });
    }

    if (itemsContainer) {
        itemsContainer.addEventListener('click', function (e) {
            const btn = e.target.closest('.btn-remove-row');
            if (!btn) return;
            btn.closest('.po-item-block')?.remove();
            renumberItems();
            updateEmptyState();
            syncVendorGate();
            recalculatePoSummary();
        });
        itemsContainer.addEventListener('input', function () { recalculatePoSummary(); });
        itemsContainer.addEventListener('change', function () { recalculatePoSummary(); });
    }

    if (poForm) {
        poForm.addEventListener('submit', function (e) {
            if (window.jQuery) {
                $('#vendor_id').prop('disabled', false);
                $('#grn_department').prop('disabled', false);
            } else {
                const v = document.getElementById('vendor_id');
                const g = document.getElementById('grn_department');
                if (v) v.disabled = false;
                if (g) g.disabled = false;
            }

            if (!hasVendor()) {
                e.preventDefault();
                syncVendorGate();
                alert('Select a supplier before creating a purchase order.');
                syncReadonlyFields();
                return false;
            }
            if (isFromPr() && itemsContainer && !itemsContainer.querySelectorAll('.po-item-block').length) {
                e.preventDefault();
                alert('Select a supplier and load the requested indent assets before creating the purchase order.');
                syncReadonlyFields();
                return false;
            }
        });
    }

    syncDepartmentFromGrnStore();

    if ((poSource && poSource.value) || oldItems.length) {
        toggleSource();
    } else {
        if (prWrap) prWrap.style.display = 'none';
        if (btnAddItem) btnAddItem.style.display = 'none';
        renderItems([]);
        syncVendorGate();
    }
    updateSupplierDetails();
    recalculatePoSummary();
    syncReadonlyFields();
});
</script>
@endsection
