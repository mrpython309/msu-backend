@extends('layouts/contentNavbarLayout')

@section('title', 'Asset Depreciation')

@section('vendor-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}">
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css') }}">
@endsection

@section('page-style')
<style>
    #depreciationTable .asset-desc-text {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        white-space: normal;
        word-break: break-word;
        line-height: 1.35;
        width: 280px;
        max-width: 280px;
        font-weight: 600;
    }
</style>
@endsection

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Asset Depreciation Report</h4>
</div>

{{-- Filters --}}
<div class="card mb-4">
    <div class="card-header border-bottom mb-4">
        <h5 class="mb-0">Filters</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('asset.depreciation') }}" class="row g-3">

            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text"
                    name="search"
                    class="form-control"
                    value="{{ request('search') }}"
                    placeholder="Search by Asset Code / Asset Description">
            </div>

            <div class="col-md-3">
                <label class="form-label">Company</label>
                <select id="company_id" name="company_id" class="form-select select2">
                    <option value="">All Companies</option>
                    @foreach($companies as $company)
                        <option value="{{ $company->id }}" {{ (string) request('company_id') === (string) $company->id ? 'selected' : '' }}>
                            {{ $company->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Branch</label>
                <select id="branch_id" name="branch_id" class="form-select select2">
                    <option value="">All Branch</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ (string) request('branch_id') === (string) $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Product Category</label>
                <select class="form-select select2" name="product_category_id" id="product_category_id">
                    <option value="">All Product Categories</option>
                    @foreach($productCategories as $category)
                        <option value="{{ $category->id }}" {{ (string) request('product_category_id') === (string) $category->id ? 'selected' : '' }}>
                            {{ $category->category_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Product Sub Category</label>
                <select class="form-select select2" name="product_sub_category_id" id="product_sub_category_id">
                    <option value="">All Product Sub Categories</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Asset Custodian</label>
                <select class="form-select select2" name="custodian_id">
                    <option value="">All Custodians</option>
                    @foreach ($custodians as $custodian)
                        <option value="{{ $custodian->id }}" {{ (string) request('custodian_id') === (string) $custodian->id ? 'selected' : '' }}>
                            {{ $custodian->employee_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Method</label>
                <select class="form-select" name="depreciation_method">
                    <option value="">All Methods</option>
                    <option value="straight_line" {{ request('depreciation_method') == 'straight_line' ? 'selected' : '' }}>Straight Line</option>
                    <option value="reducing_balance" {{ request('depreciation_method') == 'reducing_balance' ? 'selected' : '' }}>Reducing Balance</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label">Purchase From</label>
                <input type="date" name="from" class="form-control" value="{{ request('from') }}">
            </div>

            <div class="col-md-3">
                <label class="form-label">Purchase To</label>
                <input type="date" name="to" class="form-control" value="{{ request('to') }}">
            </div>

            <div class="col-md-3">
                <label class="form-label" for="per_page">Per Page</label>
                <select id="per_page" name="per_page" class="form-select" onchange="this.form.submit()">
                    @foreach ([10, 25, 50, 100] as $size)
                        <option value="{{ $size }}" @selected((string) request('per_page', 50) === (string) $size)>{{ $size }}</option>
                    @endforeach
                    <option value="all" @selected(request('per_page') === 'all')>All</option>
                </select>
            </div>

            <div class="col-md-1 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bx bx-search"></i>
                </button>
            </div>

        </form>
    </div>
</div>

{{-- Table --}}
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">Depreciation List</h5>
        </div>
        <div class="d-flex gap-2">
            @can('asset:view')
            <a href="{{ route('asset.depreciation.export', request()->query()) }}" class="btn btn-outline-success btn-sm">
                <i class="bx bx-export me-1"></i> Excel
            </a>
            <a href="{{ route('asset.depreciation.export-pdf', request()->query()) }}" class="btn btn-outline-danger btn-sm">
                <i class="bx bxs-file-pdf me-1"></i> PDF
            </a>
            @endcan
        </div>
    </div>

    <div class="table-responsive">
        <table class="table card-table" id="depreciationTable">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Asset</th>
                    <th>Code</th>
                    <th>Product Category</th>
                    <th>Product Sub Category</th>
                    <th>Asset Custodian</th>
                    <th>Method</th>
                    <th>Purchase Date</th>
                    <th>Invoice Amount</th>
                    <th>Current Value</th>
                </tr>
            </thead>

            <tbody>
                @forelse($assets as $asset)
                <tr>
                    <td>{{ $assets->firstItem() + $loop->index }}</td>
                    <td>
                        <span class="asset-desc-text" title="{{ $asset->asset_name }}">{{ $asset->asset_name }}</span>
                    </td>
                    <td>{{ $asset->asset_code }}</td>
                    <td>{{ $asset->productCategory?->category_name ?? '--' }}</td>
                    <td>{{ $asset->productSubCategory?->sub_category_name ?? '--' }}</td>
                    <td>{{ $asset->custodian?->employee_name ?? $asset->latestAssignment?->employee?->name ?? '--' }}</td>
                    <td>
                        <span class="badge bg-label-info">
                            {{ ucfirst(str_replace('_',' ',$asset->depreciation_method)) }}
                        </span>
                    </td>
                    <td>{{ optional($asset->purchase_date)->format('d M Y') ?: '--' }}</td>
                    <td>₹ {{ number_format($asset->purchase_cost,2) }}</td>
                    <td>
                        <strong class="text-success">
                            ₹ {{ number_format($asset->current_value,2) }}
                        </strong>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="10" class="text-center text-muted py-5">
                        No depreciation data available
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center">
        <div class="text-muted">Showing {{ $assets->firstItem() ?? 0 }} to {{ $assets->lastItem() ?? 0 }} of {{ $assets->total() }} assets</div>
        @if($assets->hasPages())
            <div>{{ $assets->links() }}</div>
        @endif
    </div>
</div>

@endsection

@section('vendor-script')
<script src="{{ asset('assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
@endsection

@section('page-script')
<script>
function refreshSearch($el) {
    if ($el.hasClass('select2-hidden-accessible')) {
        $el.trigger('change.select2');
    }
}

function initFilterSearch() {
    $('.select2').each(function () {
        const $select = $(this);
        if ($select.hasClass('select2-hidden-accessible')) {
            return;
        }
        $select.select2({
            placeholder: $select.find('option[value=""]').first().text() || 'Select',
            allowClear: true,
            closeOnSelect: true,
            width: '100%',
            minimumResultsForSearch: 0,
            dropdownParent: $(document.body)
        });
    });
}

function loadBranches(companyId, selectedId = null) {
    const branchDropdown = $('#branch_id');
    branchDropdown.html('<option>Loading...</option>');

    if (!companyId) {
        branchDropdown.html('<option value="">All Branch</option>');
        refreshSearch(branchDropdown);
        return;
    }

    $.get(
        "{{ route('ajax.getbranch.bycompany', ':id') }}".replace(':id', companyId),
        function (res) {
            branchDropdown.empty().append('<option value="">All Branch</option>');

            if (res.data && res.data.length > 0) {
                $.each(res.data, function (_, value) {
                    let selected = selectedId == value.id ? 'selected' : '';
                    branchDropdown.append(
                        `<option value="${value.id}" ${selected}>${value.name}</option>`
                    );
                });
            } else {
                branchDropdown.append('<option value="">No branches found</option>');
            }
            refreshSearch(branchDropdown);
        }
    ).fail(function () {
        branchDropdown.html('<option value="">Failed to load branches</option>');
        refreshSearch(branchDropdown);
    });
}

$(function() {
    $('#company_id').on('change', function () {
        loadBranches(this.value);
    });

    const PRODUCT_SUB_CATEGORIES = @json($productSubCategories);
    const SELECTED_PRODUCT_SUB = @json((string) request('product_sub_category_id'));

    function fillProductSubCategories(categoryId, selectedId) {
        const $sub = $('#product_sub_category_id');
        $sub.empty().append('<option value="">All Product Sub Categories</option>');
        PRODUCT_SUB_CATEGORIES.forEach(function (row) {
            if (categoryId && String(row.product_category_id) !== String(categoryId)) {
                return;
            }
            const selected = String(selectedId) === String(row.id) ? ' selected' : '';
            $sub.append('<option value="' + row.id + '"' + selected + '>' + row.sub_category_name + '</option>');
        });
        refreshSearch($sub);
    }

    fillProductSubCategories($('#product_category_id').val(), SELECTED_PRODUCT_SUB);
    initFilterSearch();

    $('#product_category_id').on('change', function () {
        fillProductSubCategories(this.value, '');
    });

    $('#depreciationTable').DataTable({
        paging: false,
        lengthChange: false,
        info: false,
        searching: false,
        responsive: true,
        order: [[1, 'asc']],
        columnDefs: [
            { targets: 0, orderable: false, searchable: false }
        ]
    });
});
</script>
@endsection
