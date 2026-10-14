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
            $table->unsignedInteger('task_no')->nullable()->after('id');
        });

        $rows = DB::table('recurring_tasks')
            ->orderBy('created_at')
            ->orderBy('id')
            ->pluck('id');

        $n = 1;
        foreach ($rows as $id) {
            DB::table('recurring_tasks')->where('id', $id)->update(['task_no' => $n]);
            $n++;
        }

        Schema::table('recurring_tasks', function (Blueprint $table) {
            $table->unsignedInteger('task_no')->nullable(false)->change();
            $table->unique('task_no');
        });
    }

    public function down(): void
    {
        Schema::table('recurring_tasks', function (Blueprint $table) {
            $table->dropUnique(['task_no']);
            $table->dropColumn('task_no');
        });
    }
};
