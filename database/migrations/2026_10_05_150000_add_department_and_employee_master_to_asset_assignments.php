<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('asset_assignments', 'department_id')) {
            Schema::table('asset_assignments', function (Blueprint $table) {
                $table->foreignId('department_id')
                    ->nullable()
                    ->after('assiged_emoloyee')
                    ->constrained('departments')
                    ->nullOnDelete();
            });
        }

    }

    public function down(): void
    {
        Schema::table('asset_assignments', function (Blueprint $table) {
            if (Schema::hasColumn('asset_assignments', 'employee_master_id')) {
                $table->dropConstrainedForeignId('employee_master_id');
            }

            if (Schema::hasColumn('asset_assignments', 'department_id')) {
                $table->dropConstrainedForeignId('department_id');
            }
        });
    }
};
