<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('recurring_task_tickets')) {
            return;
        }

        if (! Schema::hasColumn('recurring_task_tickets', 'branch_id')) {
            Schema::table('recurring_task_tickets', function (Blueprint $table): void {
                $table->unsignedBigInteger('branch_id')->nullable()->after('recurring_task_id');
                $table->index(['recurring_task_id', 'occurrence_date', 'branch_id'], 'rt_tickets_task_date_branch_idx');
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('recurring_task_tickets') || ! Schema::hasColumn('recurring_task_tickets', 'branch_id')) {
            return;
        }

        Schema::table('recurring_task_tickets', function (Blueprint $table): void {
            $table->dropIndex('rt_tickets_task_date_branch_idx');
            $table->dropColumn('branch_id');
        });
    }
};
