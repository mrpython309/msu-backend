@extends('layouts/contentNavbarLayout')

@section('title', isset($indent) ? 'Edit Indent' : 'Create Indent')

@section('page-style')
<style>
    .indent-mode-btn.active {
        color: #fff;
    }
    .indent-mode-btn[data-mode="new"].active {
        background-color: #696cff;
        border-color: #696cff;
    }
    .indent-mode-btn[data-mode="my"].active {
        background-color: #28c76f;
        border-color: #28c76f;
    }
    .item-suggest-dropdown {
        z-index: 1080;
        max-height: 260px;
        overflow-y: auto;
        background: #fff;
        border: 1px solid #d9dee3;
        border-radius: .375rem;
        box-shadow: 0 .25rem .75rem rgba(34, 48, 62, .14);
    }
    .item-suggest-dropdown .item-suggest-option {
        display: block;
        width: 100%;
        text-align: left;
        border: 0;
        border-bottom: 1px solid #f0f2f4;
        background: transparent;
        padding: .55rem .75rem;
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
        color: #384551;
        font-size: .875rem;
    }
    .item-suggest-dropdown .item-suggest-meta {
        display: block;
        color: #a1acb8;
        font-size: .75rem;
        margin-top: .1rem;
    }
    .item-suggest-dropdown .item-suggest-empty,
    .item-suggest-dropdown .item-suggest-loading {
        padding: .65rem .75rem;
        color: #a1acb8;
        font-size: .8125rem;
    }
    .item-row .my-asset-only { display: none; }
    .item-row .new-asset-only { display: none; }
    .item-row .type-new-only { display: none !important; }
    .item-row .type-my-only { display: none !important; }
    #items-container[data-mode="my"] .item-row .my-asset-only { display: block; }
    #items-container[data-mode="new"] .item-row .new-asset-only { display: block; }
    #items-container[data-mode="new"] .item-row .type-new-only { display: block !important; }
    #items-container[data-mode="my"] .item-row .type-my-only { display: block !important; }
    #items-container[data-mode="new"] .item-row .add-asset-name-btn { display: none !important; }
    #items-container[data-mode="my"] .item-row .add-asset-name-btn { display: inline-block; }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">{{ isset($indent) ? 'Edit Indent' : 'Create Indent' }}</h4>
    <a href="{{ route('indents.index') }}" class="btn btn-secondary btn-sm">Back to list</a>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">{{ isset($indent) ? 'Edit Indent' : 'New Indent' }}</h5>
    </div>
    <div class="card-body">
        <form method="POST" action="{{ isset($indent) ? route('indents.update', $indent) : route('indents.store') }}">
            @csrf
            @if (isset($indent))
                @method('PUT')
            @endif

            @php
                $fromStoreId = old('from_store_id', $indent->from_store_id ?? $indent->department_id ?? '');
                $toStoreId = old('to_store_id', $indent->to_store_id ?? '');
                $itemCategoryId = old('item_category_id', $indent->item_category_id ?? '');
                $priorityValue = old('priority', $indent->priority ?? 'Medium');
                $fromStoreName = $departments->firstWhere('id', (int) $fromStoreId)?->department_name ?? '';
                $toStoreName = $toStoreId
                    ? (\App\Models\Department::find($toStoreId)?->department_name ?? '')
                    : '';
                $itemCategoryName = $categories->firstWhere('id', (int) $itemCategoryId)?->category_name ?? '';
            @endphp

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label class="form-label" for="from_store_display">From Store <span class="text-danger">*</span></label>
                    <input type="hidden" name="from_store_id" id="from_store_id" value="{{ $fromStoreId }}" required>
                    <input type="hidden" name="department_id" id="department_id_mirror" value="{{ $fromStoreId }}">
                    <input type="text"
                           class="form-control @error('from_store_id') is-invalid @enderror"
                           id="from_store_display"
                           value="{{ $fromStoreName }}"
                           placeholder="Type to search..."
                           autocomplete="off"
                           required>
                    @error('from_store_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label" for="to_store_display">To Store</label>
                    <input type="hidden" name="to_store_id" id="to_store_id" value="{{ $toStoreId }}">
                    <input type="text"
                           class="form-control @error('to_store_id') is-invalid @enderror"
                           id="to_store_display"
                           value="{{ $toStoreName }}"
                           placeholder="Type to search..."
                           autocomplete="off">
                    @error('to_store_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="priority">Priority <span class="text-danger">*</span></label>
                    <select class="form-select @error('priority') is-invalid @enderror" id="priority" name="priority" required>
                        @foreach (['Low', 'Medium', 'High', 'Urgent'] as $priority)
                            <option value="{{ $priority }}" {{ $priorityValue === $priority ? 'selected' : '' }}>
                                {{ $priority }}
                            </option>
                        @endforeach
                    </select>
                    @error('priority')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="tag">Tag</label>
                    <input type="text" class="form-control @error('tag') is-invalid @enderror" id="tag" name="tag"
                           value="{{ old('tag', $indent->tag ?? '') }}" placeholder="Type to search..." autocomplete="off">
                    @error('tag')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3">
                    <label class="form-label" for="required_date">Required Date</label>
                    <input type="date"
                           class="form-control @error('required_date') is-invalid @enderror"
                           id="required_date"
                           name="required_date"
                           value="{{ old('required_date', isset($indent) && $indent->required_date ? \Carbon\Carbon::parse($indent->required_date)->format('Y-m-d') : '') }}">
                    @error('required_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" value="1" id="is_urgent" name="is_urgent"
                               @checked(old('is_urgent', $indent->is_urgent ?? false))>
                        <label class="form-check-label" for="is_urgent">Is Urgent</label>
                    </div>
                </div>
            </div>

            <hr class="my-4">
            <div class="mb-4">
                <label class="form-label" for="purpose">Purpose / Reason <span class="text-danger">*</span></label>
                <textarea class="form-control @error('purpose') is-invalid @enderror" id="purpose" name="purpose" rows="2"
                          placeholder="Enter purpose/reason..." required>{{ old('purpose', $indent->purpose ?? '') }}</textarea>
                @error('purpose')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <hr class="my-4">
            @php
                $oldItems = old('items');
                if ($oldItems === null) {
                    $oldItems = isset($indent)
                        ? $indent->items->map(fn ($item) => [
                            'item_mode' => 'new',
                            'item_name' => $item->item_name,
                            'category_id' => $item->category_id,
                            'category_name' => $item->category?->category_name
                                ?? $categories->firstWhere('id', $item->category_id)?->category_name
                                ?? '',
                            'item_type' => $item->item_type?->value ?? ($item->getRawOriginal('item_type') ?: 'asset'),
                            'composition' => $item->composition,
                            'quantity' => $item->quantity,
                            'unit' => $item->unit,
                            'estimated_price' => $item->estimated_price,
                            'description' => $item->description,
                            'manufacturer' => $item->manufacturer,
                            'item_remarks' => $item->item_remarks,
                        ])->values()->all()
                        : [[
                            'item_mode' => 'new',
                            'item_name' => '',
                            'category_id' => '',
                            'category_name' => '',
                            'item_type' => 'asset',
                            'composition' => '',
                            'quantity' => '1',
                            'unit' => 'Nos',
                            'estimated_price' => '0',
                            'description' => '',
                            'manufacturer' => '',
                            'item_remarks' => '',
                        ]];
                }
                $globalMode = collect($oldItems)->contains(fn ($item) => ($item['item_mode'] ?? 'new') === 'my') ? 'my' : 'new';
            @endphp

            <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
                <h6 class="fw-bold mb-0">Indent Items</h6>
                <button type="button" class="btn btn-sm btn-secondary" id="add-item-btn">
                    <i class="bx bx-plus"></i> Add Item
                </button>
            </div>
            <div class="d-flex align-items-center gap-3 mb-3 flex-wrap">
                <div class="btn-group" role="group" aria-label="Item source" id="indent-mode-toggle">
                    <button type="button" class="btn btn-outline-primary indent-mode-btn {{ $globalMode === 'new' ? 'active' : '' }}" data-mode="new">New Assets</button>
                    <button type="button" class="btn btn-outline-success indent-mode-btn {{ $globalMode === 'my' ? 'active' : '' }}" data-mode="my">My Asset</button>
                </div>
            </div>
            @error('items')<div class="text-danger mb-2">{{ $message }}</div>@enderror

            <div id="items-container" data-mode="{{ $globalMode }}">
                @foreach ($oldItems as $index => $item)
                @php
                    $itemTypeValue = $item['item_type'] ?? 'asset';
                    $isMy = $globalMode === 'my';
                    $typeDisplay = $isMy
                        ? ($item['composition'] ?? $item['asset_type_name'] ?? '')
                        : (($itemTypeValue === 'consumable') ? 'Consumable' : 'Asset');
                    $categoryName = $item['category_name']
                        ?? ($categories->firstWhere('id', (int) ($item['category_id'] ?? 0))->category_name ?? '');
                @endphp
                <div class="border rounded p-3 mb-3 item-row" data-mode="{{ $globalMode }}">
                    <div class="d-flex justify-content-end mb-2">
                        <button type="button" class="btn btn-outline-danger btn-sm remove-item-btn" {{ $index === 0 ? 'disabled' : '' }}>
                            <i class="bx bx-trash"></i>
                        </button>
                    </div>
                    <input type="hidden" name="items[{{ $index }}][item_mode]" class="item-mode-input" value="{{ $globalMode }}">
                    <input type="hidden" name="items[{{ $index }}][estimated_price]" class="field-price" value="{{ $item['estimated_price'] ?? 0 }}">
                    <input type="hidden" name="items[{{ $index }}][item_type]" class="field-item-type" value="{{ $itemTypeValue }}">
                    <input type="hidden" name="items[{{ $index }}][composition]" class="field-composition" value="{{ $item['composition'] ?? '' }}">

                    <div class="row g-2 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Asset Name <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text"
                                       name="items[{{ $index }}][item_name]"
                                       class="form-control field-item-name @error('items.'.$index.'.item_name') is-invalid @enderror"
                                       value="{{ $item['item_name'] ?? '' }}"
                                       placeholder="{{ $isMy ? 'Type to search your assets...' : 'Enter or search asset name' }}"
                                       autocomplete="off"
                                       required>
                                <button type="button"
                                        class="btn btn-outline-primary add-asset-name-btn {{ $globalMode === 'new' ? 'd-none' : '' }}"
                                        title="Add this name to Asset Name catalog"
                                        disabled>+</button>
                            </div>
                            @error('items.'.$index.'.item_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Asset Type <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control field-item-type-new type-new-only bg-light"
                                   value="{{ ! $isMy ? ($item['composition'] ?? '') : '' }}"
                                   placeholder="Auto-filled"
                                   readonly
                                   autocomplete="off"
                                   @disabled($isMy)>
                            <input type="text"
                                   class="form-control field-item-type-display type-my-only bg-light"
                                   value="{{ $isMy ? $typeDisplay : '' }}"
                                   placeholder="Auto-filled"
                                   readonly
                                   autocomplete="off"
                                   @disabled(! $isMy)>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Asset Category</label>
                            <input type="hidden" name="items[{{ $index }}][category_id]" class="field-category" value="{{ $item['category_id'] ?? '' }}">
                            <input type="text"
                                   class="form-control field-category-display bg-light"
                                   value="{{ $categoryName }}"
                                   placeholder="Auto-filled"
                                   readonly
                                   autocomplete="off">
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">Qty <span class="text-danger">*</span></label>
                            <input type="text"
                                   name="items[{{ $index }}][quantity]"
                                   class="form-control field-quantity"
                                   value="{{ $item['quantity'] ?? '1' }}"
                                   placeholder="Type..."
                                   inputmode="numeric"
                                   autocomplete="off"
                                   required>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Unit</label>
                            <input type="text"
                                   name="items[{{ $index }}][unit]"
                                   class="form-control field-unit bg-light"
                                   value="{{ $item['unit'] ?? 'Nos' }}"
                                   placeholder="Auto-filled"
                                   readonly
                                   autocomplete="off">
                        </div>
                    </div>

                    <div class="new-asset-only mt-3">
                        <label class="form-label">Remarks</label>
                        <textarea name="items[{{ $index }}][item_remarks]"
                                  class="form-control field-remarks-new"
                                  rows="2"
                                  placeholder="Remarks for new asset"
                                  @disabled($isMy)>{{ $item['item_remarks'] ?? '' }}</textarea>
                    </div>

                    <div class="my-asset-only mt-3">
                        <div class="row g-2">
                            <div class="col-md-3">
                                <label class="form-label">Asset Code</label>
                                <input type="text" class="form-control field-asset-code" value="" placeholder="Auto-filled" readonly>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Serial No</label>
                                <input type="text" class="form-control field-serial-no" value="" placeholder="Auto-filled" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Remarks</label>
                                <textarea name="items[{{ $index }}][item_remarks]"
                                          class="form-control field-remarks-my"
                                          rows="2"
                                          placeholder="Remarks for my asset"
                                          @disabled(! $isMy)>{{ $item['item_remarks'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <div class="text-end mt-3">
                <button type="submit" name="action" value="draft" class="btn btn-outline-secondary">Save Draft</button>
                <button type="submit" name="action" value="submit" class="btn btn-primary">Submit for Approval</button>
            </div>
        </form>
    </div>
</div>
@endsection

@section('page-script')
<script>
document.addEventListener('DOMContentLoaded', function () {
    let itemIndex = {{ count($oldItems) }};
    const categories = @json($categories->map(fn ($c) => ['id' => $c->id, 'name' => $c->category_name])->values());
    const container = document.getElementById('items-container');
    const itemSuggestUrl = @json(route('indents.items.suggest')); // item master catalog suggestions for New Assets mode
    const myAssetSearchUrl = @json(route('indents.assets.search'));
    const fieldSuggestUrl = @json(route('indents.fields.suggest'));
    const assetNameQuickStoreUrl = @json(route('asset-names.quick-store'));
    const csrfToken = @json(csrf_token());

    function escHtml(value) {
        return String(value == null ? '' : value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }

    function categoryOptions(selected) {
        return categories.map(function (category) {
            const isSelected = String(selected || '') === String(category.id) ? ' selected' : '';
            return '<option value="' + category.id + '"' + isSelected + '>' + category.name + '</option>';
        }).join('');
    }

    function refreshRemoveButtons() {
        const rows = container.querySelectorAll('.item-row');
        rows.forEach(function (row) {
            const btn = row.querySelector('.remove-item-btn');
            if (btn) btn.disabled = rows.length === 1;
        });
    }

    function getGlobalMode() {
        return container.dataset.mode === 'my' ? 'my' : 'new';
    }

    function syncPlusButtons() {
        container.querySelectorAll('.item-row').forEach(function (row) {
            updatePlusButtonState(row);
        });
    }

    function setGlobalMode(mode) {
        container.dataset.mode = mode;
        document.querySelectorAll('#indent-mode-toggle .indent-mode-btn').forEach(function (btn) {
            btn.classList.toggle('active', btn.dataset.mode === mode);
        });
        container.querySelectorAll('.item-row').forEach(function (row) {
            row.dataset.mode = mode;
            const input = row.querySelector('.item-mode-input');
            if (input) input.value = mode;
            const nameInput = row.querySelector('.field-item-name');
            if (nameInput) {
                nameInput.placeholder = mode === 'my'
                    ? 'Type to search your assets...'
                    : 'Enter or search asset name';
                nameInput.setAttribute('autocomplete', 'off');
                nameInput.dataset.allowSuggest = '1';
            }
            const typeDisplay = row.querySelector('.field-item-type-display');
            const typeNew = row.querySelector('.field-item-type-new');
            const typeHidden = row.querySelector('.field-item-type');
            const composition = row.querySelector('.field-composition');
            const categoryHidden = row.querySelector('.field-category');
            const categoryDisplay = row.querySelector('.field-category-display');
            const remarksNew = row.querySelector('.field-remarks-new');
            const remarksMy = row.querySelector('.field-remarks-my');
            if (remarksNew) remarksNew.disabled = mode === 'my';
            if (remarksMy) remarksMy.disabled = mode !== 'my';
            if (typeNew) {
                typeNew.disabled = mode === 'my';
                typeNew.placeholder = mode === 'new' ? 'Enter asset type' : 'Type to search...';
            }
            if (typeDisplay) {
                typeDisplay.disabled = mode !== 'my';
                typeDisplay.placeholder = 'Type to search...';
            }
            if (categoryDisplay) {
                categoryDisplay.disabled = false;
                categoryDisplay.placeholder = mode === 'my' ? 'Type to search...' : 'Enter category';
                categoryDisplay.dataset.allowSuggest = '1';
            }
            if (mode === 'my') {
                if (typeHidden) typeHidden.value = 'asset';
                if (typeDisplay && (!typeDisplay.value || typeDisplay.value === 'Asset' || typeDisplay.value === 'Consumable')) {
                    typeDisplay.value = composition && composition.value ? composition.value : '';
                }
            } else {
                if (typeHidden) typeHidden.value = 'asset';
                if (typeNew && composition && typeNew.value.trim() !== '') {
                    composition.value = typeNew.value.trim();
                }
            }
        });
        syncPlusButtons();
    }

    function nameExistsInResults(name, results) {
        const needle = String(name || '').trim().toLowerCase();
        if (!needle) return false;
        return (results || []).some(function (item) {
            return String(item.item_name || item.value || item.label || '').trim().toLowerCase() === needle;
        });
    }

    function updatePlusButtonState(row, results) {
        const plusBtn = row.querySelector('.add-asset-name-btn');
        const nameInput = row.querySelector('.field-item-name');
        if (!plusBtn || !nameInput) return;

        if (getGlobalMode() === 'new') {
            plusBtn.classList.add('d-none');
            plusBtn.disabled = true;
            return;
        }

        plusBtn.classList.remove('d-none');
        const name = nameInput.value.trim();
        const exists = nameExistsInResults(name, results != null ? results : (row._lastSuggestResults || []));
        plusBtn.disabled = !(name.length > 0 && !exists);
    }

    function fillRowFromSuggestion(row, item) {
        const name = row.querySelector('.field-item-name');
        const category = row.querySelector('.field-category');
        const categoryDisplay = row.querySelector('.field-category-display');
        const itemType = row.querySelector('.field-item-type');
        const itemTypeDisplay = row.querySelector('.field-item-type-display');
        const composition = row.querySelector('.field-composition');
        const unit = row.querySelector('.field-unit');
        const quantity = row.querySelector('.field-quantity');
        const assetCode = row.querySelector('.field-asset-code');
        const serialNo = row.querySelector('.field-serial-no');
        const remarks = row.querySelector(getGlobalMode() === 'my' ? '.field-remarks-my' : '.field-remarks-new');
        const remarksNew = row.querySelector('.field-remarks-new');
        const remarksMy = row.querySelector('.field-remarks-my');

        if (name && item.item_name != null) name.value = item.item_name;
        if (category) category.value = item.category_id || '';
        if (categoryDisplay) {
            categoryDisplay.value = item.category_name
                || (item.category_id
                    ? ((categories.find(function (c) { return String(c.id) === String(item.category_id); }) || {}).name || '')
                    : '');
        }

        if (itemType) itemType.value = item.item_type || 'asset';
        const typeName = item.asset_type_name || item.composition || '';
        if (itemTypeDisplay) itemTypeDisplay.value = typeName;
        const typeNew = row.querySelector('.field-item-type-new');
        if (typeNew) typeNew.value = typeName;
        if (composition) composition.value = typeName;

        if (unit) unit.value = item.unit || 'Nos';
        if (quantity) quantity.value = item.quantity != null ? item.quantity : (quantity.value || 1);
        if (assetCode) assetCode.value = item.asset_code || '';
        if (serialNo) serialNo.value = item.serial_no || '';
        if (item.item_remarks != null) {
            if (remarks) remarks.value = item.item_remarks;
            if (remarksNew) remarksNew.value = item.item_remarks;
            if (remarksMy) remarksMy.value = item.item_remarks;
        }

        row._lastSuggestResults = row._lastSuggestResults || [];
        if (item.item_name) {
            const already = nameExistsInResults(item.item_name, row._lastSuggestResults);
            if (!already) {
                row._lastSuggestResults = row._lastSuggestResults.concat([item]);
            }
        }
        updatePlusButtonState(row, row._lastSuggestResults);
    }

    function createDropdown() {
        const dropdown = document.createElement('div');
        dropdown.className = 'item-suggest-dropdown';
        dropdown.setAttribute('role', 'listbox');
        document.body.appendChild(dropdown);
        return dropdown;
    }

    function positionDropdown(dropdown, inputEl) {
        const rect = inputEl.getBoundingClientRect();
        dropdown.style.position = 'fixed';
        dropdown.style.left = rect.left + 'px';
        dropdown.style.width = Math.max(rect.width, 280) + 'px';
        dropdown.style.top = (rect.bottom + 4) + 'px';
        dropdown.style.right = 'auto';
    }

    function initSuggestions(inputEl, options) {
        if (!inputEl || inputEl.dataset.suggestionsReady === '1') return;
        inputEl.dataset.suggestionsReady = '1';

        let debounceTimer = null;
        let requestToken = 0;
        let dropdown = null;
        let activeIndex = -1;
        let currentItems = [];

        function hideDropdown() {
            if (dropdown) {
                dropdown.remove();
                dropdown = null;
            }
            activeIndex = -1;
            currentItems = [];
        }

        function ensureDropdown() {
            if (!dropdown) dropdown = createDropdown();
            positionDropdown(dropdown, inputEl);
            return dropdown;
        }

        function setActiveOption(index) {
            if (!dropdown) return;
            const opts = dropdown.querySelectorAll('.item-suggest-option');
            opts.forEach(function (el, i) {
                el.classList.toggle('is-active', i === index);
            });
            if (index >= 0 && opts[index]) opts[index].scrollIntoView({ block: 'nearest' });
            activeIndex = index;
        }

        function selectItem(item) {
            if (!item) return;
            if (typeof options.onSelect === 'function') {
                options.onSelect(item);
            } else if (item.value != null) {
                inputEl.value = item.value;
            } else if (item.item_name != null) {
                inputEl.value = item.item_name;
            }
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
                panel.innerHTML = '<div class="item-suggest-empty">' + escHtml(options.emptyText || 'No matches found') + '</div>';
                return;
            }

            currentItems.forEach(function (item, index) {
                const btn = document.createElement('button');
                btn.type = 'button';
                btn.className = 'item-suggest-option';
                btn.setAttribute('role', 'option');
                const title = item.item_name || item.label || item.value || '';
                const meta = item.meta || '';
                btn.innerHTML =
                    '<span class="item-suggest-title">' + escHtml(title) + '</span>' +
                    (meta ? '<span class="item-suggest-meta">' + escHtml(meta) + '</span>' : '');
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
            if (typeof options.shouldFetch === 'function' && !options.shouldFetch()) {
                hideDropdown();
                return;
            }

            const token = ++requestToken;
            renderSuggestions([], 'loading');

            fetch(options.buildUrl(term), {
                headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                credentials: 'same-origin'
            })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    if (token !== requestToken) return;
                    const results = data.results || [];
                    if (typeof options.onResults === 'function') options.onResults(results);
                    renderSuggestions(results);
                })
                .catch(function () {
                    if (token !== requestToken) return;
                    if (typeof options.onResults === 'function') options.onResults([]);
                    renderSuggestions([]);
                });
        }

        inputEl.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                fetchSuggestions(inputEl.value.trim());
            }, 220);
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
            } else if (e.key === 'Enter') {
                if (activeIndex >= 0 && currentItems[activeIndex]) {
                    e.preventDefault();
                    selectItem(currentItems[activeIndex]);
                }
            } else if (e.key === 'Escape') {
                hideDropdown();
            }
        });

        inputEl.addEventListener('blur', function () {
            setTimeout(hideDropdown, 150);
        });

        window.addEventListener('scroll', function () {
            if (dropdown) positionDropdown(dropdown, inputEl);
        }, true);
        window.addEventListener('resize', function () {
            if (dropdown) positionDropdown(dropdown, inputEl);
        });
    }

    function quickAddAssetName(row) {
        const nameInput = row.querySelector('.field-item-name');
        const plusBtn = row.querySelector('.add-asset-name-btn');
        const name = (nameInput && nameInput.value || '').trim();
        if (!name || !plusBtn || plusBtn.disabled) return;

        plusBtn.disabled = true;

        fetch(assetNameQuickStoreUrl, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            },
            credentials: 'same-origin',
            body: new URLSearchParams({
                _token: csrfToken,
                name: name,
                status: 'active'
            }).toString()
        })
            .then(function (r) { return r.json().then(function (data) { return { ok: r.ok, data: data }; }); })
            .then(function (res) {
                if (!res.ok || !res.data || res.data.success === false) {
                    updatePlusButtonState(row);
                    return;
                }

                const savedName = (res.data.data && res.data.data.name) ? res.data.data.name : name;
                nameInput.value = savedName;
                row._lastSuggestResults = (row._lastSuggestResults || []).concat([{
                    item_name: savedName,
                    item_type: 'asset',
                    unit: 'Nos',
                    meta: 'Asset catalog'
                }]);
                updatePlusButtonState(row, row._lastSuggestResults);
            })
            .catch(function () {
                updatePlusButtonState(row);
            });
    }

    function wireRow(row) {
        if (!row || row.dataset.wired === '1') return;
        row.dataset.wired = '1';
        row._lastSuggestResults = [];

        const nameInput = row.querySelector('.field-item-name');
        const unitInput = row.querySelector('.field-unit');
        const quantityInput = row.querySelector('.field-quantity');
        const typeDisplay = row.querySelector('.field-item-type-display');
        const typeHidden = row.querySelector('.field-item-type');
        const composition = row.querySelector('.field-composition');
        const categoryDisplay = row.querySelector('.field-category-display');
        const categoryHidden = row.querySelector('.field-category');
        const plusBtn = row.querySelector('.add-asset-name-btn');

        initSuggestions(nameInput, {
            emptyText: 'No matching assets found',
            shouldFetch: function () {
                return nameInput.dataset.allowSuggest !== '0';
            },
            buildUrl: function (term) {
                if (getGlobalMode() === 'new') {
                    return itemSuggestUrl + '?q=' + encodeURIComponent(term || '') + '&limit=500';
                }
                return myAssetSearchUrl + '?q=' + encodeURIComponent(term || '') + '&limit=20';
            },
            onResults: function (results) {
                row._lastSuggestResults = results || [];
                updatePlusButtonState(row, results);
            },
            onSelect: function (item) {
                fillRowFromSuggestion(row, item);
            }
        });
        if (nameInput) {
            nameInput.dataset.allowSuggest = '1';
            nameInput.setAttribute('autocomplete', 'off');
        }

        initSuggestions(typeDisplay, {
            emptyText: 'No matching asset type',
            shouldFetch: function () {
                return getGlobalMode() === 'my';
            },
            buildUrl: function (term) {
                return fieldSuggestUrl + '?field=asset_type&q=' + encodeURIComponent(term || '') + '&limit=20';
            },
            onSelect: function (item) {
                if (typeHidden) typeHidden.value = 'asset';
                if (composition) composition.value = item.value || item.label || '';
                if (typeDisplay) typeDisplay.value = item.label || item.value || '';
            }
        });

        const typeNew = row.querySelector('.field-item-type-new');
        if (typeNew) {
            typeNew.addEventListener('input', function () {
                if (getGlobalMode() !== 'new') return;
                if (typeHidden) typeHidden.value = 'asset';
                if (composition) composition.value = typeNew.value.trim();
            });
        }

        initSuggestions(categoryDisplay, {
            emptyText: 'No matching category',
            shouldFetch: function () {
                return categoryDisplay.dataset.allowSuggest !== '0';
            },
            buildUrl: function (term) {
                return fieldSuggestUrl + '?field=category&q=' + encodeURIComponent(term || '') + '&limit=20';
            },
            onSelect: function (item) {
                if (categoryHidden) categoryHidden.value = item.value || item.category_id || '';
                if (categoryDisplay) categoryDisplay.value = item.label || '';
            }
        });
        if (categoryDisplay) {
            categoryDisplay.dataset.allowSuggest = '1';
        }

        initSuggestions(quantityInput, {
            emptyText: 'No quantity suggestions',
            buildUrl: function (term) {
                return fieldSuggestUrl + '?field=quantity&q=' + encodeURIComponent(term || '') + '&limit=15';
            },
            onSelect: function (item) {
                quantityInput.value = item.value || item.label || '';
            }
        });

        initSuggestions(unitInput, {
            emptyText: 'No unit suggestions',
            buildUrl: function (term) {
                return fieldSuggestUrl + '?field=unit&q=' + encodeURIComponent(term || '') + '&limit=15';
            },
            onSelect: function (item) {
                unitInput.value = item.value || item.label || '';
            }
        });

        if (nameInput) {
            nameInput.addEventListener('input', function () {
                updatePlusButtonState(row, row._lastSuggestResults);
            });
        }

        if (typeDisplay && typeHidden) {
            typeDisplay.addEventListener('input', function () {
                if (getGlobalMode() !== 'my') return;
                if (composition) composition.value = typeDisplay.value.trim();
                typeHidden.value = 'asset';
            });
            typeDisplay.addEventListener('focus', function () {
                if (getGlobalMode() !== 'my') {
                    document.querySelectorAll('.item-suggest-dropdown').forEach(function (el) { el.remove(); });
                }
            });
        }

        if (categoryDisplay && categoryHidden) {
            categoryDisplay.addEventListener('input', function () {
                if (getGlobalMode() !== 'my') {
                    // Free-text in New Assets — clear catalog id so typed text is not treated as a selection.
                    categoryHidden.value = '';
                    return;
                }
                if (!categoryDisplay.value.trim()) {
                    categoryHidden.value = '';
                }
            });
            categoryDisplay.addEventListener('focus', function () {
                if (getGlobalMode() !== 'my') {
                    document.querySelectorAll('.item-suggest-dropdown').forEach(function (el) { el.remove(); });
                }
            });
        }

        if (plusBtn) {
            plusBtn.addEventListener('click', function () {
                quickAddAssetName(row);
            });
        }

        updatePlusButtonState(row);
    }

    function buildItemRowHtml(index) {
        const mode = getGlobalMode();
        return '' +
            '<div class="d-flex justify-content-end mb-2">' +
                '<button type="button" class="btn btn-outline-danger btn-sm remove-item-btn"><i class="bx bx-trash"></i></button>' +
            '</div>' +
            '<input type="hidden" name="items[' + index + '][item_mode]" class="item-mode-input" value="' + mode + '">' +
            '<input type="hidden" name="items[' + index + '][estimated_price]" class="field-price" value="0">' +
            '<input type="hidden" name="items[' + index + '][item_type]" class="field-item-type" value="asset">' +
            '<input type="hidden" name="items[' + index + '][composition]" class="field-composition" value="">' +
            '<div class="row g-2 align-items-end">' +
                '<div class="col-md-4">' +
                    '<label class="form-label">Asset Name <span class="text-danger">*</span></label>' +
                    '<div class="input-group">' +
                        '<input type="text" name="items[' + index + '][item_name]" class="form-control field-item-name" placeholder="' + (mode === 'my' ? 'Type to search your assets...' : 'Enter or search asset name') + '" autocomplete="off" data-allow-suggest="1" required>' +
                        '<button type="button" class="btn btn-outline-primary add-asset-name-btn' + (mode === 'new' ? ' d-none' : '') + '" title="Add this name to Asset Name catalog" disabled>+</button>' +
                    '</div>' +
                '</div>' +
                '<div class="col-md-2">' +
                    '<label class="form-label">Asset Type <span class="text-danger">*</span></label>' +
                    '<input type="text" class="form-control field-item-type-new type-new-only bg-light" value="" placeholder="Auto-filled" readonly autocomplete="off"' + (mode === 'my' ? ' disabled' : '') + '>' +
                    '<input type="text" class="form-control field-item-type-display type-my-only bg-light" value="" placeholder="Auto-filled" readonly autocomplete="off"' + (mode === 'new' ? ' disabled' : '') + '>' +
                '</div>' +
                '<div class="col-md-3">' +
                    '<label class="form-label">Asset Category</label>' +
                    '<input type="hidden" name="items[' + index + '][category_id]" class="field-category" value="">' +
                    '<input type="text" class="form-control field-category-display bg-light" value="" placeholder="Auto-filled" readonly autocomplete="off">' +
                '</div>' +
                '<div class="col-md-1">' +
                    '<label class="form-label">Qty <span class="text-danger">*</span></label>' +
                    '<input type="text" name="items[' + index + '][quantity]" class="form-control field-quantity" value="1" placeholder="Type..." inputmode="numeric" autocomplete="off" required>' +
                '</div>' +
                '<div class="col-md-2">' +
                    '<label class="form-label">Unit</label>' +
                    '<input type="text" name="items[' + index + '][unit]" class="form-control field-unit bg-light" value="Nos" placeholder="Auto-filled" readonly autocomplete="off">' +
                '</div>' +
            '</div>' +
            '<div class="new-asset-only mt-3">' +
                '<label class="form-label">Remarks</label>' +
                '<textarea name="items[' + index + '][item_remarks]" class="form-control field-remarks-new" rows="2" placeholder="Remarks for new asset"' + (mode === 'my' ? ' disabled' : '') + '></textarea>' +
            '</div>' +
            '<div class="my-asset-only mt-3">' +
                '<div class="row g-2">' +
                    '<div class="col-md-3">' +
                        '<label class="form-label">Asset Code</label>' +
                        '<input type="text" class="form-control field-asset-code" value="" placeholder="Auto-filled" readonly>' +
                    '</div>' +
                    '<div class="col-md-3">' +
                        '<label class="form-label">Serial No</label>' +
                        '<input type="text" class="form-control field-serial-no" value="" placeholder="Auto-filled" readonly>' +
                    '</div>' +
                    '<div class="col-md-6">' +
                        '<label class="form-label">Remarks</label>' +
                        '<textarea name="items[' + index + '][item_remarks]" class="form-control field-remarks-my" rows="2" placeholder="Remarks for my asset"' + (mode === 'new' ? ' disabled' : '') + '></textarea>' +
                    '</div>' +
                '</div>' +
            '</div>';
    }

    document.getElementById('add-item-btn').addEventListener('click', function () {
        const row = document.createElement('div');
        row.className = 'border rounded p-3 mb-3 item-row';
        row.dataset.mode = getGlobalMode();
        row.innerHTML = buildItemRowHtml(itemIndex);
        container.appendChild(row);
        wireRow(row);
        itemIndex++;
        refreshRemoveButtons();
    });

    document.getElementById('indent-mode-toggle').addEventListener('click', function (e) {
        const modeBtn = e.target.closest('.indent-mode-btn');
        if (!modeBtn) return;
        setGlobalMode(modeBtn.dataset.mode);
    });

    container.addEventListener('click', function (e) {
        const removeBtn = e.target.closest('.remove-item-btn');
        if (!removeBtn || removeBtn.disabled) return;
        removeBtn.closest('.item-row').remove();
        refreshRemoveButtons();
    });

    container.querySelectorAll('.item-row').forEach(wireRow);
    refreshRemoveButtons();
    syncPlusButtons();

    function wireHeaderField(displayEl, hiddenEl, field, onSelect) {
        if (!displayEl) return;
        initSuggestions(displayEl, {
            emptyText: 'No matches found',
            buildUrl: function (term) {
                return fieldSuggestUrl + '?field=' + encodeURIComponent(field) + '&q=' + encodeURIComponent(term || '') + '&limit=20';
            },
            onSelect: function (item) {
                if (typeof onSelect === 'function') {
                    onSelect(item);
                    return;
                }
                if (hiddenEl) hiddenEl.value = item.value || '';
                displayEl.value = item.label || item.value || '';
            }
        });
        if (hiddenEl) {
            displayEl.addEventListener('input', function () {
                if (!displayEl.value.trim()) {
                    hiddenEl.value = '';
                }
            });
        }
    }

    const fromStoreHidden = document.getElementById('from_store_id');
    const fromStoreDisplay = document.getElementById('from_store_display');
    const deptMirror = document.getElementById('department_id_mirror');
    const toStoreHidden = document.getElementById('to_store_id');
    const toStoreDisplay = document.getElementById('to_store_display');
    const prioritySelect = document.getElementById('priority');
    const tagInput = document.getElementById('tag');

    wireHeaderField(fromStoreDisplay, fromStoreHidden, 'store', function (item) {
        fromStoreHidden.value = item.value || '';
        fromStoreDisplay.value = item.label || '';
        if (deptMirror) deptMirror.value = item.value || '';
    });

    wireHeaderField(toStoreDisplay, toStoreHidden, 'all_store', function (item) {
        toStoreHidden.value = item.value || '';
        toStoreDisplay.value = item.label || '';
    });

    if (prioritySelect) {
        prioritySelect.addEventListener('change', function () {
            if (String(prioritySelect.value).toLowerCase() === 'urgent') {
                const urgent = document.getElementById('is_urgent');
                if (urgent) urgent.checked = true;
            }
        });
    }

    wireHeaderField(tagInput, null, 'tag', function (item) {
        tagInput.value = item.value || item.label || '';
    });

    if (fromStoreDisplay && deptMirror && fromStoreHidden) {
        fromStoreDisplay.addEventListener('input', function () {
            if (!fromStoreDisplay.value.trim()) {
                fromStoreHidden.value = '';
                deptMirror.value = '';
            }
        });
    }
});
</script>
@endsection
