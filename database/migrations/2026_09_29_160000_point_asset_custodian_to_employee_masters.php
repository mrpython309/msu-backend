<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('assets', 'custodian_id')) {
            return;
        }

        $this->dropForeignIfExists('assets', 'assets_custodian_id_foreign');

        // Existing values pointed at users, not employee master records.
        DB::table('assets')->whereNotNull('custodian_id')->update(['custodian_id' => null]);

        Schema::table('assets', function (Blueprint $table) {
            if (! $this->foreignKeyExists('assets', 'assets_custodian_id_foreign')) {
                $table->foreign('custodian_id')->references('id')->on('employee_masters')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasColumn('assets', 'custodian_id')) {
            return;
        }

        $this->dropForeignIfExists('assets', 'assets_custodian_id_foreign');

        DB::table('assets')->whereNotNull('custodian_id')->update(['custodian_id' => null]);

        Schema::table('assets', function (Blueprint $table) {
            $table->foreign('custodian_id')->references('id')->on('users')->nullOnDelete();
        });
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
