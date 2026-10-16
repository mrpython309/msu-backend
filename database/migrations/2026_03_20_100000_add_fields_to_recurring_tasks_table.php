<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recurring_tasks', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->after('task_category_id')->constrained('departments')->nullOnDelete();
            $table->string('priority', 32)->default('medium')->after('description');
            $table->string('task_type', 64)->default('general')->after('priority');
            $table->date('start_date')->nullable()->after('task_type');
            $table->date('end_date')->nullable()->after('start_date');
            $table->string('status', 32)->default('active')->after('end_date');
        });

        $today = now()->toDateString();
        $deptId = DB::table('departments')->where('status', 'active')->orderBy('id')->value('id');

        DB::table('recurring_tasks')->whereNull('start_date')->update(['start_date' => $today]);

        if ($deptId) {
            DB::table('recurring_tasks')->whereNull('department_id')->update(['department_id' => $deptId]);
        }

        DB::table('recurring_tasks')->where('is_active', false)->update(['status' => 'inactive']);
    }

    public function down(): void
    {
        Schema::table('recurring_tasks', function (Blueprint $table) {
            $table->dropConstrainedForeignId('department_id');
            $table->dropColumn(['priority', 'task_type', 'start_date', 'end_date', 'status']);
        });
    }
};
