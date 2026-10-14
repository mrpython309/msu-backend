<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recurring_tasks', function (Blueprint $table) {
            if (Schema::hasColumn('recurring_tasks', 'task_code')) {
                $table->dropUnique(['task_code']);
                $table->dropColumn('task_code');
            }
        });
    }

    public function down(): void
    {
        Schema::table('recurring_tasks', function (Blueprint $table) {
            $table->string('task_code', 16)->nullable()->unique()->after('id');
        });
    }
};
