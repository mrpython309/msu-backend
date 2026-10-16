<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('msu_grns', function (Blueprint $table) {
            if (! Schema::hasColumn('msu_grns', 'asset_status')) {
                $table->string('asset_status', 30)->default('pending')->after('is_added_to_asset');
            }

            if (! Schema::hasColumn('msu_grns', 'asset_cancel_reason')) {
                $table->text('asset_cancel_reason')->nullable()->after('asset_status');
            }
        });

        if (Schema::hasColumn('msu_grns', 'is_added_to_asset') && Schema::hasColumn('msu_grns', 'asset_status')) {
            DB::table('msu_grns')
                ->where('is_added_to_asset', true)
                ->update(['asset_status' => 'approved']);
        }

        $guard = 'web';
        if (Schema::hasTable('permissions') && class_exists(Permission::class)) {
            foreach (['msu-grn:approve', 'msu-grn:cancel'] as $permission) {
                Permission::firstOrCreate(['name' => $permission, 'guard_name' => $guard]);
            }

            foreach (['Super Admin', 'superadmin', 'Admin', 'admin'] as $roleName) {
                $role = Role::where('name', $roleName)->where('guard_name', $guard)->first();
                if ($role) {
                    $role->givePermissionTo(['msu-grn:approve', 'msu-grn:cancel']);
                }
            }

            app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
        }
    }

    public function down(): void
    {
        Schema::table('msu_grns', function (Blueprint $table) {
            if (Schema::hasColumn('msu_grns', 'asset_cancel_reason')) {
                $table->dropColumn('asset_cancel_reason');
            }

            if (Schema::hasColumn('msu_grns', 'asset_status')) {
                $table->dropColumn('asset_status');
            }
        });
    }
};
