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
            $table->timestamp('ticket_completed_at')->nullable();
        });

        if (Schema::hasColumn('recurring_tasks', 'ticket_completed_at')) {
            DB::table('recurring_tasks')
                ->where('ticket_status', 'completed')
                ->whereNull('ticket_completed_at')
                ->update(['ticket_completed_at' => DB::raw('updated_at')]);
        }
    }

    public function down(): void
    {
        Schema::table('recurring_tasks', function (Blueprint $table) {
            $table->dropColumn('ticket_completed_at');
        });
    }
};
