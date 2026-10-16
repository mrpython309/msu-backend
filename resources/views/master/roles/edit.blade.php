@extends('layouts/contentNavbarLayout')

@section('title', 'Edit Role')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
  <h4 class="fw-bold mb-0">Edit Role: {{ $role->name }}</h4>
  <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">
    <i class="bx bx-arrow-back me-1"></i> Back
  </a>
</div>

<div class="card shadow-sm border-0">
  <div class="card-body p-4">
    <form action="{{ route('roles.update', $role) }}" method="POST">
      @csrf
      @method('PUT')

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
          <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" placeholder="Enter role name" value="{{ old('name', $role->name) }}" required>
          @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
        <div class="col-md-6">
          <label class="form-label fw-semibold">Description</label>
          <input type="text" name="description" class="form-control @error('description') is-invalid @enderror" placeholder="Optional description" value="{{ old('description', $role->description) }}">
          @error('description')
            <div class="invalid-feedback">{{ $message }}</div>
          @enderror
        </div>
      </div>

      {{-- Permissions Section --}}
      <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-semibold mb-0">Assign Permissions</h5>
        <div class="d-flex gap-2">
          <span class="badge bg-info">{{ count($rolePermissions) }} permissions selected</span>
          <button type="button" id="selectAll" class="btn btn-sm btn-outline-primary">
            <i class="bx bx-check-double me-1"></i> Select All
          </button>
        </div>
      </div>

      @php
        // Sort modules alphabetically by key
        $sortedPermissions = collect($permissions)->sortKeys();
      @endphp

      @foreach ($sortedPermissions as $menu => $perms)
        @php
          $menuPermissions = $perms->pluck('name')->toArray();
          $selectedInGroup = array_intersect($rolePermissions, $menuPermissions);
          $groupProgress = count($selectedInGroup) . '/' . count($menuPermissions);
        @endphp
        <div class="border rounded-3 mb-3">
          <div class="bg-light p-3 rounded-top d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
              <span class="fw-semibold text-capitalize">{{ str_replace(['_', '-'], ' ', $menu) }}</span>
              <span class="badge bg-secondary">{{ $groupProgress }}</span>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary select-group" data-target="{{ Str::slug($menu) }}">
              {{ count($selectedInGroup) == count($menuPermissions) ? 'Deselect All' : 'Select All' }}
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
                $assigned = old('permissions', $rolePermissions);
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
      <div class="d-flex justify-content-between align-items-center mt-4">
        <div class="text-muted">
          <i class="bx bx-info-circle me-1"></i>
          Last updated: {{ $role->updated_at->format('M d, Y \a\t g:i A') }}
        </div>
        <div class="d-flex gap-2">
          <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary px-4">
            <i class="bx bx-x me-1"></i> Cancel
          </a>
          <button type="submit" class="btn btn-primary px-4">
            <i class="bx bx-save me-1"></i> Update Role
          </button>
        </div>
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
    const badge = document.querySelector('.badge.bg-info');

    function updateSelectedCount() {
      const checkedCount = [...checkboxes].filter(cb => cb.checked).length;
      badge.textContent = `${checkedCount} permissions selected`;
    }

    function updateSelectAllButton() {
      const allChecked = [...checkboxes].every(cb => cb.checked);
      selectAll.innerHTML = allChecked
        ? '<i class="bx bx-x me-1"></i> Deselect All'
        : '<i class="bx bx-check-double me-1"></i> Select All';
    }

    selectAll.addEventListener('click', () => {
      const allChecked = [...checkboxes].every(cb => cb.checked);
      checkboxes.forEach(cb => cb.checked = !allChecked);
      updateSelectAllButton();
      updateSelectedCount();
      updateGroupButtons();
    });

    function updateGroupButtons() {
      document.querySelectorAll('.select-group').forEach(btn => {
        const target = document.getElementById(btn.dataset.target);
        const groupChecks = target.querySelectorAll('.permission-checkbox');
        const allChecked = [...groupChecks].every(cb => cb.checked);
        btn.innerHTML = allChecked ? 'Deselect All' : 'Select All';
        
        // Update group badge
        const checkedCount = [...groupChecks].filter(cb => cb.checked).length;
        const totalCount = groupChecks.length;
        const badge = btn.parentElement.querySelector('.badge.bg-secondary');
        badge.textContent = `${checkedCount}/${totalCount}`;
      });
    }

    document.querySelectorAll('.select-group').forEach(btn => {
      btn.addEventListener('click', () => {
        const target = document.getElementById(btn.dataset.target);
        const groupChecks = target.querySelectorAll('.permission-checkbox');
        const allChecked = [...groupChecks].every(cb => cb.checked);
        groupChecks.forEach(cb => cb.checked = !allChecked);
        updateSelectAllButton();
        updateSelectedCount();
        updateGroupButtons();
      });
    });

    // Add event listeners to individual checkboxes
    checkboxes.forEach(checkbox => {
      checkbox.addEventListener('change', () => {
        updateSelectAllButton();
        updateSelectedCount();
        updateGroupButtons();
      });
    });

    // Initial update
    updateSelectAllButton();
    updateSelectedCount();
    updateGroupButtons();
  });
</script>
@endpush

{{-- Style tweaks --}}
<style>
  .form-check-label { font-size: 0.92rem; }
  .bg-light { background-color: #f8f9fb !important; }
  .card { border-radius: 1rem; }
  .badge.bg-secondary { font-size: 0.7rem; }
  .badge.bg-info { font-size: 0.8rem; }
</style>
@endsection
