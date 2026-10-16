<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recurring_tasks', function (Blueprint $table) {
            $table->string('assignment_type', 16)->default('employee')->after('branch_id');
        });

        Schema::table('recurring_tasks', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->change();
            $table->foreignId('assigned_to')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('recurring_tasks', function (Blueprint $table) {
            $table->dropColumn('assignment_type');
        });
    }
};
