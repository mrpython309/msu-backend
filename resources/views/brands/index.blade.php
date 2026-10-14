@extends('layouts/contentNavbarLayout')

@section('title', 'Brands')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
  Brands
</h4>

<x-master-filters action="{{ route('brands.index') }}" placeholder="Brand name" />

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div>
            <h5 class="mb-0">Brand List</h5>
        </div>
        <div class="d-flex gap-2">
            @include('partials.master-export-buttons', ['excelRoute' => 'brands.export', 'pdfRoute' => 'brands.export-pdf', 'permission' => 'brands:view'])
            @can('brands:create')
            <a href="{{ route('brands.create') }}" class="btn btn-primary">
                Add Brand
            </a>
            @endcan
        </div>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th class="text-nowrap">#</th>
                    <th>Brand Name</th>
                    <th>Status</th>
                    <th width="180">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($brands as $brand)
                    <tr>
                        <td class="text-nowrap text-muted">
                            {{ ($brands->currentPage() - 1) * $brands->perPage() + $loop->iteration }}
                        </td>
                        <td>{{ $brand->brand_name }}</td>
                        <td>
                            @if($brand->status === 'active')
                                <span class="badge bg-label-success">Active</span>
                            @else
                                <span class="badge bg-label-danger">Inactive</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="dropdown">
                                <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="bx bx-dots-vertical-rounded"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-end">

                             @can('brands:edit')
                            <a href="{{ route('brands.edit', $brand->id) }}"
                               class="dropdown-item"><i class="bx bx-edit-alt me-1"></i> Edit</a>
                            @endcan

                             @can('brands:delete')

                            <form action="{{ route('brands.destroy', $brand->id) }}"
                                  method="POST"
                                  class="d-inline"
                                  id="delete-form-{{ $brand->id }}">
                                @csrf
                                @method('DELETE')
                                <button type="button"
                                        class="dropdown-item text-danger"
                                        onclick="confirmDelete({{ $brand->id }})"><i class="bx bx-trash me-1"></i> Delete</button>
                            </form>

                            @endcan
                        
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">
                            No Brands found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="card-footer d-flex justify-content-between align-items-center">
        @if($brands->hasPages())
            <div class="text-muted">Showing {{ $brands->firstItem() ?? 0 }} to {{ $brands->lastItem() ?? 0 }} of {{ $brands->total() }} Brands</div>
            <div>{{ $brands->links() }}</div>
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

function updateDepartmentStatus(el) {

    let departmentId = el.getAttribute('data-id');
    let status = el.checked ? 'active' : 'inactive';

    $.ajax({
        url: "{{ route('brands.status') }}",
        type: "PUT",
        data: {
            _token: "{{ csrf_token() }}",
            id: departmentId,
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
