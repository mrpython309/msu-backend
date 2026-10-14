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
        if (! Schema::hasTable('asset_custodians')) {
            Schema::create('asset_custodians', function (Blueprint $table) {
                $table->id();
                $table->string('employee_name');
                $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
                $table->string('mobile_number', 20)->nullable();
                $table->string('email')->nullable();
                $table->enum('status', ['active', 'inactive'])->default('active');
                $table->timestamps();
            });
        }

        $this->copyEmployeeMasters();
        $this->pointColumn('assets', 'custodian_id', 'assets_custodian_id_foreign');
        $this->pointColumn('asset_assignments', 'assiged_emoloyee', 'asset_assignments_assiged_emoloyee_foreign');
        $this->grantPermissions();
    }

    public function down(): void
    {
        $this->pointColumnBack('assets', 'custodian_id', 'assets_custodian_id_foreign');
        $this->pointColumnBack('asset_assignments', 'assiged_emoloyee', 'asset_assignments_assiged_emoloyee_foreign');
        Schema::dropIfExists('asset_custodians');
    }

    private function copyEmployeeMasters(): void
    {
        if (! Schema::hasTable('employee_masters') || ! Schema::hasTable('asset_custodians')) {
            return;
        }

        $existingIds = DB::table('asset_custodians')->pluck('id')->map(fn ($id) => (int) $id)->all();

        DB::table('employee_masters')
            ->orderBy('id')
            ->get()
            ->each(function ($row) use ($existingIds) {
                if (in_array((int) $row->id, $existingIds, true)) {
                    return;
                }

                DB::table('asset_custodians')->insert([
                    'id' => $row->id,
                    'employee_name' => $row->employee_name,
                    'department_id' => $row->department_id,
                    'mobile_number' => $row->mobile_number,
                    'email' => $row->email,
                    'status' => $row->status ?: 'active',
                    'created_at' => $row->created_at,
                    'updated_at' => $row->updated_at,
                ]);
            });
    }

    private function pointColumn(string $table, string $column, string $foreign): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column)) {
            return;
        }

        $this->dropForeignsOnColumn($table, $column);

        DB::table($table)
            ->whereNotNull($column)
            ->whereNotIn($column, function ($query) {
                $query->select('id')->from('asset_custodians');
            })
            ->update([$column => null]);

        if (! $this->foreignReferences($table, $column, 'asset_custodians')) {
            Schema::table($table, function (Blueprint $blueprint) use ($column, $foreign) {
                $blueprint->foreign($column, $foreign)
                    ->references('id')
                    ->on('asset_custodians')
                    ->nullOnDelete();
            });
        }
    }

    private function pointColumnBack(string $table, string $column, string $foreign): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, $column) || ! Schema::hasTable('employee_masters')) {
            return;
        }

        $this->dropForeignsOnColumn($table, $column);

        DB::table($table)
            ->whereNotNull($column)
            ->whereNotIn($column, function ($query) {
                $query->select('id')->from('employee_masters');
            })
            ->update([$column => null]);

        Schema::table($table, function (Blueprint $blueprint) use ($column, $foreign) {
            $blueprint->foreign($column, $foreign)
                ->references('id')
                ->on('employee_masters')
                ->nullOnDelete();
        });
    }

    private function dropForeignsOnColumn(string $table, string $column): void
    {
        $db = DB::getDatabaseName();
        $rows = DB::select(
            'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
             WHERE CONSTRAINT_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$db, $table, $column]
        );

        foreach ($rows as $row) {
            Schema::table($table, function (Blueprint $blueprint) use ($row) {
                $blueprint->dropForeign($row->CONSTRAINT_NAME);
            });
        }
    }

    private function foreignReferences(string $table, string $column, string $referencedTable): bool
    {
        $db = DB::getDatabaseName();
        $row = DB::selectOne(
            'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
             WHERE CONSTRAINT_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME = ?',
            [$db, $table, $column, $referencedTable]
        );

        return (bool) $row;
    }

    private function grantPermissions(): void
    {
        if (! Schema::hasTable('permissions') || ! class_exists(Permission::class)) {
            return;
        }

        $guard = 'web';
        $permissions = [
            'asset-custodian:view',
            'asset-custodian:create',
            'asset-custodian:edit',
            'asset-custodian:delete',
            'asset-custodian:status',
        ];

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
};
