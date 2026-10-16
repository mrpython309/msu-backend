<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class MaterialModulePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Define the permissions
        $basePermissions = [
            'material_requests:view',
            'material_requests:create',
            'material_requests:edit',
            'material_requests:delete',
            'material_issues:view',
            'material_issues:create',
            'material_issues:edit',
            'material_issues:delete',
        ];

        $approveCancel = [
            'material_requests:approve',
            'material_requests:cancel',
        ];

        foreach (array_merge($basePermissions, $approveCancel) as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        foreach (['Super Admin', 'superadmin', 'Admin', 'admin'] as $roleName) {
            $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();
            if ($role) {
                $role->givePermissionTo($basePermissions);
            }
        }

        foreach (['Super Admin', 'superadmin'] as $roleName) {
            $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();
            if ($role) {
                $role->givePermissionTo($approveCancel);
            }
        }

        foreach (['Admin', 'admin'] as $roleName) {
            $role = Role::where('name', $roleName)->where('guard_name', 'web')->first();
            if ($role) {
                $role->revokePermissionTo($approveCancel);
            }
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
