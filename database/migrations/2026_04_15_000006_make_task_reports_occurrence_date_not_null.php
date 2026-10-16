<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('task_reports')) {
            return;
        }

        // Keep one NULL-dated row per recurring_task_id + branch_id, delete duplicates
        // so backfilling to today won't violate the existing unique constraint.
        DB::statement("
            DELETE t1 FROM task_reports t1
            INNER JOIN task_reports t2
                ON t1.recurring_task_id = t2.recurring_task_id
                AND t1.branch_id = t2.branch_id
                AND t1.occurrence_date IS NULL
                AND t2.occurrence_date IS NULL
                AND t1.id > t2.id
        ");

        DB::table('task_reports')
            ->whereNull('occurrence_date')
            ->update(['occurrence_date' => now()->toDateString()]);

        DB::statement('ALTER TABLE task_reports MODIFY occurrence_date DATE NOT NULL');
    }

    public function down(): void
    {
        if (! Schema::hasTable('task_reports')) {
            return;
        }

        DB::statement('ALTER TABLE task_reports MODIFY occurrence_date DATE NULL');
    }
};
