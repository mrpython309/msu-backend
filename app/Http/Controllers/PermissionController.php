<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $q = trim((string) $request->get('q', ''));

        $permissions = Permission::query()
            ->when($q !== '', fn ($query) => $query->where('name', 'like', "%{$q}%"))
            ->orderBy('name')
            ->paginate(50)
            ->withQueryString();

        return view('master.permissions.index', compact('permissions'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255|unique:permissions,name',
        ]);

        Permission::create($data);
        return redirect()->route('permissions.index')->with('success', 'Permission created.');
    }

    public function destroy(Permission $permission)
    {
        $permission->roles()->detach();
        $permission->users()->detach();
        $permission->delete();
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('permissions.index')->with('success', 'Permission deleted.');
    }

    public function deleteDuplicates()
    {
        $permissions = Permission::all();

        $grouped = $permissions->groupBy(function (Permission $p) {
            $module = RoleController::permissionModuleLabel($p->name);
            $action = strtolower(RoleController::permissionActionLabel($p->name));
            return $module . '_' . $action;
        });

        $deletedCount = 0;

        foreach ($grouped as $group) {
            if ($group->count() <= 1) {
                continue;
            }

            // Prefer dot-notation over colon-notation as canonical (modern standard)
            $sorted = $group->sortBy(function (Permission $p) {
                if (str_contains($p->name, '.')) {
                    return 1;
                }
                if (str_contains($p->name, ':')) {
                    return 2;
                }
                return 3;
            })->values();

            /** @var Permission $canonical */
            $canonical = $sorted[0];

            for ($i = 1; $i < $sorted->count(); $i++) {
                /** @var Permission $duplicate */
                $duplicate = $sorted[$i];

                foreach ($duplicate->roles as $role) {
                    try {
                        if (! $role->hasPermissionTo($canonical)) {
                            $role->givePermissionTo($canonical);
                        }
                    } catch (\Throwable) {
                        // Ignore if already assigned or guard mismatch
                    }
                }

                foreach ($duplicate->users as $user) {
                    try {
                        if (! $user->hasPermissionTo($canonical)) {
                            $user->givePermissionTo($canonical);
                        }
                    } catch (\Throwable) {
                        // Ignore if already assigned
                    }
                }

                $duplicate->roles()->detach();
                $duplicate->users()->detach();
                $duplicate->delete();
                $deletedCount++;
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('permissions.index')->with('success', "Successfully merged and deleted {$deletedCount} duplicate/legacy permissions.");
    }
}
