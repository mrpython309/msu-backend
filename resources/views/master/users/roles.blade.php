@extends('layouts/contentNavbarLayout')

@section('title', 'Manage Roles & Permissions')

@section('content')
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Manage Roles &amp; Direct Permissions for <span class="text-primary">{{ $user->name }}</span></h4>
    <div>
      <a href="{{ route('users.show', $user->id) }}" class="btn btn-outline-secondary me-2"><i class="bx bx-user me-1"></i> View Profile</a>
      <a href="{{ route('users.index') }}" class="btn btn-secondary"><i class="bx bx-arrow-back me-1"></i> Back to Users</a>
    </div>
  </div>

  <form method="POST" action="{{ route('users.roles.update', $user->id) }}">
    @csrf
    {{-- Section 1: Assign Roles --}}
    <div class="card p-4 mb-4">
      <h5 class="fw-semibold mb-3"><i class="bx bx-shield text-primary me-2"></i>Assign Roles</h5>
      <div class="row g-3">
        @foreach ($roles as $role)
          <div class="col-md-3 col-sm-6">
            <div class="form-check border p-2 rounded-2 bg-light">
              <input class="form-check-input ms-1" type="checkbox" name="roles[]" value="{{ $role->name }}"
                id="role_{{ $role->id }}" {{ $user->hasRole($role->name) ? 'checked' : '' }}>
              <label class="form-check-label fw-medium ms-2" for="role_{{ $role->id }}">{{ $role->name }}</label>
            </div>
          </div>
        @endforeach
      </div>
    </div>

    {{-- Section 2: Assign Direct Permissions --}}
    <div class="card p-4 mb-4">
      <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
          <h5 class="fw-semibold mb-1"><i class="bx bx-key text-warning me-2"></i>Assign Direct Permissions</h5>
        </div>
        <div class="d-flex gap-2 align-items-center">
          <span class="badge bg-info">{{ count($userPermissions) }} permissions selected</span>
          <button type="button" id="selectAll" class="btn btn-sm btn-outline-primary">
            <i class="bx bx-check-double me-1"></i> Select All
          </button>
        </div>
      </div>

      @php
        $sortedPermissions = collect($permissions)->sortKeys();
      @endphp

      @foreach ($sortedPermissions as $menu => $perms)
        @php
          $menuPermissions = $perms->pluck('name')->toArray();
          $selectedInGroup = array_intersect($userPermissions, $menuPermissions);
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
                $assigned = old('permissions', $userPermissions);
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
                    @if(str_contains(strtolower($action), 'create') && str_contains(strtolower($menu), 'grn'))
                      <span class="badge bg-label-success ms-1">GRN Create</span>
                    @elseif(str_contains(strtolower($action), 'perform') && str_contains(strtolower($menu), 'inspection'))
                      <span class="badge bg-label-warning ms-1">Inspect</span>
                    @endif
                  </label>
                </div>
              </div>
            @endforeach
          </div>
        </div>
      @endforeach

      <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="{{ route('users.index') }}" class="btn btn-outline-secondary px-4">
          <i class="bx bx-x me-1"></i> Cancel
        </a>
        <button type="submit" class="btn btn-primary px-4">
          <i class="bx bx-save me-1"></i> Save Roles &amp; Permissions
        </button>
      </div>
    </div>
  </form>

@endsection

@push('scripts')
<script>
  document.addEventListener("DOMContentLoaded", () => {
    const selectAll = document.getElementById('selectAll');
    const checkboxes = document.querySelectorAll('.permission-checkbox');
    const badge = document.querySelector('.badge.bg-info');

    function updateSelectedCount() {
      const checkedCount = [...checkboxes].filter(cb => cb.checked).length;
      if (badge) badge.textContent = `${checkedCount} permissions selected`;
    }

    function updateSelectAllButton() {
      if (!selectAll) return;
      const allChecked = checkboxes.length > 0 && [...checkboxes].every(cb => cb.checked);
      selectAll.innerHTML = allChecked
        ? '<i class="bx bx-x me-1"></i> Deselect All'
        : '<i class="bx bx-check-double me-1"></i> Select All';
    }

    if (selectAll) {
      selectAll.addEventListener('click', () => {
        const allChecked = checkboxes.length > 0 && [...checkboxes].every(cb => cb.checked);
        checkboxes.forEach(cb => cb.checked = !allChecked);
        updateSelectAllButton();
        updateSelectedCount();
        updateGroupButtons();
      });
    }

    function updateGroupButtons() {
      document.querySelectorAll('.select-group').forEach(btn => {
        const target = document.getElementById(btn.dataset.target);
        if (!target) return;
        const groupChecks = target.querySelectorAll('.permission-checkbox');
        const allChecked = groupChecks.length > 0 && [...groupChecks].every(cb => cb.checked);
        btn.innerHTML = allChecked ? 'Deselect All' : 'Select All';
        
        const checkedCount = [...groupChecks].filter(cb => cb.checked).length;
        const totalCount = groupChecks.length;
        const groupBadge = btn.parentElement.querySelector('.badge.bg-secondary');
        if (groupBadge) groupBadge.textContent = `${checkedCount}/${totalCount}`;
      });
    }

    document.querySelectorAll('.select-group').forEach(btn => {
      btn.addEventListener('click', () => {
        const target = document.getElementById(btn.dataset.target);
        if (!target) return;
        const groupChecks = target.querySelectorAll('.permission-checkbox');
        const allChecked = groupChecks.length > 0 && [...groupChecks].every(cb => cb.checked);
        groupChecks.forEach(cb => cb.checked = !allChecked);
        updateSelectAllButton();
        updateSelectedCount();
        updateGroupButtons();
      });
    });

    checkboxes.forEach(checkbox => {
      checkbox.addEventListener('change', () => {
        updateSelectAllButton();
        updateSelectedCount();
        updateGroupButtons();
      });
    });

    updateSelectAllButton();
    updateSelectedCount();
    updateGroupButtons();
  });
</script>
@endpush

<style>
  .form-check-label { font-size: 0.92rem; }
  .bg-light { background-color: #f8f9fb !important; }
  .card { border-radius: 1rem; }
  .badge.bg-secondary { font-size: 0.7rem; }
  .badge.bg-info { font-size: 0.8rem; }
</style>
