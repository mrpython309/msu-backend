@extends('layouts/contentNavbarLayout')

@section('title', 'Roles')

@section('content')
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Roles</h4>
    <div>
      <a href="{{ route('roles.create') }}" class="btn btn-primary">Create Role</a>
    </div>
  </div>
  <div class="row mb-3">
    <div class="col-md-4">
      @include('partials.live-search', ['target' => '.table tbody', 'value' => request('q', '')])
    </div>
  </div>
  <div class="card">
    <div class="table-responsive text-nowrap">
      <table class="table">
        <thead>
          <tr>
            <th>#</th>
            <th>Name</th>
            <th>Description</th>
            <th>Permissions</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($roles as $role)
            <tr>
              <td>{{ $role->id }}</td>
              <td>{{ $role->name }}</td>
              <td>{{ $role->description ?? '-' }}</td>
              <td>
                <span class="badge bg-primary">{{ $permissionCounts[$role->id] ?? 0 }} permissions</span>
              </td>
              <td>
                <div class="dropdown">
                  <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                    <i class="bx bx-dots-vertical-rounded"></i>
                  </button>
                  <div class="dropdown-menu">
                    <a class="dropdown-item" href="{{ route('roles.edit', $role) }}">
                      <i class="bx bx-edit-alt me-1"></i> Edit
                    </a>
                    {{-- <a class="dropdown-item" href="{{ route('access.index', ['role' => $role->id]) }}">
                      <i class="bx bx-key me-1"></i> Manage Permissions
                    </a> --}}
                    <div class="dropdown-divider"></div>
                    <form action="{{ route('roles.destroy', $role->id) }}" method="POST" class="d-inline">
                      @csrf @method('DELETE')
                      <button class="dropdown-item text-danger" onclick="return confirm('Are you sure you want to delete this role?')">
                        <i class="bx bx-trash me-1"></i> Delete
                      </button>
                    </form>
                  </div>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </div>

  <!-- role modal -->
  <div class="modal fade" id="roleModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <form action="{{ route('roles.store') }}" method="POST">
          @csrf
          <div class="modal-header">
            <h5 class="modal-title">Create Role</h5>
            @include('partials.modal-close-button')
          </div>
          <div class="modal-body">
            <div class="mb-3">
              <label class="form-label">Name</label>
              <input name="name" class="form-control" required>
            </div>
          </div>
          <div class="modal-footer"><button class="btn btn-primary">Save</button></div>
        </form>
      </div>
    </div>
  </div>

@endsection
