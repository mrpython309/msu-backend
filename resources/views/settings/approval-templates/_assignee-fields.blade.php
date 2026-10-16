@php
    $type = old('assignee_type', $step->assignee_type ?? 'user');
@endphp
<div class="mb-3">
    <label class="form-label">Assignee Type <span class="text-danger">*</span></label>
    <select name="assignee_type" class="form-select" required>
        <option value="user" @selected($type === 'user')>Specific User</option>
        <option value="role" @selected($type === 'role')>Role</option>
        <option value="permission" @selected($type === 'permission')>Permission</option>
    </select>
</div>

<div data-assignee-panel="user" class="mb-3 {{ $type === 'user' ? '' : 'd-none' }}">
    <label class="form-label">Approver User <span class="text-danger">*</span></label>
    <select name="approver_user_id" class="form-select" {{ $type === 'user' ? 'required' : '' }}>
        <option value="">Select user</option>
        @foreach ($users as $user)
            @php
                $roleNames = $user->roles?->pluck('name')->filter()->implode(', ');
            @endphp
            <option value="{{ $user->id }}" @selected((int) old('approver_user_id', $step->approver_user_id ?? 0) === (int) $user->id)>
                {{ $user->name }} ({{ $user->email }})@if ($roleNames) — {{ $roleNames }}@endif
            </option>
        @endforeach
    </select>
    <small class="text-muted">Any user can be assigned, including Admin / Super Admin.</small>
</div>

<div data-assignee-panel="role" class="mb-3 {{ $type === 'role' ? '' : 'd-none' }}">
    <label class="form-label">Approver Role <span class="text-danger">*</span></label>
    <select name="assignee_role" class="form-select" {{ $type === 'role' ? 'required' : '' }}>
        <option value="">Select role</option>
        @foreach ($roles as $role)
            <option value="{{ $role }}" @selected(old('assignee_role', $step->assignee_role ?? '') === $role)>{{ $role }}</option>
        @endforeach
    </select>
</div>

<div data-assignee-panel="permission" class="mb-3 {{ $type === 'permission' ? '' : 'd-none' }}">
    <label class="form-label">Approver Permission <span class="text-danger">*</span></label>
    <select name="assignee_permission" class="form-select" {{ $type === 'permission' ? 'required' : '' }}>
        <option value="">Select permission</option>
        @foreach ($permissions as $permission)
            <option value="{{ $permission }}" @selected(old('assignee_permission', $step->assignee_permission ?? '') === $permission)>
                {{ $permission }}
            </option>
        @endforeach
    </select>
</div>

<div class="form-check">
    <input class="form-check-input" type="checkbox" name="is_mandatory" value="1"
           id="mandatory-{{ $prefix }}" @checked(old('is_mandatory', $step->is_mandatory ?? true))>
    <label class="form-check-label" for="mandatory-{{ $prefix }}">Mandatory</label>
</div>
