<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('recurring_tasks', 'assignment_type')) {
            return;
        }

        DB::table('recurring_tasks')
            ->where('assignment_type', 'none')
            ->update(['assignment_type' => 'branch']);
    }

    public function down(): void
    {
        // not reversible
    }
};
