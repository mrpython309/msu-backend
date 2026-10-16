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
            if (! Schema::hasColumn('recurring_tasks', 'recurring_type')) {
                $table->string('recurring_type', 20)->nullable()->after('frequency');
            }
            if (! Schema::hasColumn('recurring_tasks', 'weekdays')) {
                $table->json('weekdays')->nullable()->after('recurring_type');
            }
            if (! Schema::hasColumn('recurring_tasks', 'months')) {
                $table->json('months')->nullable()->after('weekdays');
            }
        });

        DB::table('recurring_tasks')
            ->whereNull('recurring_type')
            ->whereIn('frequency', ['daily', 'weekly', 'monthly'])
            ->update(['recurring_type' => DB::raw('frequency')]);
    }

    public function down(): void
    {
        Schema::table('recurring_tasks', function (Blueprint $table) {
            if (Schema::hasColumn('recurring_tasks', 'months')) {
                $table->dropColumn('months');
            }
            if (Schema::hasColumn('recurring_tasks', 'weekdays')) {
                $table->dropColumn('weekdays');
            }
            if (Schema::hasColumn('recurring_tasks', 'recurring_type')) {
                $table->dropColumn('recurring_type');
            }
        });
    }
};

