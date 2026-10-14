@extends('layouts/contentNavbarLayout')

@section('title', 'Item Stock')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h4 class="fw-bold mb-0">
            <span class="text-muted fw-light">Inventory /</span> Item Stock
        </h4>
        <div class="d-flex gap-2">
            @can('item-stocks:view')
            <a href="{{ route('item-stocks.export', request()->query()) }}" class="btn btn-outline-success">
                <i class="bx bx-export me-1"></i> Excel
            </a>
            <a href="{{ route('item-stocks.export-pdf', request()->query()) }}" class="btn btn-outline-danger">
                <i class="bx bxs-file-pdf me-1"></i> PDF
            </a>
            @endcan
        </div>
    </div>
    <!-- Summary Cards -->
    <div class="row mb-2">
        <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
            <div class="card card-border-shadow-primary h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-1">
                        <div class="avatar me-2">
                            <span class="avatar-initial rounded bg-label-primary"><i class="bx bx-box"></i></span>
                        </div>
                        <h4 class="ms-1 mb-0">{{ number_format($totalOpening, 3) }}</h4>
                    </div>
                    <p class="mb-0 text-heading fw-medium">Opening Stock</p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3 mb-3 mb-lg-0">
            <div class="card card-border-shadow-success h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-1">
                        <div class="avatar me-2">
                            <span class="avatar-initial rounded bg-label-success"><i class="bx bx-down-arrow-alt"></i></span>
                        </div>
                        <h4 class="ms-1 mb-0">{{ number_format($totalReceived, 3) }}</h4>
                    </div>
                    <p class="mb-0 text-heading fw-medium">Received Stocks</p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3 mb-3 mb-sm-0">
            <div class="card card-border-shadow-danger h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-1">
                        <div class="avatar me-2">
                            <span class="avatar-initial rounded bg-label-danger"><i class="bx bx-up-arrow-alt"></i></span>
                        </div>
                        <h4 class="ms-1 mb-0">{{ number_format($totalIssued, 3) }}</h4>
                    </div>
                    <p class="mb-0 text-heading fw-medium">Issued Stock</p>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card card-border-shadow-info h-100">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center mb-1">
                        <div class="avatar me-2">
                            <span class="avatar-initial rounded bg-label-info"><i class="bx bx-cube"></i></span>
                        </div>
                        <h4 class="ms-1 mb-0">{{ number_format($totalClosing, 3) }}</h4>
                    </div>
                    <p class="mb-0 text-heading fw-medium">Closing Stock</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="card mb-3">
        <div class="card-body py-3">
            <form action="{{ route('item-stocks.index') }}" method="GET" class="row g-2 align-items-end flex-nowrap stock-filters-row">
                <div class="col">
                    <label class="form-label mb-1" for="item_code">Item Code</label>
                    <input type="text" id="item_code" name="item_code" class="form-control form-control-sm" list="item-code-options" autocomplete="off" value="{{ request('item_code') }}" placeholder="Code">
                    <datalist id="item-code-options">
                        @foreach($allItems as $item)
                            @if($item->item_code)
                                <option value="{{ $item->item_code }}"></option>
                            @endif
                        @endforeach
                    </datalist>
                </div>
                <div class="col">
                    <label class="form-label mb-1" for="item_name">Item Name</label>
                    <input type="text" id="item_name" name="item_name" class="form-control form-control-sm" list="item-name-options" autocomplete="off" value="{{ request('item_name') }}" placeholder="Name">
                    <datalist id="item-name-options">
                        @foreach($allItems as $item)
                            @if($item->item_name)
                                <option value="{{ $item->item_name }}"></option>
                            @endif
                        @endforeach
                    </datalist>
                </div>
                <div class="col">
                    <label class="form-label mb-1" for="hsn_id">HSN</label>
                    <select id="hsn_id" name="hsn_id" class="form-select form-select-sm select2">
                        <option value="">All</option>
                        @foreach($hsns as $hsn)
                            <option value="{{ $hsn->id }}" {{ request('hsn_id') == $hsn->id ? 'selected' : '' }}>{{ $hsn->hsn_code }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col">
                    <label class="form-label mb-1" for="unit_id">Unit</label>
                    <select id="unit_id" name="unit_id" class="form-select form-select-sm select2">
                        <option value="">All</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}" {{ request('unit_id') == $unit->id ? 'selected' : '' }}>{{ $unit->unit_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col">
                    <label class="form-label mb-1" for="product_category_id">Category</label>
                    <select id="product_category_id" name="product_category_id" class="form-select form-select-sm select2">
                        <option value="">All</option>
                        @foreach($productCategories as $category)
                            <option value="{{ $category->id }}" {{ request('product_category_id') == $category->id ? 'selected' : '' }}>{{ $category->category_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col">
                    <label class="form-label mb-1" for="product_sub_category_id">Sub Category</label>
                    <select id="product_sub_category_id" name="product_sub_category_id" class="form-select form-select-sm select2">
                        <option value="">All</option>
                        @foreach($productSubCategories as $subCategory)
                            <option value="{{ $subCategory->id }}" {{ request('product_sub_category_id') == $subCategory->id ? 'selected' : '' }}>{{ $subCategory->sub_category_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col">
                    <label class="form-label mb-1" for="stock_status">Stock Status</label>
                    <select id="stock_status" name="stock_status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="out_stock" {{ request('stock_status') === 'out_stock' ? 'selected' : '' }}>Out Stock</option>
                        <option value="low" {{ request('stock_status') === 'low' ? 'selected' : '' }}>Low Stock</option>
                        <option value="in_stock" {{ request('stock_status') === 'in_stock' ? 'selected' : '' }}>In Stock</option>
                    </select>
                </div>
                <div class="col">
                    <label class="form-label mb-1" for="filter_date">Date</label>
                    <input type="date" id="filter_date" name="filter_date" class="form-control form-control-sm" value="{{ request('filter_date', now()->toDateString()) }}">
                </div>
                <div class="col">
                    <label class="form-label mb-1" for="per_page">Per Page</label>
                    <select id="per_page" name="per_page" class="form-select form-select-sm">
                        <option value="10" {{ (isset($perPage) && $perPage == 10) ? 'selected' : '' }}>10</option>
                        <option value="15" {{ (isset($perPage) && $perPage == 15) ? 'selected' : '' }}>15</option>
                        <option value="20" {{ (isset($perPage) && $perPage == 20) ? 'selected' : '' }}>20</option>
                        <option value="50" {{ (isset($perPage) && $perPage == 50) ? 'selected' : '' }}>50</option>
                        <option value="1000000" {{ (isset($perPage) && $perPage == 1000000) ? 'selected' : '' }}>All</option>
                    </select>
                </div>
                <div class="col d-flex gap-1 align-items-end">
                    <button type="submit" class="btn btn-sm btn-primary flex-fill" title="Filter">
                        <i class="bx bx-search"></i>
                    </button>
                    <a href="{{ route('item-stocks.index') }}" class="btn btn-sm btn-outline-secondary flex-fill" title="Reset">
                        <i class="bx bx-reset"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

<style>
.stock-filters-row > .col {
    min-width: 0;
    flex: 1 1 0;
}
.stock-filters-row .form-label {
    font-size: 0.75rem;
    white-space: nowrap;
}
.stock-filters-row .form-control,
.stock-filters-row .form-select {
    width: 100%;
    height: calc(1.5em + 0.5rem + 2px);
    min-height: calc(1.5em + 0.5rem + 2px);
    font-size: 0.8125rem;
    padding: 0.25rem 0.5rem;
}
.stock-filters-row .select2-container {
    width: 100% !important;
}
.stock-filters-row .select2-container .select2-selection--single {
    height: calc(1.5em + 0.5rem + 2px);
    min-height: calc(1.5em + 0.5rem + 2px);
    font-size: 0.8125rem;
    padding: 0.15rem 0.5rem;
    display: flex;
    align-items: center;
}
.stock-filters-row .select2-container .select2-selection--single .select2-selection__rendered {
    padding-left: 0;
    line-height: 1.5;
}
.stock-filters-row .select2-container .select2-selection--single .select2-selection__arrow {
    height: 100%;
}
.table-sticky-header th {
    position: sticky;
    top: 0;
    background: #fff !important;
    z-index: 10;
    box-shadow: 0 2px 2px -1px rgba(0,0,0,0.1);
}
.table-sticky-header th.sortable {
    cursor: pointer;
    user-select: none;
}
.table-sticky-header th.sortable:hover {
    background: #f8f9fa !important;
}
.sort-icon {
    font-size: 0.8em;
    margin-left: 5px;
    color: #a1acb8;
}
.table-responsive.stock-table-wrapper {
    max-height: 60vh;
    overflow-y: auto;
    overflow-x: auto;
}
#stockTable {
    transform-origin: top left;
    width: 100%;
}
#stockTable th,
#stockTable td {
    vertical-align: middle;
    white-space: nowrap;
}
#stockTable th:nth-child(3),
#stockTable td:nth-child(3) {
    white-space: normal;
    min-width: 180px;
    max-width: 240px;
}
#stockTable th:nth-child(7),
#stockTable td:nth-child(7),
#stockTable th:nth-child(8),
#stockTable td:nth-child(8) {
    white-space: normal;
    min-width: 120px;
    max-width: 160px;
}
#stockTable .cell-wrap {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    white-space: normal;
    word-break: break-word;
}
</style>

    <!-- Table -->
    <div class="card">
        <div class="table-responsive stock-table-wrapper">
            <table class="table table-hover table-sticky-header" id="stockTable">
                <thead>
                    <tr>
                        <th class="sortable" data-type="number">S.No <span class="sort-icon"></span></th>
                        <th class="sortable" data-type="string">Item Code <span class="sort-icon"></span></th>
                        <th class="sortable" data-type="string">Item Name <span class="sort-icon"></span></th>
                        <th class="sortable" data-type="string">HSN <span class="sort-icon"></span></th>
                        <th class="sortable" data-type="string">Unit <span class="sort-icon"></span></th>
                        <th class="sortable" data-type="number">Price <span class="sort-icon"></span></th>
                        <th class="sortable" data-type="string">Product Category <span class="sort-icon"></span></th>
                        <th class="sortable" data-type="string">Product Sub Category <span class="sort-icon"></span></th>
                        <th class="text-end sortable" data-type="number">Opening Stock <span class="sort-icon"></span></th>
                        <th class="text-end sortable" data-type="number">Adjusted Stocks <span class="sort-icon"></span></th>
                        <th class="text-end sortable" data-type="number">Received Stocks <span class="sort-icon"></span></th>
                        <th class="text-end sortable" data-type="number">Issued Stock <span class="sort-icon"></span></th>
                        <th class="text-end sortable" data-type="number">Closing Stock <span class="sort-icon"></span></th>
                        <th class="sortable" data-type="string" title="Out Stock when current stock is zero or below. Low Stock when current stock is above zero and at or below the item threshold quantity.">Stock Status <span class="sort-icon"></span></th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($items as $index => $item)
                        @php
                            $latestStock = $latestStocks[$item->id] ?? null;
                            
                            if ($latestStock && $latestStock->date->toDateString() === $filterDate) {
                                // There was a movement exactly on this day, use its specific opening/closing
                                $opening = (float) $latestStock->opening_stock;
                                $closing = (float) $latestStock->closing_stock;
                            } elseif ($latestStock) {
                                // There were no movements on this day, but there were in the past.
                                // Therefore, today's opening AND closing are equal to the last known closing stock.
                                $opening = (float) $latestStock->closing_stock;
                                $closing = (float) $latestStock->closing_stock;
                            } else {
                                // Never had any stock movements. Use base opening stock.
                                $opening = (float) $item->opening_stock;
                                $closing = (float) $item->opening_stock;
                            }

                            $received = (float) ($receivedStocks[$item->id] ?? 0);
                            $issued = (float) ($issuedStocks[$item->id] ?? 0);
                            $adjusted = (float) ($adjustedStocks[$item->id] ?? 0);
                            
                            // If today is the filter date or there's no latest stock on exactly the filter date
                            if (!$latestStock || $latestStock->date->toDateString() !== $filterDate) {
                                $closing = $opening + $received - $issued + $adjusted;
                            }
                            
                            $price = $item->rates->first()->rate ?? 0;
                        @endphp
                        <tr>
                            <td>{{ $items->firstItem() + $index }}</td>
                            <td><strong>{{ $item->item_code }}</strong></td>
                            <td>
                                <span class="cell-wrap" title="{{ $item->item_name }}">{{ $item->item_name }}</span>
                                @if($item->created_at && $item->created_at->isToday())
                                    <span class="badge bg-success ms-1" style="font-size: 0.65em;">New</span>
                                @endif
                            </td>
                            <td>{{ $item->hsn?->hsn_code ?? '--' }}</td>
                            <td>{{ $item->unit?->unit_name ?? $item->uom }}</td>
                            <td>{{ number_format($price, 2) }}</td>
                            <td><span class="cell-wrap" title="{{ $item->productCategory?->category_name ?? '--' }}">{{ $item->productCategory?->category_name ?? '--' }}</span></td>
                            <td><span class="cell-wrap" title="{{ $item->productSubCategory?->sub_category_name ?? '--' }}">{{ $item->productSubCategory?->sub_category_name ?? '--' }}</span></td>
                            <td class="text-end">{{ number_format($opening, 3) }}</td>
                            <td class="text-end">{{ number_format($adjusted, 3) }}</td>
                            <td class="text-end text-success">{{ number_format($received, 3) }}</td>
                            <td class="text-end text-danger">{{ number_format($issued, 3) }}</td>
                            <td class="text-end fw-bold">{{ number_format($closing, 3) }}</td>
                            <td>
                                <span class="badge bg-label-{{ $item->stockStatusBadgeClass() }}">{{ $item->stock_status }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="14" class="text-center py-4">No items found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($items->hasPages())
        <div class="card-footer d-flex justify-content-center">
            {{ $items->links() }}
        </div>
        @endif
    </div>
@endsection

@section('page-script')
<script>
    $(document).ready(function() {
        if (typeof $.fn.select2 === 'function') {
            $('#hsn_id, #unit_id, #product_category_id, #product_sub_category_id').select2({
                placeholder: 'All',
                allowClear: true,
                width: '100%'
            });
        }

        const getCellValue = (tr, idx) => tr.children[idx].innerText || tr.children[idx].textContent;

        const comparer = (idx, asc, type) => (a, b) => {
            const v1 = getCellValue(asc ? a : b, idx).trim().replace(/,/g, '');
            const v2 = getCellValue(asc ? b : a, idx).trim().replace(/,/g, '');
            if (v1 === '' || v1 === '--') return asc ? 1 : -1;
            if (v2 === '' || v2 === '--') return asc ? -1 : 1;
            
            if (type === 'number') {
                return parseFloat(v1) - parseFloat(v2);
            }
            return v1.toString().localeCompare(v2);
        };

        document.querySelectorAll('th.sortable').forEach(th => th.addEventListener('click', (() => {
            const table = th.closest('table');
            const tbody = table.querySelector('tbody');
            const type = th.getAttribute('data-type');
            
            Array.from(th.parentNode.children).forEach(sibling => {
                if (sibling !== th) {
                    sibling.classList.remove('asc', 'desc');
                    sibling.querySelector('.sort-icon').innerHTML = '';
                }
            });

            let isAsc = !th.classList.contains('asc');
            th.classList.toggle('asc', isAsc);
            th.classList.toggle('desc', !isAsc);
            
            th.querySelector('.sort-icon').innerHTML = isAsc ? '&#9650;' : '&#9660;';

            Array.from(tbody.querySelectorAll('tr:nth-child(n)'))
                .sort(comparer(Array.from(th.parentNode.children).indexOf(th), isAsc, type))
                .forEach(tr => tbody.appendChild(tr) );
        })));

        const table = document.getElementById('stockTable');
        if (!table) {
            return;
        }

        const zoomStorageKey = 'item-stock-table-zoom';
        let tableZoom = parseFloat(sessionStorage.getItem(zoomStorageKey) || '1') || 1;

        const canUseZoom = 'zoom' in document.body.style;

        function setTableZoom(next) {
            tableZoom = Math.min(1.6, Math.max(0.5, Math.round(next * 10) / 10));
            if (canUseZoom) {
                table.style.zoom = tableZoom;
                table.style.transform = '';
            } else {
                table.style.transform = 'scale(' + tableZoom + ')';
            }
            sessionStorage.setItem(zoomStorageKey, String(tableZoom));
        }

        setTableZoom(tableZoom);

        document.addEventListener('keydown', function (e) {
            if (!(e.ctrlKey || e.metaKey) || e.altKey) return;

            const isPlus = e.key === '+' || e.key === '=' || e.code === 'NumpadAdd' || e.code === 'Equal';
            const isMinus = e.key === '-' || e.key === '_' || e.code === 'NumpadSubtract' || e.code === 'Minus';
            const isReset = e.key === '0' || e.code === 'Digit0' || e.code === 'Numpad0';

            if (isPlus) {
                e.preventDefault();
                setTableZoom(tableZoom + 0.1);
            } else if (isMinus) {
                e.preventDefault();
                setTableZoom(tableZoom - 0.1);
            } else if (isReset) {
                e.preventDefault();
                setTableZoom(1);
            }
        });

        window.addEventListener('wheel', function (e) {
            if (!(e.ctrlKey || e.metaKey)) return;
            e.preventDefault();
            setTableZoom(tableZoom + (e.deltaY < 0 ? 0.1 : -0.1));
        }, { passive: false });
    });
</script>
@endsection
