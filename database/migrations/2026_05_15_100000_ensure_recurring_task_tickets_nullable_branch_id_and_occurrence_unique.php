<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Occurrence-scoped tickets use branch_id = NULL. Earlier migrations forced NOT NULL;
 * this migration aligns the schema without relying on doctrine/dbal for ->change().
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('recurring_task_tickets') || ! Schema::hasColumn('recurring_task_tickets', 'branch_id')) {
            return;
        }

        if ($this->branchIdColumnIsNullable()) {
            $this->ensureOccurrenceUniqueIndex();

            return;
        }

        if ($this->indexExists('recurring_task_tickets', 'rt_tickets_task_date_branch_unique')) {
            Schema::table('recurring_task_tickets', function (Blueprint $table): void {
                $table->dropUnique('rt_tickets_task_date_branch_unique');
            });
        }

        $this->deleteDuplicateOccurrenceTicketRows();

        DB::table('recurring_task_tickets')->whereNotNull('branch_id')->update([
            'branch_id' => null,
            'updated_at' => now(),
        ]);

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            $tableName = DB::getTablePrefix().'recurring_task_tickets';
            DB::statement('ALTER TABLE `'.$tableName.'` MODIFY branch_id BIGINT UNSIGNED NULL');
        } else {
            Schema::table('recurring_task_tickets', function (Blueprint $table): void {
                $table->unsignedBigInteger('branch_id')->nullable()->change();
            });
        }

        $this->ensureOccurrenceUniqueIndex();
    }

    public function down(): void
    {
        if (! Schema::hasTable('recurring_task_tickets') || ! Schema::hasColumn('recurring_task_tickets', 'branch_id')) {
            return;
        }

        DB::table('recurring_task_tickets')->whereNull('branch_id')->delete();

        if ($this->indexExists('recurring_task_tickets', 'rt_tickets_task_occurrence_unique')) {
            Schema::table('recurring_task_tickets', function (Blueprint $table): void {
                $table->dropUnique('rt_tickets_task_occurrence_unique');
            });
        }

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            $tableName = DB::getTablePrefix().'recurring_task_tickets';
            DB::statement('ALTER TABLE `'.$tableName.'` MODIFY branch_id BIGINT UNSIGNED NOT NULL');
        } else {
            Schema::table('recurring_task_tickets', function (Blueprint $table): void {
                $table->unsignedBigInteger('branch_id')->nullable(false)->change();
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

    private function branchIdColumnIsNullable(): bool
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            $row = DB::selectOne(
                'SELECT IS_NULLABLE FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE()
                   AND TABLE_NAME = ?
                   AND COLUMN_NAME = ?',
                ['recurring_task_tickets', 'branch_id']
            );

            return $row !== null && strtoupper((string) ($row->IS_NULLABLE ?? '')) === 'YES';
        }
        if ($driver === 'sqlite') {
            foreach (DB::select('PRAGMA table_info('.DB::getTablePrefix().'recurring_task_tickets)') as $col) {
                if (($col->name ?? '') === 'branch_id') {
                    return (int) ($col->notnull ?? 1) === 0;
                }
            }
        }

        return false;
    }

    private function ensureOccurrenceUniqueIndex(): void
    {
        if ($this->indexExists('recurring_task_tickets', 'rt_tickets_task_occurrence_unique')) {
            return;
        }
        Schema::table('recurring_task_tickets', function (Blueprint $table): void {
            $table->unique(
                ['recurring_task_id', 'occurrence_date'],
                'rt_tickets_task_occurrence_unique'
            );
        });
    }

    /**
     * Remove extra rows for the same (recurring_task_id, occurrence_date), keeping the lowest id.
     */
    private function deleteDuplicateOccurrenceTicketRows(): void
    {
        $dupIds = DB::table('recurring_task_tickets as t1')
            ->join('recurring_task_tickets as t2', function ($join): void {
                $join->on('t1.recurring_task_id', '=', 't2.recurring_task_id')
                    ->whereColumn('t1.occurrence_date', 't2.occurrence_date')
                    ->whereColumn('t1.id', '>', 't2.id');
            })
            ->pluck('t1.id');
        if ($dupIds->isEmpty()) {
            return;
        }
        foreach ($dupIds->chunk(200) as $chunk) {
            $ids = array_values(array_filter(array_map('intval', $chunk->all()), static fn (int $id): bool => $id > 0));
            if ($ids === []) {
                continue;
            }
            if (Schema::hasTable('recurring_task_ticket_branches')) {
                DB::table('recurring_task_ticket_branches')->whereIn('recurring_task_ticket_id', $ids)->delete();
            }
            DB::table('recurring_task_tickets')->whereIn('id', $ids)->delete();
        }
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'sqlite') {
            try {
                $rows = Schema::getConnection()->select('PRAGMA index_list('.DB::getTablePrefix().$table.')');
            } catch (\Throwable) {
                $rows = Schema::getConnection()->select('PRAGMA index_list('.$table.')');
            }
            foreach ($rows as $ix) {
                $name = is_object($ix) ? ($ix->name ?? '') : ($ix['name'] ?? '');
                if ($name === $indexName) {
                    return true;
                }
            }

            return false;
        }

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
