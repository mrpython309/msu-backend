@extends('layouts/contentNavbarLayout')

@section('title', 'Asset Names')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
  Asset Names
</h4>

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h5 class="mb-0">Asset Name List</h5>
    @can('asset-types:create')
    <a href="{{ route('asset-names.create') }}" class="btn btn-primary">Create Asset Name</a>
    @endcan
  </div>

  <div class="card-body border-bottom">
    <form method="GET" action="{{ route('asset-names.index') }}" class="row g-3">
      <div class="col-md-6">
        <input type="text" name="q" class="form-control" placeholder="Search asset name" value="{{ $q ?? '' }}">
      </div>
      <div class="col-md-3">
        <select name="status" class="form-select">
          <option value="">All Status</option>
          <option value="active" {{ ($status ?? '') === 'active' ? 'selected' : '' }}>Active</option>
          <option value="inactive" {{ ($status ?? '') === 'inactive' ? 'selected' : '' }}>Inactive</option>
        </select>
      </div>
      <div class="col-md-3">
        <button class="btn btn-outline-primary" type="submit">Search</button>
      </div>
    </form>
  </div>

  <div class="table-responsive">
    <table class="table">
      <thead>
        <tr>
          <th>S.No</th>
          <th>Asset Name</th>
          <th>Status</th>
          <th width="180">Actions</th>
        </tr>
      </thead>
      <tbody>
        @forelse($assetNames as $assetName)
        <tr>
          <td>{{ ($assetNames->firstItem() ?? 0) + $loop->index }}</td>
          <td>{{ $assetName->name }}</td>
          <td>
            @if($assetName->status === 'active')
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
            <a href="{{ route('asset-names.edit', $assetName->id) }}" class="dropdown-item"><i class="bx bx-edit-alt me-1"></i> Edit</a>
            @endcan
            @can('asset-types:delete')
            <form action="{{ route('asset-names.destroy', $assetName->id) }}" method="POST" class="d-inline" id="delete-form-{{ $assetName->id }}">
              @csrf
              @method('DELETE')
              <button type="button" class="dropdown-item text-danger" onclick="confirmDelete({{ $assetName->id }})"><i class="bx bx-trash me-1"></i> Delete</button>
            </form>
            @endcan
          
                                </div>
                            </div>
                        </td>
        </tr>
        @empty
        <tr>
          <td colspan="4" class="text-center">No Asset Names found.</td>
        </tr>
        @endforelse
      </tbody>
    </table>
  </div>

  <div class="card-body">
    {{ $assetNames->links() }}
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

function updateAssetNameStatus(el) {
  const id = el.getAttribute('data-id');
  const status = el.checked ? 'active' : 'inactive';
  $.ajax({
    url: "{{ route('asset-names.status') }}",
    type: "PUT",
    data: {
      _token: "{{ csrf_token() }}",
      id: id,
      status: status
    },
    error: function() {
      el.checked = !el.checked;
    }
  });
}
</script>
@endsection

