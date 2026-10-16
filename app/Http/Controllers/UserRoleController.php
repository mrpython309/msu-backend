<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserRoleController extends Controller
{
    public function edit(User $user)
    {
        $roles = Role::orderBy('name')->get();
        $permissions = RoleController::permissionsGroupedByModule();
        $userPermissions = $user->permissions->pluck('name')->toArray();

        return view('master.users.roles', compact('user', 'roles', 'permissions', 'userPermissions'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,name',
            'permissions' => 'nullable|array',
            'permissions.*' => 'exists:permissions,name',
        ]);

        $user->syncRoles($data['roles'] ?? []);

        $expandedPermissions = RoleController::expandPermissionAliases($data['permissions'] ?? []);
        $user->syncPermissions($expandedPermissions);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('users.index')->with('success', 'User roles and direct permissions updated successfully.');
    }
}
