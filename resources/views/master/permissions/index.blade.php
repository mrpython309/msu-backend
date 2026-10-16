@extends('layouts/contentNavbarLayout')

@section('title', 'Permissions')

@section('content')
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Permissions</h4>
    <div class="d-flex gap-2">
      <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#permissionModal"><i class="bx bx-plus me-1"></i> Create Permission</button>
    </div>
  </div>
  <div class="row mb-3">
    <div class="col-md-6">
      <form method="GET" action="{{ route('permissions.index') }}" class="d-flex gap-2">
        <input type="search" name="q" class="form-control" placeholder="Search permissions (e.g. purchase-order, indent, grn)" value="{{ request('q') }}">
        <button class="btn btn-outline-primary" type="submit">Search</button>
        @if (request('q'))
          <a href="{{ route('permissions.index') }}" class="btn btn-outline-secondary">Clear</a>
        @endif
      </form>
    </div>
  </div>
  <div class="card">
    <div class="table-responsive text-nowrap">
      <table class="table">
        <thead>
          <tr>
            <th>#</th>
            <th>Name</th>
            <th>Module</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($permissions as $p)
            @php
              $module = str_contains($p->name, ':')
                  ? explode(':', $p->name, 2)[0]
                  : (str_contains($p->name, '.') ? explode('.', $p->name)[0] : 'general');
            @endphp
            <tr>
              <td>{{ $p->id }}</td>
              <td><code>{{ $p->name }}</code></td>
              <td><span class="badge bg-label-secondary">{{ $module }}</span></td>
              <td>
                <form action="{{ route('permissions.destroy', $p->id) }}" method="POST" class="d-inline">
                  @csrf @method('DELETE')
                  <button class="btn btn-sm btn-danger">Delete</button>
                </form>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="4" class="text-center text-muted py-4">No permissions found.</td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
    @if ($permissions->hasPages())
      <div class="card-footer">{{ $permissions->links() }}</div>
    @endif
  </div>

  <div class="modal fade" id="permissionModal" tabindex="-1">
    <div class="modal-dialog">
      <div class="modal-content">
        <form action="{{ route('permissions.store') }}" method="POST">
          @csrf
          <div class="modal-header">
            <h5>Create Permission</h5>
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
