@extends('layouts/contentNavbarLayout')

@section('title', 'Create Role')

@section('content')


<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="fw-bold mb-0">Create New Role</h4>
  <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">
    <i class="bx bx-arrow-back me-1"></i> Back
  </a>
</div>

<div class="card shadow-sm border-0">
  <div class="card-body p-4">
    <form action="{{ route('roles.store') }}" method="POST">
      @csrf

      {{-- Display Errors --}}
      @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <h6 class="alert-heading mb-2">Please fix the following errors:</h6>
          <ul class="mb-0">
            @foreach ($errors->all() as $error)
              <li>{{ $error }}</li>
            @endforeach
          </ul>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      @endif

      {{-- Role Info --}}
      <div class="row g-3 mb-4">
        <div class="col-md-6">
          <label class="form-label fw-semibold">Role Name <span class="text-danger">*</span></label>
          <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="Enter role name" value="{{ old('name') }}" required>
          @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Description</label>
          <input type="text" name="description" class="form-control @error('description') is-invalid @enderror" placeholder="Optional description" value="{{ old('description') }}">
          @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>

      {{-- Permissions Section --}}
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-semibold mb-0">Assign Permissions</h5>
        <button type="button" id="selectAll" class="btn btn-sm btn-outline-primary">
          <i class="bx bx-check-double me-1"></i> Select All
        </button>
      </div>

            {{-- Mobile Application Modules Highlighted Section --}}
      <div class="border rounded-3 mb-4 p-3 shadow-sm" style="background: linear-gradient(135deg, #f0f4ff 0%, #e6edff 100%); border-left: 5px solid #4157E6 !important;">
        <div class="d-flex justify-content-between align-items-center mb-2 flex-wrap gap-2">
          <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary px-2 py-1"><i class="bx bx-mobile-alt me-1"></i> Mobile Application Modules</span>
            <span class="fw-semibold text-dark">Mobile Access Controls</span>
          </div>
          <small class="text-muted">Directly controls modules visible on the Mobile Application</small>
        </div>
        <div class="row g-2 pt-2">
          @php
            $mobileModuleItems = [
              'dashboard:view' => ['name' => '📱 Mobile Dashboard', 'desc' => 'Dashboard overview and metrics', 'color' => 'primary'],
              'asset:view' => ['name' => '📦 Mobile Assets', 'desc' => 'Asset list and asset details', 'color' => 'success'],
              'audit:view' => ['name' => '📋 Mobile Asset Audit', 'desc' => 'QR scanner and audit logs', 'color' => 'warning'],
              'support-ticket:view' => ['name' => '🎫 Mobile Support Tickets', 'desc' => 'Ticket management module', 'color' => 'info'],
              'support-ticket:create' => ['name' => '➕ Mobile Create Ticket', 'desc' => 'Allow submitting new tickets', 'color' => 'danger'],
            ];
            $assignedMobile = old('permissions', $rolePermissions ?? ($userPermissions ?? []));
          @endphp
          @foreach($mobileModuleItems as $mKey => $mInfo)
            @php
              $isCheckedMobile = in_array($mKey, $assignedMobile, true);
            @endphp
            <div class="col-md-4 col-sm-6">
              <div class="form-check p-2 bg-white rounded border">
                <input
                  type="checkbox"
                  class="form-check-input permission-checkbox ms-1"
                  id="mobile-perm-{{ md5($mKey) }}"
                  name="permissions[]"
                  value="{{ $mKey }}"
                  {{ $isCheckedMobile ? 'checked' : '' }}
                >
                <label class="form-check-label ms-2" for="mobile-perm-{{ md5($mKey) }}">
                  <strong class="text-{{ $mInfo['color'] }}">{{ $mInfo['name'] }}</strong>
                  <small class="d-block text-muted" style="font-size: 0.72rem;">{{ $mInfo['desc'] }}</small>
                </label>
              </div>
            </div>
          @endforeach
        </div>
      </div>

      @php
        // Sort modules alphabetically by key
        $sortedPermissions = collect($permissions)->sortKeys();
      @endphp

      @foreach ($sortedPermissions as $menu => $perms)
        <div class="border rounded-3 mb-3">
          <div class="bg-light p-3 rounded-top d-flex justify-content-between align-items-center">
            <span class="fw-semibold text-capitalize">{{ str_replace(['_', '-'], ' ', $menu) }}</span>
            <button type="button" class="btn btn-sm btn-outline-secondary select-group" data-target="{{ Str::slug($menu) }}">
              Select All
            </button>
          </div>

          <div class="row px-3 py-3" id="{{ Str::slug($menu) }}">
            @foreach ($perms as $permission)
              @php
                $permName = $permission->name;
                $action = \App\Http\Controllers\RoleController::permissionActionLabel($permName);
                if ($action === '') {
                    continue;
                }
                $assigned = old('permissions', []);
                $aliases = $permission->alias_names ?? [];
                $isChecked = in_array($permName, $assigned, true)
                    || ! empty(array_intersect($aliases, $assigned));
              @endphp
              <div class="col-md-3 col-sm-6 mb-2">
                <div class="form-check">
                  <input
                    type="checkbox"
                    class="form-check-input permission-checkbox"
                    id="perm-{{ md5($permName) }}"
                    name="permissions[]"
                    value="{{ $permName }}"
                    {{ $isChecked ? 'checked' : '' }}
                  >
                  <label class="form-check-label text-muted" for="perm-{{ md5($permName) }}">
                    {{ $action }}
                  </label>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      @endforeach

      {{-- Submit --}}
      <div class="text-end mt-4">
        <button type="submit" class="btn btn-primary px-4">
          <i class="bx bx-save me-1"></i> Create Role
        </button>
      </div>
    </form>
  </div>
</div>

{{-- JS for Select All Functionality --}}
@push('scripts')
<script>
  document.addEventListener("DOMContentLoaded", () => {
    
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.permission-checkbox');

    selectAll.addEventListener('click', () => {
      const allChecked = [...checkboxes].every(cb => cb.checked);
      checkboxes.forEach(cb => cb.checked = !allChecked);
      selectAll.innerHTML = allChecked
        ? '<i class="bx bx-check-double me-1"></i> Select All'
        : '<i class="bx bx-x me-1"></i> Deselect All';
    });

    document.querySelectorAll('.select-group').forEach(btn => {
      btn.addEventListener('click', () => {
        const target = document.getElementById(btn.dataset.target);
        const groupChecks = target.querySelectorAll('.permission-checkbox');
        const allChecked = [...groupChecks].every(cb => cb.checked);
        groupChecks.forEach(cb => cb.checked = !allChecked);
        btn.innerHTML = allChecked ? 'Select All' : 'Deselect All';
      });
    });
  });
</script>
@endpush



{{-- Style tweaks --}}
<style>
  .form-check-label { font-size: 0.92rem; }
  .bg-light { background-color: #f8f9fb !important; }
  .card { border-radius: 1rem; }
</style>
@endsection
