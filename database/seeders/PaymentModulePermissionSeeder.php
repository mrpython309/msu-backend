<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class PaymentModulePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $guard = 'web';

        $allPermissions = [
            'payment.view',
            'payments:view',
            'payment.create',
            'payments:create',
            'payment.approve',
            'payments:approve',
            'payment.pay',
            'payments:pay',
        ];

        foreach ($allPermissions as $permName) {
            Permission::findOrCreate($permName, $guard);
        }

        // Assign to Purchase Officer
        $purchaseOfficer = Role::where('name', 'Purchase Officer')->where('guard_name', $guard)->first();
        if ($purchaseOfficer) {
            $purchaseOfficer->givePermissionTo([
                'payment.view',
                'payments:view',
                'payment.create',
                'payments:create',
            ]);
            // Revoke any accidental approve/pay rights from Purchase Officer
            foreach (['payment.approve', 'payments:approve', 'payment.pay', 'payments:pay'] as $restricted) {
                if ($purchaseOfficer->hasPermissionTo($restricted)) {
                    $purchaseOfficer->revokePermissionTo($restricted);
                }
            }
        }

        // Assign to Accounts Department roles
        foreach (['Accounts Department', 'Accounts', 'Finance & Accounts', 'Accounts Manager', 'Accounts Officer'] as $accountsRoleName) {
            $role = Role::where('name', $accountsRoleName)->where('guard_name', $guard)->first();
            if ($role) {
                $role->givePermissionTo($allPermissions);
            }
        }

        // Assign to Admins
        foreach (['superadmin', 'Super Admin', 'Admin', 'admin'] as $adminRoleName) {
            $admin = Role::where('name', $adminRoleName)->where('guard_name', $guard)->first();
            if ($admin) {
                $admin->givePermissionTo($allPermissions);
            }
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
