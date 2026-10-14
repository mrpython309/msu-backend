<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('task_reports')) {
            return;
        }

        if (! $this->indexExists('task_reports', 'task_reports_task_branch_occ_unique')) {
            Schema::table('task_reports', function (Blueprint $table): void {
                $table->unique(
                    ['recurring_task_id', 'branch_id', 'occurrence_date'],
                    'task_reports_task_branch_occ_unique'
                );
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('task_reports')) {
            return;
        }

        if ($this->indexExists('task_reports', 'task_reports_task_branch_occ_unique')) {
            Schema::table('task_reports', function (Blueprint $table): void {
                $table->dropUnique('task_reports_task_branch_occ_unique');
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
