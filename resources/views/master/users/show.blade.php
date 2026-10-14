@extends('layouts/contentNavbarLayout')

@section('title', 'User Details')

@section('content')
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">User Details</h4>
    <div>
      <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
        <i class="bx bx-arrow-back me-1"></i> Back to Users
      </a>
      @if(auth()->user()->hasRole('superadmin') || auth()->user()->can('users:edit'))
        <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary">
          <i class="bx bx-edit me-1"></i> Edit User
        </a>
      @endif
    </div>
  </div>

  <div class="row">
    <div class="col-md-8">
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0">User Information</h5>
        </div>
        <div class="card-body">
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label fw-bold">Full Name</label>
              <p class="form-control-plaintext">{{ $user->name }}</p>
            </div>
            <div class="col-md-6 mb-3">
              <label class="form-label fw-bold">Email Address</label>
              <p class="form-control-plaintext">{{ $user->email }}</p>
            </div>
          </div>
          
          <div class="row">
            <div class="col-md-12 mb-3">
              <label class="form-label fw-bold">Store Access</label>
              <div>
                @if(empty($user->stores) || $user->stores === null)
                  <span class="badge bg-success fs-6">All Stores</span>
                  <p class="text-muted mt-2">This user has access to all stores in the system.</p>
                @else
                  <span class="badge bg-primary fs-6">{{ count($user->stores) }} Store(s)</span>
                  <div class="mt-2">
                    @foreach($user->accessibleStores() as $store)
                      <span class="badge bg-light text-dark me-1 mb-1">{{ $store->name }}</span>
                    @endforeach
                  </div>
                  <p class="text-muted mt-2">This user has limited access to the selected stores only.</p>
                @endif
              </div>
            </div>
          </div>

          <div class="row">
            <div class="col-md-12 mb-3">
              <label class="form-label fw-bold">Roles & Permissions</label>
              <div>
                @if($user->roles->count() > 0 || $user->permissions->count() > 0)
                  @foreach($user->roles as $role)
                    <span class="badge bg-warning text-dark me-1 mb-1" title="Role"><i class="bx bx-shield me-1"></i>{{ $role->name }}</span>
                  @endforeach
                  @foreach($user->permissions as $permission)
                    <span class="badge bg-info text-dark me-1 mb-1" title="Direct Permission"><i class="bx bx-key me-1"></i>{{ $permission->name }}</span>
                  @endforeach
                @else
                  <span class="text-muted">No roles or direct permissions assigned</span>
                @endif
                <div class="mt-2">
                  <a href="{{ route('users.roles.edit', $user->id) }}" class="btn btn-sm btn-outline-warning">
                    <i class="bx bx-user-check me-1"></i> Manage Roles & Permissions
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card">
        <div class="card-header">
          <h5 class="mb-0">Account Status</h5>
        </div>
        <div class="card-body">
          <div class="mb-3">
            <label class="form-label fw-bold">User ID</label>
            <p class="form-control-plaintext">#{{ $user->id }}</p>
          </div>
          
          <div class="mb-3">
            <label class="form-label fw-bold">Email Verified</label>
            <p class="form-control-plaintext">
              @if($user->email_verified_at)
                <span class="badge bg-success">Verified</span>
                <small class="d-block text-muted">{{ $user->email_verified_at->format('M d, Y h:i A') }}</small>
              @else
                <span class="badge bg-warning">Not Verified</span>
              @endif
            </p>
          </div>
          
          <div class="mb-3">
            <label class="form-label fw-bold">Account Created</label>
            <p class="form-control-plaintext">
              {{ $user->created_at->format('M d, Y') }}
              <small class="d-block text-muted">{{ $user->created_at->format('h:i A') }}</small>
            </p>
          </div>
          
          <div class="mb-3">
            <label class="form-label fw-bold">Last Updated</label>
            <p class="form-control-plaintext">
              {{ $user->updated_at->format('M d, Y') }}
              <small class="d-block text-muted">{{ $user->updated_at->format('h:i A') }}</small>
            </p>
          </div>
          
          @if($user->id === auth()->id())
            <div class="alert alert-info">
              <i class="bx bx-info-circle me-2"></i>
              <strong>This is your account</strong>
            </div>
          @endif
        </div>
      </div>

      <div class="card mt-3">
        <div class="card-header">
          <h5 class="mb-0">Quick Actions</h5>
        </div>
        <div class="card-body">
          @if(auth()->user()->hasRole('superadmin') || auth()->user()->can('users:edit'))
            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-outline-primary btn-sm d-block mb-2">
              <i class="bx bx-edit me-1"></i> Edit User Details
            </a>
            <a href="{{ route('users.roles.edit', $user->id) }}" class="btn btn-outline-warning btn-sm d-block mb-2">
              <i class="bx bx-user-check me-1"></i> Manage Roles
            </a>
          @endif
          
          @if((auth()->user()->hasRole('superadmin') || auth()->user()->can('users:delete')) && $user->id !== auth()->id())
            <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="d-inline-block w-100" 
                  onsubmit="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">
              @csrf
              @method('DELETE')
              <button type="submit" class="btn btn-outline-danger btn-sm d-block">
                <i class="bx bx-trash me-1"></i> Delete User
              </button>
            </form>
          @endif
        </div>
      </div>
    </div>
  </div>
@endsection
