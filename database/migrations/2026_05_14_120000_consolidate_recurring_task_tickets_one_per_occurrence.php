<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * One recurring_task_tickets row per (recurring_task_id, occurrence_date); branch_id NULL = occurrence scope.
 * Merges legacy per-branch ticket rows and replaces the unique key with (recurring_task_id, occurrence_date).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('recurring_task_tickets')) {
            return;
        }

        if ($this->indexExists('recurring_task_tickets', 'rt_tickets_task_date_branch_unique')) {
            Schema::table('recurring_task_tickets', function (Blueprint $table): void {
                $table->dropUnique('rt_tickets_task_date_branch_unique');
            });
        }

        $this->consolidateDuplicateTickets();

        if (Schema::hasColumn('recurring_task_tickets', 'branch_id')) {
            $driver = Schema::getConnection()->getDriverName();
            if ($driver === 'mysql') {
                $tableName = DB::getTablePrefix().'recurring_task_tickets';
                DB::statement('ALTER TABLE `'.$tableName.'` MODIFY branch_id BIGINT UNSIGNED NULL');
            } else {
                Schema::table('recurring_task_tickets', function (Blueprint $table): void {
                    $table->unsignedBigInteger('branch_id')->nullable()->change();
                });
            }
        }

        if (! $this->indexExists('recurring_task_tickets', 'rt_tickets_task_occurrence_unique')) {
            Schema::table('recurring_task_tickets', function (Blueprint $table): void {
                $table->unique(
                    ['recurring_task_id', 'occurrence_date'],
                    'rt_tickets_task_occurrence_unique'
                );
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('recurring_task_tickets')) {
            return;
        }

        if ($this->indexExists('recurring_task_tickets', 'rt_tickets_task_occurrence_unique')) {
            Schema::table('recurring_task_tickets', function (Blueprint $table): void {
                $table->dropUnique('rt_tickets_task_occurrence_unique');
            });
        }

        if (Schema::hasColumn('recurring_task_tickets', 'branch_id')) {
            $driver = Schema::getConnection()->getDriverName();
            if ($driver === 'mysql') {
                $tableName = DB::getTablePrefix().'recurring_task_tickets';
                DB::statement('ALTER TABLE `'.$tableName.'` MODIFY branch_id BIGINT UNSIGNED NOT NULL');
            } else {
                Schema::table('recurring_task_tickets', function (Blueprint $table): void {
                    $table->unsignedBigInteger('branch_id')->nullable(false)->change();
                });
            }
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

    private function consolidateDuplicateTickets(): void
    {
        $groups = DB::table('recurring_task_tickets')
            ->select('recurring_task_id', 'occurrence_date')
            ->groupBy('recurring_task_id', 'occurrence_date')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($groups as $g) {
            $taskId = (int) $g->recurring_task_id;
            $occ = (string) $g->occurrence_date;
            $rows = DB::table('recurring_task_tickets')
                ->where('recurring_task_id', $taskId)
                ->whereDate('occurrence_date', $occ)
                ->orderBy('id')
                ->get();
            if ($rows->count() <= 1) {
                continue;
            }

            $survivor = $rows->first();
            $survivorId = (int) $survivor->id;
            $mergedCompletions = [];
            $mergedCompleted = [];
            $ticketStatusRank = ['pending' => 1, 'overdue' => 2, 'completed' => 3];
            $bestRank = 0;
            $bestStatus = strtolower((string) ($survivor->status ?? 'pending'));

            foreach ($rows as $row) {
                $raw = $row->branch_completions ?? null;
                $decoded = is_array($raw) ? $raw : json_decode((string) $raw, true);
                if (is_array($decoded)) {
                    foreach ($decoded as $key => $block) {
                        if (! is_array($block)) {
                            continue;
                        }
                        $k = (string) $key;
                        if (! isset($mergedCompletions[$k])) {
                            $mergedCompletions[$k] = $block;
                        } else {
                            $mergedCompletions[$k] = $this->preferBranchCompletionBlock($mergedCompletions[$k], $block);
                        }
                    }
                }
                $craw = $row->completed_branch_ids ?? null;
                $cids = is_array($craw) ? $craw : json_decode((string) $craw, true);
                if (is_array($cids)) {
                    foreach ($cids as $cid) {
                        $cid = (int) $cid;
                        if ($cid > 0) {
                            $mergedCompleted[$cid] = true;
                        }
                    }
                }
                $st = strtolower((string) ($row->status ?? 'pending'));
                $r = $ticketStatusRank[$st] ?? 0;
                if ($r > $bestRank) {
                    $bestRank = $r;
                    $bestStatus = $st;
                }
            }

            $mergedCompletedIds = array_keys($mergedCompleted);
            sort($mergedCompletedIds);

            foreach ($rows as $row) {
                $rid = (int) $row->id;
                if ($rid === $survivorId) {
                    continue;
                }
                if (Schema::hasTable('recurring_task_ticket_branches')) {
                    $branchRows = DB::table('recurring_task_ticket_branches')
                        ->where('recurring_task_ticket_id', $rid)
                        ->get();
                    foreach ($branchRows as $br) {
                        $now = now();
                        DB::table('recurring_task_ticket_branches')->insertOrIgnore([
                            [
                                'recurring_task_ticket_id' => $survivorId,
                                'branch_id' => (int) $br->branch_id,
                                'status' => (string) ($br->status ?? 'pending'),
                                'created_at' => $br->created_at ?? $now,
                                'updated_at' => $br->updated_at ?? $now,
                            ],
                        ]);
                    }
                    DB::table('recurring_task_ticket_branches')
                        ->where('recurring_task_ticket_id', $rid)
                        ->delete();
                }
                DB::table('recurring_task_tickets')->where('id', $rid)->delete();
            }

            $finalStatus = $bestStatus === 'completed' ? 'completed' : ($bestStatus === 'overdue' ? 'overdue' : (string) ($survivor->status ?? 'pending'));

            DB::table('recurring_task_tickets')->where('id', $survivorId)->update([
                'branch_id' => null,
                'branch_completions' => $mergedCompletions !== [] ? json_encode($mergedCompletions) : null,
                'completed_branch_ids' => $mergedCompletedIds !== [] ? json_encode($mergedCompletedIds) : null,
                'status' => $finalStatus,
                'updated_at' => now(),
            ]);
        }

        DB::table('recurring_task_tickets')->whereNotNull('branch_id')->update([
            'branch_id' => null,
            'updated_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $a
     * @param  array<string, mixed>  $b
     * @return array<string, mixed>
     */
    private function preferBranchCompletionBlock(array $a, array $b): array
    {
        $order = [
            'pending' => 1,
            'accepted' => 2,
            'in_progress' => 3,
            'hold' => 4,
            'on_hold' => 4,
            'overdue' => 5,
            'completed' => 6,
        ];
        $sa = strtolower((string) ($a['status'] ?? 'pending'));
        $sb = strtolower((string) ($b['status'] ?? 'pending'));

        return ($order[$sb] ?? 0) >= ($order[$sa] ?? 0) ? array_merge($a, $b) : array_merge($b, $a);
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
