<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recurring_tasks', function (Blueprint $table) {
            $table->foreignId('branch_id')->nullable()->after('department_id')->constrained('branches')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->after('branch_id')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('recurring_tasks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('assigned_to');
            $table->dropConstrainedForeignId('branch_id');
        });
    }
};
