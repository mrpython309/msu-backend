@extends('layouts/contentNavbarLayout')

@section('title', 'Item Master')

@section('page-style')
<style>
    .item-master-table {
        table-layout: fixed;
        width: 100%;
    }
    .item-master-table th,
    .item-master-table td {
        vertical-align: middle;
    }
    .item-master-table td > .d-flex {
        max-width: 100%;
    }
    .item-master-table .item-name-text {
        flex: 1;
        min-width: 0;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        white-space: normal;
        word-break: break-word;
    }
    .item-master-table .text-ellipsis {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Item Master</h4>
    <div class="d-flex gap-2">
        @can('item-master:view')
        <a href="{{ route('item-masters.export', request()->query()) }}" class="btn btn-outline-success">
            <i class="bx bx-export me-1"></i> Excel
        </a>
        <a href="{{ route('item-masters.export-pdf', request()->query()) }}" class="btn btn-outline-danger">
            <i class="bx bxs-file-pdf me-1"></i> PDF
        </a>
        @endcan
        @can('item-master:create')
        <a href="{{ route('item-masters.create') }}" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i> Add Item
        </a>
        @endcan
    </div>
</div>

@if(session('success'))
<div class="alert alert-success d-flex" role="alert">
  <span class="badge badge-center rounded-pill bg-success border-label-success p-3 me-2"><i class="bx bx-check fs-6"></i></span>
  <div class="d-flex flex-column ps-1">
    <h6 class="alert-heading d-flex align-items-center fw-bold mb-1">Success!</h6>
    <span>{{ session('success') }}</span>
  </div>
</div>
@endif

<div class="card mb-4">
    <div class="card-body">
        <form action="{{ route('item-masters.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label" for="search">Search</label>
                <input type="text" id="search" name="search" class="form-control" placeholder="Item Code or Name" value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label" for="product_category_id">Product Category</label>
                <select id="product_category_id" name="product_category_id" class="form-select select2">
                    <option value="">All Categories</option>
                    @foreach($productCategories as $category)
                        <option value="{{ $category->id }}" {{ request('product_category_id') == $category->id ? 'selected' : '' }}>{{ $category->category_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label" for="product_sub_category_id">Product Sub Category</label>
                <select id="product_sub_category_id" name="product_sub_category_id" class="form-select select2">
                    <option value="">All Sub Categories</option>
                    @foreach($productSubCategories as $subCategory)
                        <option value="{{ $subCategory->id }}" {{ request('product_sub_category_id') == $subCategory->id ? 'selected' : '' }}>{{ $subCategory->sub_category_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label" for="per_page">Per Page</label>
                <select id="per_page" name="per_page" class="form-select" onchange="this.form.submit()">
                    <option value="10" {{ (string) request('per_page', $perPage ?? 10) === '10' ? 'selected' : '' }}>10</option>
                    <option value="25" {{ (string) request('per_page', $perPage ?? 10) === '25' ? 'selected' : '' }}>25</option>
                    <option value="50" {{ (string) request('per_page', $perPage ?? 10) === '50' ? 'selected' : '' }}>50</option>
                    <option value="100" {{ (string) request('per_page', $perPage ?? 10) === '100' ? 'selected' : '' }}>100</option>
                    <option value="all" {{ request('per_page') === 'all' || (int) request('per_page') === 1000000 ? 'selected' : '' }}>All</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">Filter</button>
                <a href="{{ route('item-masters.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Item Master List</h5>
    </div>

    <div class="table-responsive">
        <table class="table table-hover item-master-table">
            <thead>
                <tr>
                    <th style="width: 6%">S.No</th>
                    <th style="width: 12%">Item Code</th>
                    <th style="width: 38%">Item Name</th>
                    <th style="width: 16%">Product Category</th>
                    <th style="width: 16%">Product Sub Category</th>
                    <th style="width: 7%">Unit</th>
                    <th class="text-center" style="width: 5%">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($items as $index => $item)
                <tr>
                    <td>{{ $items->firstItem() + $index }}</td>
                    <td><strong>{{ $item->item_code }}</strong></td>
                    <td>
                        <div class="d-flex align-items-start gap-1">
                            <span class="item-name-text" title="{{ $item->item_name }}">{{ $item->item_name }}</span>
                            @if($item->created_at && $item->created_at->isToday())
                                <span class="badge bg-success flex-shrink-0" style="font-size: 0.65em;">New</span>
                            @endif
                        </div>
                    </td>
                    <td class="text-ellipsis" title="{{ $item->productCategory?->category_name ?? '-' }}">{{ $item->productCategory?->category_name ?? '-' }}</td>
                    <td class="text-ellipsis" title="{{ $item->productSubCategory?->sub_category_name ?? '-' }}">{{ $item->productSubCategory?->sub_category_name ?? '-' }}</td>
                    <td class="text-ellipsis">{{ $item->unit?->unit_name ?? ($item->uom ?: '--') }}</td>
                    <td class="text-center">
                        <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bx bx-dots-vertical-rounded"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                @can('item-master:view')
                                <a class="dropdown-item" href="{{ route('item-masters.show', $item) }}">
                                    <i class="bx bx-show me-1"></i> View
                                </a>
                                @endcan
                                @can('item-master:edit')
                                <a class="dropdown-item" href="{{ route('item-masters.edit', $item) }}">
                                    <i class="bx bx-edit-alt me-1"></i> Edit
                                </a>
                                @endcan
                                @can('item-master:delete')
                                <form action="{{ route('item-masters.destroy', $item) }}" method="POST" id="delete-form-{{ $item->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="dropdown-item text-danger" onclick="confirmDelete({{ $item->id }})">
                                        <i class="bx bx-trash me-1"></i> Delete
                                    </button>
                                </form>
                                @endcan
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-4">
                        <p class="mb-2">No items found.</p>
                        @can('item-master:create')
                        <a href="{{ route('item-masters.create') }}" class="btn btn-primary btn-sm">
                            <i class="bx bx-plus me-1"></i> Add Item
                        </a>
                        @endcan
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($items->hasPages())
    <div class="card-footer border-top">
        {{ $items->links() }}
    </div>
    @endif
</div>
@endsection

@section('page-script')
<script>
function confirmDelete(id) {
    Swal.fire({
        title: 'Delete this item?',
        text: 'The item will be deleted.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            document.getElementById('delete-form-' + id).submit();
        }
    });
}

function updateItemStatus(el) {
    let itemId = el.getAttribute('data-id');
    let status = el.checked ? 'active' : 'inactive';

    $.ajax({
        url: "{{ route('item-masters.status') }}",
        type: 'PUT',
        data: {
            _token: "{{ csrf_token() }}",
            id: itemId,
            status: status
        },
        success: function(response) {
            if (!response.success) {
                el.checked = !el.checked;
            }
        },
        error: function() {
            el.checked = !el.checked;
        }
    });
}

$(document).ready(function() {
    if (typeof $.fn.select2 === 'function') {
        $('#product_category_id, #product_sub_category_id').select2({
            placeholder: 'Select',
            allowClear: true,
            width: '100%'
        });
    }

    $('#product_category_id').on('change', function() {
        var categoryId = $(this).val();
        var $subCategorySelect = $('#product_sub_category_id');

        $subCategorySelect.empty();
        $subCategorySelect.append('<option value="">All Sub Categories</option>');

        if (categoryId) {
            $.ajax({
                url: '{{ route("item-masters.get-sub-categories") }}',
                type: 'GET',
                data: { product_category_id: categoryId },
                success: function(data) {
                    $.each(data, function(key, value) {
                        $subCategorySelect.append('<option value="'+ value.id +'">'+ value.sub_category_name +'</option>');
                    });
                    $subCategorySelect.val('').trigger('change');
                }
            });
        } else {
            $subCategorySelect.val('').trigger('change');
        }
    });
});
</script>
@endsection
