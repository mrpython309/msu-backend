<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class QualityInspectionPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $guard = 'web';

        $allPermissions = [
            'quality-inspection.view',
            'quality-inspections:view',
            'quality-inspection.perform',
            'quality-inspections:perform',
        ];

        foreach ($allPermissions as $permName) {
            Permission::findOrCreate($permName, $guard);
        }

        $rolesToGrant = [
            'Quality Inspector',
            'Quality Control',
            'Store Incharge',
            'Store Officer',
            'Store Manager',
            'Purchase Officer',
            'Procurement Manager',
            'superadmin',
            'Super Admin',
            'Admin',
            'admin',
        ];

        foreach ($rolesToGrant as $roleName) {
            $role = Role::where('name', $roleName)->where('guard_name', $guard)->first();
            if ($role) {
                $role->givePermissionTo($allPermissions);
            }
        }
    }
}
