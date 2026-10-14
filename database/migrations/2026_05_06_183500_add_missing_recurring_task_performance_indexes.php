<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // recurring_task_ticket_branches
        if (Schema::hasTable('recurring_task_ticket_branches')) {
            Schema::table('recurring_task_ticket_branches', function (Blueprint $table): void {
                if (! $this->indexExists('recurring_task_ticket_branches', 'rttb_branch_completed_at_idx')) {
                    $table->index(['branch_id', 'completed_at'], 'rttb_branch_completed_at_idx');
                }
                if (! $this->indexExists('recurring_task_ticket_branches', 'rttb_created_at_idx')) {
                    $table->index('created_at', 'rttb_created_at_idx');
                }
                if (! $this->indexExists('recurring_task_ticket_branches', 'rttb_updated_at_idx')) {
                    $table->index('updated_at', 'rttb_updated_at_idx');
                }
            });
        }

        // recurring_task_tickets
        if (Schema::hasTable('recurring_task_tickets')) {
            Schema::table('recurring_task_tickets', function (Blueprint $table): void {
                if (! $this->indexExists('recurring_task_tickets', 'rtt_occurrence_date_idx')) {
                    $table->index('occurrence_date', 'rtt_occurrence_date_idx');
                }
            });
        }

        // task_reports
        if (Schema::hasTable('task_reports')) {
            Schema::table('task_reports', function (Blueprint $table): void {
                if (! $this->indexExists('task_reports', 'tr_task_occurrence_idx')) {
                    $table->index(['recurring_task_id', 'occurrence_date'], 'tr_task_occurrence_idx');
                }
                if (! $this->indexExists('task_reports', 'tr_status_occurrence_idx')) {
                    $table->index(['status', 'occurrence_date'], 'tr_status_occurrence_idx');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('recurring_task_ticket_branches')) {
            Schema::table('recurring_task_ticket_branches', function (Blueprint $table): void {
                if ($this->indexExists('recurring_task_ticket_branches', 'rttb_branch_completed_at_idx')) {
                    $table->dropIndex('rttb_branch_completed_at_idx');
                }
                if ($this->indexExists('recurring_task_ticket_branches', 'rttb_created_at_idx')) {
                    $table->dropIndex('rttb_created_at_idx');
                }
                if ($this->indexExists('recurring_task_ticket_branches', 'rttb_updated_at_idx')) {
                    $table->dropIndex('rttb_updated_at_idx');
                }
            });
        }

        if (Schema::hasTable('recurring_task_tickets')) {
            Schema::table('recurring_task_tickets', function (Blueprint $table): void {
                if ($this->indexExists('recurring_task_tickets', 'rtt_occurrence_date_idx')) {
                    $table->dropIndex('rtt_occurrence_date_idx');
                }
            });
        }

        if (Schema::hasTable('task_reports')) {
            Schema::table('task_reports', function (Blueprint $table): void {
                if ($this->indexExists('task_reports', 'tr_task_occurrence_idx')) {
                    $table->dropIndex('tr_task_occurrence_idx');
                }
                if ($this->indexExists('task_reports', 'tr_status_occurrence_idx')) {
                    $table->dropIndex('tr_status_occurrence_idx');
                }
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

