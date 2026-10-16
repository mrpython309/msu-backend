<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('recurring_task_tickets')) {
            return;
        }

        DB::table('recurring_task_tickets')
            ->whereNull('branch_id')
            ->delete();

        DB::statement('ALTER TABLE recurring_task_tickets MODIFY branch_id BIGINT UNSIGNED NOT NULL');
    }

    public function down(): void
    {
        if (! Schema::hasTable('recurring_task_tickets')) {
            return;
        }

        DB::statement('ALTER TABLE recurring_task_tickets MODIFY branch_id BIGINT UNSIGNED NULL');
    }
};
