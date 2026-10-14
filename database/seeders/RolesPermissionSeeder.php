<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolesPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $guard = 'web';

        /**
         * 🔹 Step 1: Define permissions (based on your routes)
         */
        $permissions = [
            'roles:view', 'roles:create', 'roles:edit', 'roles:delete',
            'permissions:view', 'permissions:create', 'permissions:edit', 'permissions:delete',
            'users:view', 'users:create', 'users:edit', 'users:delete',
        ];

        /**
         * 🔹 Step 2: Create permissions
         */
        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => $guard
            ]);
        }

        /**
         * 🔹 Step 3: Create roles
         */
        $superAdmin = Role::firstOrCreate([
            'name' => 'superadmin',
            'guard_name' => $guard
        ]);

        $admin = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => $guard
        ]);

        /**
         * 🔹 Step 4: Assign permissions to roles
         */

        // Superadmin → ALL permissions
        $superAdmin->syncPermissions(Permission::all());

        // Admin → Limited permissions
        $admin->syncPermissions([
            'roles:view',
            'users:view',
            'users:create',
            'users:edit',
        ]);

        /**
         * 🔹 Step 5: Assign superadmin role to first user
         */
        $user = User::find(1);
        if ($user) {
            $user->assignRole($superAdmin);
        }
    }
}
