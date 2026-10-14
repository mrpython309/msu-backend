@extends('layouts/contentNavbarLayout')

@section('title', 'Asset Types')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
  Asset Type
</h4>

<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">Asset Type List</h5>
        @can('asset-types:create')
            <a href="{{ route('asset-types.create') }}" class="btn btn-primary">
            Add Asset Type
             </a>
        @endcan
        
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Parent Category</th>
                    <th>Category</th>
                    <th>Asset Type Name</th>
                    <th>Status</th>
                    <th width="180">Actions</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $groupedTypes = $assetTypes->groupBy(function ($type) {
                        return ($type->category_id ?? '0') . '-' . ($type->sub_category_id ?? '0');
                    })->sortByDesc(function ($group) {
                        return $group->max('id');
                    });
                    $p = 1;
                @endphp

                @forelse($groupedTypes as $types)
                    @php $first = $types->first(); @endphp

                    @if($types->count() === 1)
                        @php $assetType = $first; @endphp
                        <tr>
                            <td>{{ $p }}.</td>
                            <td>{{ $assetType->category?->category_name }}</td>
                            <td>{{ $assetType->subCategory?->category_name }}</td>
                            <td>{{ $assetType->asset_type_name }}</td>
                            <td>
                                @if($assetType->status === 'active')
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

                                @can('asset-types:edit')
                                <a href="{{ route('asset-types.edit', $assetType->id) }}" class="dropdown-item"><i class="bx bx-edit-alt me-1"></i> Edit</a>
                                @endcan
                                @can('asset-types:delete')
                                <form action="{{ route('asset-types.destroy', $assetType->id) }}" method="POST" class="d-inline" id="delete-form-{{ $assetType->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="dropdown-item text-danger" onclick="confirmDelete({{ $assetType->id }})"><i class="bx bx-trash me-1"></i> Delete</button>
                                </form>
                                @endcan
                            
                                </div>
                            </div>
                        </td>
                        </tr>
                    @else
                        <tr>
                            <td>{{ $p }}.</td>
                            <td>{{ $first->category?->category_name }}</td>
                            <td>{{ $first->subCategory?->category_name }}</td>
                            <td>--</td>
                            <td></td>
                            <td></td>
                        </tr>

                        @php $c = 1; @endphp
                        @foreach($types as $assetType)
                        <tr>
                            <td>&nbsp;&nbsp;&nbsp;&nbsp; {{ $c }}.</td>
                            <td>--</td>
                            <td>--</td>
                            <td>{{ $assetType->asset_type_name }}</td>
                            <td>
                                @if($assetType->status === 'active')
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

                                @can('asset-types:edit')
                                <a href="{{ route('asset-types.edit', $assetType->id) }}" class="dropdown-item"><i class="bx bx-edit-alt me-1"></i> Edit</a>
                                @endcan
                                @can('asset-types:delete')
                                <form action="{{ route('asset-types.destroy', $assetType->id) }}" method="POST" class="d-inline" id="delete-form-{{ $assetType->id }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" class="dropdown-item text-danger" onclick="confirmDelete({{ $assetType->id }})"><i class="bx bx-trash me-1"></i> Delete</button>
                                </form>
                                @endcan
                            
                                </div>
                            </div>
                        </td>
                        </tr>
                        @php $c++; @endphp
                        @endforeach
                    @endif

                    @php $p++; @endphp
                @empty
                    <tr>
                        <td colspan="6" class="text-center">
                            No Asset Types found.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
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
        url: "{{ route('asset-types.status') }}",
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
