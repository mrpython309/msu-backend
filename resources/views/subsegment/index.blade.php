@extends('layouts/contentNavbarLayout')

@section('title', 'Sub Segments')

@section('content')
<h4 class="py-3 breadcrumb-wrapper mb-4">
  Sub Segments
</h4>

<div class="card">
    <div class="card-header d-flex justify-content-between">
        <h5 class="mb-0">Sub Segment List</h5>
        <a href="{{ route('subsegment.create') }}" class="btn btn-primary">
            Add Sub Segment
        </a>
    </div>

    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Segment</th>
                    <th>Sub Segment</th>
                    <th>Status</th>
                    <th width="180">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($subsegments as $subsegment)
                    <tr>
                        <td>{{ $subsegment->id }}</td>
                        <td>
                            {{ $subsegment->segment?->name ?? '-' }}
                        </td>
                        <td>{{ $subsegment->subsegment_name }}</td>
                        <td>
                            @if($subsegment->status === 'active')
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

                            <a href="{{ route('subsegment.edit', $subsegment->id) }}"
                               class="dropdown-item"><i class="bx bx-edit-alt me-1"></i> Edit</a>

                            <form action="{{ route('subsegment.destroy', $subsegment->id) }}"
                                  method="POST"
                                  class="d-inline"
                                  id="delete-form-{{ $subsegment->id }}">
                                @csrf
                                @method('DELETE')
                                <button type="button"
                                        class="dropdown-item text-danger"
                                        onclick="confirmDelete({{ $subsegment->id }})"><i class="bx bx-trash me-1"></i> Delete</button>
                            </form>
                        
                                </div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center">
                            No sub segments found.
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

function updateSubSegmentStatus(el) {

    let subsegmentId = el.getAttribute('data-id');
    let status = el.checked ? 'active' : 'inactive';

    $.ajax({
        url: "{{ route('subsegment.status') }}",
        type: "PUT",
        data: {
            _token: "{{ csrf_token() }}",
            id: subsegmentId,
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
