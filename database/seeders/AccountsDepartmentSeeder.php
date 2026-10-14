<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Register Accounts Department roles and permissions to restrict the Make Payment action.
 */
class AccountsDepartmentSeeder extends Seeder
{
    /** @var list<string> */
    public const PERMISSIONS = [
        'purchase-orders:view',
        'purchase-order.view',
        'purchase-order.payment',
        'purchase-order.pay',
        'purchase-orders:payment',
        'purchase-report.view',
        'purchase-management.dashboard',
        'procurement.dashboard:view',
        'procurement.reports:view',
    ];

    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $guard = 'web';

        foreach (self::PERMISSIONS as $name) {
            Permission::findOrCreate($name, $guard);
        }

        $accountsRoles = ['Accounts Department', 'Accounts', 'Finance & Accounts'];
        foreach ($accountsRoles as $roleName) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => $guard]);
            $role->syncPermissions(self::PERMISSIONS);
        }

        $department = Department::firstOrCreate(
            ['department_name' => 'Finance & Accounts'],
            ['status' => 'active']
        );

        // Create sample accounts department user
        $user = User::firstOrCreate(
            ['email' => 'accounts@ks.com'],
            [
                'name' => 'Accounts Officer',
                'password' => Hash::make('123456'),
                'employee_status' => 'active',
            ]
        );

        $user->forceFill([
            'name' => 'Accounts Officer',
            'password' => Hash::make('123456'),
            'employee_status' => 'active',
        ])->save();

        if (! $user->hasRole('Accounts Department')) {
            $user->assignRole('Accounts Department');
        }

        // Attach to Finance & Accounts department
        if (! $user->departments()->where('departments.id', $department->id)->exists()) {
            $user->departments()->attach($department->id);
        }

        // Also ensure L3 Finance user has Accounts Department role & permissions
        $l3User = User::where('email', 'l3@ks.com')->first();
        if ($l3User && ! $l3User->hasRole('Accounts Department')) {
            $l3User->assignRole('Accounts Department');
            if (! $l3User->departments()->where('departments.id', $department->id)->exists()) {
                $l3User->departments()->attach($department->id);
            }
        }

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
    }
}
