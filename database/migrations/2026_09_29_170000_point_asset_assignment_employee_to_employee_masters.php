<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('asset_assignments', 'assiged_emoloyee')) {
            return;
        }

        $this->dropUsersForeignKey();

        DB::table('asset_assignments')
            ->whereNotNull('assiged_emoloyee')
            ->whereNotIn('assiged_emoloyee', function ($query) {
                $query->select('id')->from('employee_masters');
            })
            ->update(['assiged_emoloyee' => null]);

        if (! $this->foreignKeyExists('asset_assignments', 'asset_assignments_assiged_emoloyee_foreign')) {
            Schema::table('asset_assignments', function (Blueprint $table) {
                $table->foreign('assiged_emoloyee', 'asset_assignments_assiged_emoloyee_foreign')
                    ->references('id')
                    ->on('employee_masters')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('asset_assignments', 'assiged_emoloyee')) {
            return;
        }

        $this->dropForeignIfExists('asset_assignments', 'asset_assignments_assiged_emoloyee_foreign');

        DB::table('asset_assignments')->whereNotNull('assiged_emoloyee')->update(['assiged_emoloyee' => null]);

        Schema::table('asset_assignments', function (Blueprint $table) {
            $table->foreign('assiged_emoloyee')->references('id')->on('users')->nullOnDelete();
        });
    }

    private function dropUsersForeignKey(): void
    {
        $db = DB::getDatabaseName();
        $row = DB::selectOne(
            'SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE
             WHERE CONSTRAINT_SCHEMA = ? AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME = ?',
            [$db, 'asset_assignments', 'assiged_emoloyee', 'users']
        );

        if ($row && ! empty($row->CONSTRAINT_NAME)) {
            Schema::table('asset_assignments', function (Blueprint $blueprint) use ($row) {
                $blueprint->dropForeign($row->CONSTRAINT_NAME);
            });
        }
    }

    private function dropForeignIfExists(string $table, string $foreign): void
    {
        if ($this->foreignKeyExists($table, $foreign)) {
            Schema::table($table, function (Blueprint $blueprint) use ($foreign) {
                $blueprint->dropForeign($foreign);
            });
        }
    }

    private function foreignKeyExists(string $table, string $foreign): bool
    {
        $db = DB::getDatabaseName();
        $row = DB::selectOne(
            'SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
             WHERE CONSTRAINT_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = ?',
            [$db, $table, $foreign, 'FOREIGN KEY']
        );

        return (bool) $row;
    }
};
