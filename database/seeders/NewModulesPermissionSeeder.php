<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class NewModulesPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $guard = 'web';
        $permissions = [
            'item-stocks:view',
            'stock-adjustments:view', 'stock-adjustments:create', 'stock-adjustments:edit', 'stock-adjustments:delete',
            'material_requests:view', 'material_requests:create', 'material_requests:edit', 'material_requests:delete', 'material_requests:approve', 'material_requests:cancel',
            'material_issues:view', 'material_issues:create', 'material_issues:edit', 'material_issues:delete',
            'msu-purchase-order:view', 'msu-purchase-order:create', 'msu-purchase-order:edit', 'msu-purchase-order:delete', 'msu-purchase-order:approve', 'msu-purchase-order:cancel',
            'msu-work-order:view', 'msu-work-order:create', 'msu-work-order:edit', 'msu-work-order:delete', 'msu-work-order:approve', 'msu-work-order:cancel',
            'msu-grn:view', 'msu-grn:create', 'msu-grn:edit', 'msu-grn:delete', 'msu-grn:approve', 'msu-grn:cancel',
            'miscellaneous-charge:view', 'miscellaneous-charge:create', 'miscellaneous-charge:edit', 'miscellaneous-charge:delete', 'miscellaneous-charge:status',
            'unit:view', 'unit:create', 'unit:edit', 'unit:delete', 'unit:status',
            'hsn:view', 'hsn:create', 'hsn:edit', 'hsn:delete', 'hsn:status',
            'shipping-detail:view', 'shipping-detail:create', 'shipping-detail:edit', 'shipping-detail:delete', 'shipping-detail:status',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => $guard]);
        }

        $allPermissions = Permission::all();

        // Assign to Super Admin and Admin
        foreach (['Super Admin', 'superadmin', 'Admin', 'admin'] as $roleName) {
            $role = Role::where('name', $roleName)->where('guard_name', $guard)->first();
            if ($role) {
                $role->syncPermissions($allPermissions);
            }
        }

        // Approve/Cancel are assigned explicitly per role/user — not on every Admin by default.
        $restricted = [
            'material_requests:approve',
            'material_requests:cancel',
            'msu-purchase-order:approve',
            'msu-purchase-order:cancel',
            'msu-work-order:approve',
            'msu-work-order:cancel',
        ];
        foreach (['Admin', 'admin'] as $roleName) {
            $role = Role::where('name', $roleName)->where('guard_name', $guard)->first();
            if ($role) {
                $role->revokePermissionTo($restricted);
            }
        }
        
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
