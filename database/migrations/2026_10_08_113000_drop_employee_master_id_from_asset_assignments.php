<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('asset_assignments', 'employee_master_id')) {
            return;
        }

        Schema::table('asset_assignments', function (Blueprint $table) {
            $table->dropConstrainedForeignId('employee_master_id');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('asset_assignments', 'employee_master_id')) {
            return;
        }

        Schema::table('asset_assignments', function (Blueprint $table) {
            $table->foreignId('employee_master_id')
                ->nullable()
                ->after('department_id')
                ->constrained('employee_masters')
                ->nullOnDelete();
        });
    }
};
