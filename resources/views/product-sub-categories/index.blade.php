@extends('layouts/contentNavbarLayout')

@section('title', 'Product Sub Categories')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Product Sub Categories</h4>
    <div class="d-flex gap-2">
        @include('partials.master-export-buttons', ['excelRoute' => 'product-sub-categories.export', 'pdfRoute' => 'product-sub-categories.export-pdf', 'permission' => 'product-sub-category:view'])
        @can('product-sub-category:create')
        <a href="{{ route('product-sub-categories.create') }}" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i> Add Product Sub Category
        </a>
        @endcan
    </div>
</div>

<x-master-filters action="{{ route('product-sub-categories.index') }}" placeholder="Sub category or category">
    <div class="col-md-3">
        <label class="form-label" for="product_category_id">Category</label>
        <select id="product_category_id" name="product_category_id" class="form-select select2">
            <option value="">All Categories</option>
            @foreach($productCategories as $productCategory)
                <option value="{{ $productCategory->id }}" @selected(request('product_category_id') == $productCategory->id)>{{ $productCategory->category_name }}</option>
            @endforeach
        </select>
    </div>
</x-master-filters>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">Product Sub Category List</h5>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th class="text-nowrap">#</th>
                    <th>Category</th>
                    <th>Sub Category Name</th>
                    <th>Status</th>
                    <th class="text-center" style="width:100px">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($productSubCategories as $productSubCategory)
                    <tr>
                        <td class="text-nowrap text-muted">
                            {{ ($productSubCategories->currentPage() - 1) * $productSubCategories->perPage() + $loop->iteration }}
                        </td>
                        <td>{{ $productSubCategory->productCategory?->category_name ?? '-' }}</td>
                        <td>{{ $productSubCategory->sub_category_name }}</td>
                        <td>
                            @can('product-sub-category:status')
                            @if($productSubCategory->status === 'active')
                                <span class="badge bg-label-success">Active</span>
                            @else
                                <span class="badge bg-label-danger">Inactive</span>
                            @endif
                            @else
                            <span class="badge bg-{{ $productSubCategory->status === 'active' ? 'success' : 'secondary' }}">
                                {{ ucfirst($productSubCategory->status) }}
                            </span>
                            @endcan
                        </td>
                        <td class="text-center">
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    @can('product-sub-category:edit')
                                    <a class="dropdown-item" href="{{ route('product-sub-categories.edit', $productSubCategory->id) }}">
                                        <i class="bx bx-edit-alt me-1"></i> Edit
                                    </a>
                                    @endcan
                                    @can('product-sub-category:delete')
                                    <form action="{{ route('product-sub-categories.destroy', $productSubCategory->id) }}"
                                          method="POST"
                                          id="delete-form-{{ $productSubCategory->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="dropdown-item text-danger" onclick="confirmDelete({{ $productSubCategory->id }})">
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
                        <td colspan="5" class="text-center py-4">
                            <p class="mb-2">No product sub categories found.</p>
                            @can('product-sub-category:create')
                            <a href="{{ route('product-sub-categories.create') }}" class="btn btn-primary btn-sm">
                                <i class="bx bx-plus me-1"></i> Add Product Sub Category
                            </a>
                            @endcan
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center">
        @if($productSubCategories->hasPages())
            <div class="text-muted">Showing {{ $productSubCategories->firstItem() ?? 0 }} to {{ $productSubCategories->lastItem() ?? 0 }} of {{ $productSubCategories->total() }} Product Sub Categories</div>
            <div>{{ $productSubCategories->links() }}</div>
        @endif
    </div>
</div>
@endsection

@section('page-script')
<script>
function confirmDelete(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
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

function updateProductSubCategoryStatus(el) {
    let subCategoryId = el.getAttribute('data-id');
    let status = el.checked ? 'active' : 'inactive';

    $.ajax({
        url: "{{ route('product-sub-categories.status') }}",
        type: "PUT",
        data: {
            _token: "{{ csrf_token() }}",
            id: subCategoryId,
            status: status
        },
        success: function (response) {
            if (!response.success) {
                el.checked = !el.checked;
            }
        },
        error: function () {
            el.checked = !el.checked;
        }
    });
}
</script>
@endsection
