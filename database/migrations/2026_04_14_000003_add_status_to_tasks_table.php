<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('tasks') && ! Schema::hasColumn('tasks', 'status')) {
            Schema::table('tasks', function (Blueprint $table): void {
                $table->string('status', 32)->default('pending')->after('branch_id');
            });
        }

        if (Schema::hasTable('tasks') && Schema::hasColumn('tasks', 'status')) {
            DB::table('tasks')
                ->whereNull('status')
                ->update(['status' => 'pending']);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('tasks') && Schema::hasColumn('tasks', 'status')) {
            Schema::table('tasks', function (Blueprint $table): void {
                $table->dropColumn('status');
            });
        }
    }
};
