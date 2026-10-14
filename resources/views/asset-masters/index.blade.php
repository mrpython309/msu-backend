@extends('layouts/contentNavbarLayout')

@section('title', 'Asset Master')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
  Asset Master
</h4>

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
        <form action="{{ route('asset-masters.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <label class="form-label" for="search">Search</label>
                <input type="text" id="search" name="search" class="form-control" placeholder="Code or Name" value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="category_id">Asset Category</label>
                <select id="category_id" name="category_id" class="form-select select2">
                    <option value="">All Categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->category_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label" for="asset_type_id">Asset Type</label>
                <select id="asset_type_id" name="asset_type_id" class="form-select select2">
                    <option value="">All Types</option>
                    @foreach($assetTypes as $type)
                        <option value="{{ $type->id }}" {{ request('asset_type_id') == $type->id ? 'selected' : '' }}>{{ $type->asset_type_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label" for="per_page">Per Page</label>
                <select id="per_page" name="per_page" class="form-select" onchange="this.form.submit()">
                    @foreach([10, 25, 50, 100] as $size)
                        <option value="{{ $size }}" @selected((string) request('per_page', 10) === (string) $size)>{{ $size }}</option>
                    @endforeach
                    <option value="all" @selected(request('per_page') === 'all')>All</option>
                </select>
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary me-2">Filter</button>
                <a href="{{ route('asset-masters.index') }}" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">Asset Master List</h5>
        <div class="d-flex gap-2">
            @include('partials.master-export-buttons', ['excelRoute' => 'asset-masters.export', 'pdfRoute' => 'asset-masters.export-pdf', 'permission' => 'asset-master.view'])
            @can('asset-master.create')
                <a href="{{ route('asset-masters.create') }}" class="btn btn-primary">
                    Add Asset
                </a>
            @endcan
        </div>
    </div>

    <div class="table-responsive text-nowrap">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Item Code</th>
                    <th>Item Name</th>
                    <th>Category</th>
                    <th>Asset Type</th>
                    <th>UOM</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody class="table-border-bottom-0">
                @forelse($items as $item)
                <tr>
                    <td><strong>{{ $item->item_code }}</strong></td>
                    <td>{{ $item->item_name }}</td>
                    <td>{{ $item->category?->category_name }}</td>
                    <td>{{ $item->assetType?->asset_type_name }}</td>
                    <td>{{ $item->uom ?: '--' }}</td>
                    <td>
                        <span class="badge bg-label-{{ $item->status === 'active' ? 'success' : 'secondary' }}">
                            {{ ucfirst($item->status) }}
                        </span>
                    </td>
                    <td>
                        <div class="dropdown">
                            <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="bx bx-dots-vertical-rounded"></i></button>
                            <div class="dropdown-menu">
                                @can('asset-master.edit')
                                <a class="dropdown-item" href="{{ route('asset-masters.edit', $item->id) }}"><i class="bx bx-edit-alt me-1"></i> Edit</a>
                                @endcan
                                @can('asset-master.delete')
                                <form action="{{ route('asset-masters.destroy', $item->id) }}" method="POST" class="d-inline" id="delete-form-{{ $item->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="dropdown-item text-danger" onclick="confirmDelete({{ $item->id }})"><i class="bx bx-trash me-1"></i> Delete</button>
                                </form>
                                @endcan
                            </div>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center">No assets found.</td>
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

$(document).ready(function() {
    if (typeof $.fn.select2 === 'function') {
        $('#category_id, #asset_type_id').select2({
            placeholder: 'Select',
            allowClear: true,
            width: '100%'
        });
    }

    $('#category_id').on('change', function() {
        var categoryId = $(this).val();
        var $assetTypeSelect = $('#asset_type_id');
        
        $assetTypeSelect.empty();
        $assetTypeSelect.append('<option value="">All Types</option>');
        
        if (categoryId) {
            $.ajax({
                url: '{{ route("asset-masters.get-asset-types") }}',
                type: 'GET',
                data: { category_id: categoryId },
                success: function(data) {
                    $.each(data, function(key, value) {
                        $assetTypeSelect.append('<option value="'+ value.id +'">'+ value.asset_type_name +'</option>');
                    });
                    $assetTypeSelect.val('').trigger('change');
                }
            });
        } else {
            $assetTypeSelect.val('').trigger('change');
        }
    });
});
</script>
@endsection
