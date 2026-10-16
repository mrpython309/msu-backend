<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Ensure Admin / Super Admin have every procurement permission,
 * and Super Admin has ALL permissions in the system.
 */
class AdminProcurementPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $guard = 'web';
        $permissions = ProcurementPermissionSeeder::PERMISSIONS;

        foreach ($permissions as $name) {
            Permission::findOrCreate($name, $guard);
        }

        $adminRoles = ['Admin', 'admin'];
        foreach ($adminRoles as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => $guard]);
            $role->givePermissionTo($permissions);
        }

        // Super Admin gets every permission in the system (procurement + all modules).
        $superRoles = ['Super Admin', 'superadmin'];
        $allPermissions = Permission::query()->where('guard_name', $guard)->get();

        foreach ($superRoles as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => $guard]);
            $role->syncPermissions($allPermissions);
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command?->info('Admin: procurement permissions granted. Super Admin: all '.$allPermissions->count().' permissions synced.');
    }
}
