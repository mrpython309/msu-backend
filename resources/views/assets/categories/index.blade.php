@extends('layouts/contentNavbarLayout')

@section('title', ' Asset Categories')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
  Asset Categories
</h4>

<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">Asset Categories List</h5>
          @can('asset-category:create')
        <a href="{{ route('asset-category.create') }}" class="btn btn-primary">
            Add Asset Category
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
                    <th>Status</th>
                    <th width="180">Actions</th>
                </tr>
            </thead>
            <tbody>
                @php $p=1; @endphp
                @forelse($categories as $category)
                    <tr>
                        <td>{{ $p }}.</td>
                        <td>{{ $category->category_name }}</td>
                        <td>--</td>
                        <td>
                            @if($category->status === 'active')
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

                              @can('asset-category:edit')
                            <a href="{{ route('asset-category.edit', $category->id) }}"
                               class="dropdown-item"><i class="bx bx-edit-alt me-1"></i> Edit</a>
                            @endcan

                              @can('asset-category:delete')

                            <form action="{{ route('asset-category.destroy', $category->id) }}"
                                  method="POST"
                                  class="d-inline"
                                  id="delete-form-{{ $category->id }}">
                                @csrf
                                @method('DELETE')
                                <button type="button"
                                        class="dropdown-item text-danger"
                                        onclick="confirmDelete({{ $category->id }})"><i class="bx bx-trash me-1"></i> Delete</button>
                            </form>

                            @endcan
                        
                                </div>
                            </div>
                        </td>
                    </tr>

                    @php $c=1; @endphp

                    @foreach ($category->subCategories as $subCategory)
                        
                     <tr>
                        <td>&nbsp;&nbsp;&nbsp;&nbsp; {{ $c }}.</td>                       
                        <td>--</td>
                         <td>{{ $subCategory->category_name }}</td>
                        <td>
                            @if($subCategory->status === 'active')
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

                            <a href="{{ route('asset-category.edit', $subCategory->id) }}"
                               class="dropdown-item"><i class="bx bx-edit-alt me-1"></i> Edit</a>

                            <form action="{{ route('asset-category.destroy', $subCategory->id) }}"
                                  method="POST"
                                  class="d-inline"
                                  id="delete-form-{{ $subCategory->id }}">
                                @csrf
                                @method('DELETE')
                                <button type="button"
                                        class="dropdown-item text-danger"
                                        onclick="confirmDelete({{ $subCategory->id }})"><i class="bx bx-trash me-1"></i> Delete</button>
                            </form>
                        
                                </div>
                            </div>
                        </td>
                    </tr>

                      @php $c++; @endphp

                    @endforeach

                    @php $p++; @endphp
                @empty
                    <tr>
                        <td colspan="4" class="text-center">
                            No Categories found.
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
        url: "{{ route('asset-category.status') }}",
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
