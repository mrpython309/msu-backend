<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('cost_centres')) {
            Schema::create('cost_centres', function (Blueprint $table) {
                $table->id();
                $table->string('cost_centre_name');
                $table->text('cost_centre_description')->nullable();
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->timestamps();
                $table->unique('cost_centre_name');
            });
        }

        $guard = 'web';
        $permissions = [
            'cost-centres:view',
            'cost-centres:create',
            'cost-centres:edit',
            'cost-centres:delete',
        ];

        if (Schema::hasTable('permissions') && class_exists(Permission::class)) {
            foreach ($permissions as $permission) {
                Permission::firstOrCreate(['name' => $permission, 'guard_name' => $guard]);
            }

            foreach (['Super Admin', 'superadmin', 'Admin', 'admin'] as $roleName) {
                $role = Role::where('name', $roleName)->where('guard_name', $guard)->first();
                if ($role) {
                    $role->givePermissionTo($permissions);
                }
            }

            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cost_centres');
    }
};
