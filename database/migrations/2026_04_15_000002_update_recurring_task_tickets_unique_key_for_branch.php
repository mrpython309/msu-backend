<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('recurring_task_tickets')) {
            return;
        }

        // Keep FK support index on recurring_task_id before dropping old unique index.
        if (! $this->indexExists('recurring_task_tickets', 'rt_tickets_task_id_idx')) {
            Schema::table('recurring_task_tickets', function (Blueprint $table): void {
                $table->index('recurring_task_id', 'rt_tickets_task_id_idx');
            });
        }

        if ($this->indexExists('recurring_task_tickets', 'recurring_task_tickets_recurring_task_id_occurrence_date_unique')) {
            Schema::table('recurring_task_tickets', function (Blueprint $table): void {
                // Legacy unique key blocked multi-branch rows for same task/date.
                $table->dropUnique('recurring_task_tickets_recurring_task_id_occurrence_date_unique');
            });
        }

        if (! $this->indexExists('recurring_task_tickets', 'rt_tickets_task_date_branch_unique')) {
            Schema::table('recurring_task_tickets', function (Blueprint $table): void {
                $table->unique(
                    ['recurring_task_id', 'occurrence_date', 'branch_id'],
                    'rt_tickets_task_date_branch_unique'
                );
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('recurring_task_tickets')) {
            return;
        }

        if ($this->indexExists('recurring_task_tickets', 'rt_tickets_task_date_branch_unique')) {
            Schema::table('recurring_task_tickets', function (Blueprint $table): void {
                $table->dropUnique('rt_tickets_task_date_branch_unique');
            });
        }

        if (! $this->indexExists('recurring_task_tickets', 'recurring_task_tickets_recurring_task_id_occurrence_date_unique')) {
            Schema::table('recurring_task_tickets', function (Blueprint $table): void {
                $table->unique(
                    ['recurring_task_id', 'occurrence_date'],
                    'recurring_task_tickets_recurring_task_id_occurrence_date_unique'
                );
            });
        }

        // Clean up helper index after restoring original unique key.
        if ($this->indexExists('recurring_task_tickets', 'rt_tickets_task_id_idx')) {
            Schema::table('recurring_task_tickets', function (Blueprint $table): void {
                $table->dropIndex('rt_tickets_task_id_idx');
            });
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $row = DB::selectOne(
            'SELECT 1
             FROM information_schema.statistics
             WHERE table_schema = DATABASE()
               AND table_name = ?
               AND index_name = ?
             LIMIT 1',
            [$table, $indexName]
        );

        return $row !== null;
    }
};
