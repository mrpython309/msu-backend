@extends('layouts/contentNavbarLayout')

@section('title', 'Product Categories')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Product Categories</h4>
    <div class="d-flex gap-2">
        @include('partials.master-export-buttons', ['excelRoute' => 'product-categories.export', 'pdfRoute' => 'product-categories.export-pdf', 'permission' => 'product-category:view'])
        @can('product-category:create')
        <a href="{{ route('product-categories.create') }}" class="btn btn-primary">
            <i class="bx bx-plus me-1"></i> Add Product Category
        </a>
        @endcan
    </div>
</div>

<x-master-filters action="{{ route('product-categories.index') }}" placeholder="Category name or short name" />

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">Product Category List</h5>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th class="text-nowrap">#</th>
                    <th>Category Name</th>
                    <th>Short Name</th>
                    <th>Status</th>
                    <th class="text-center" style="width:100px">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($productCategories as $productCategory)
                    <tr>
                        <td class="text-nowrap text-muted">
                            {{ ($productCategories->currentPage() - 1) * $productCategories->perPage() + $loop->iteration }}
                        </td>
                        <td>{{ $productCategory->category_name }}</td>
                        <td><code>{{ $productCategory->category_code }}</code></td>
                        <td>
                            @can('product-category:status')
                            @if($productCategory->status === 'active')
                                <span class="badge bg-label-success">Active</span>
                            @else
                                <span class="badge bg-label-danger">Inactive</span>
                            @endif
                            @else
                            <span class="badge bg-{{ $productCategory->status === 'active' ? 'success' : 'secondary' }}">
                                {{ ucfirst($productCategory->status) }}
                            </span>
                            @endcan
                        </td>
                        <td class="text-center">
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">
                                    @can('product-category:edit')
                                    <a class="dropdown-item" href="{{ route('product-categories.edit', $productCategory->id) }}">
                                        <i class="bx bx-edit-alt me-1"></i> Edit
                                    </a>
                                    @endcan
                                    @can('product-category:delete')
                                    <form action="{{ route('product-categories.destroy', $productCategory->id) }}"
                                          method="POST"
                                          id="delete-form-{{ $productCategory->id }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="button" class="dropdown-item text-danger" onclick="confirmDelete({{ $productCategory->id }})">
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
                            <p class="mb-2">No product categories found.</p>
                            @can('product-category:create')
                            <a href="{{ route('product-categories.create') }}" class="btn btn-primary btn-sm">
                                <i class="bx bx-plus me-1"></i> Add Product Category
                            </a>
                            @endcan
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center">
        @if($productCategories->hasPages())
            <div class="text-muted">Showing {{ $productCategories->firstItem() ?? 0 }} to {{ $productCategories->lastItem() ?? 0 }} of {{ $productCategories->total() }} Product Categories</div>
            <div>{{ $productCategories->links() }}</div>
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

function updateProductCategoryStatus(el) {
    let categoryId = el.getAttribute('data-id');
    let status = el.checked ? 'active' : 'inactive';

    $.ajax({
        url: "{{ route('product-categories.status') }}",
        type: "PUT",
        data: {
            _token: "{{ csrf_token() }}",
            id: categoryId,
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
