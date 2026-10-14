<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('recurring_tasks', function (Blueprint $table) {
            if (! Schema::hasColumn('recurring_tasks', 'completed_count')) {
                $table->integer('completed_count')->default(0);
            }
            if (! Schema::hasColumn('recurring_tasks', 'expected_count_until_now')) {
                $table->integer('expected_count_until_now')->default(0);
            }
            if (! Schema::hasColumn('recurring_tasks', 'active_occurrence_date')) {
                $table->date('active_occurrence_date')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recurring_tasks', function (Blueprint $table) {
            $drop = [];
            if (Schema::hasColumn('recurring_tasks', 'completed_count')) {
                $drop[] = 'completed_count';
            }
            if (Schema::hasColumn('recurring_tasks', 'expected_count_until_now')) {
                $drop[] = 'expected_count_until_now';
            }
            if (Schema::hasColumn('recurring_tasks', 'active_occurrence_date')) {
                $drop[] = 'active_occurrence_date';
            }
            if ($drop !== []) {
                $table->dropColumn($drop);
            }
        });
    }
};
