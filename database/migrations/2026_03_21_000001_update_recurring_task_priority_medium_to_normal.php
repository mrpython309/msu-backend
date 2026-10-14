<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('recurring_tasks')->where('priority', 'medium')->update(['priority' => 'normal']);
    }

    public function down(): void
    {
        DB::table('recurring_tasks')->where('priority', 'normal')->update(['priority' => 'medium']);
    }
};
