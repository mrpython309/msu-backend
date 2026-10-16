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
            $table->string('task_code', 16)->nullable()->unique()->after('id');
        });

        foreach (DB::table('recurring_tasks')->select('id', 'task_code')->cursor() as $row) {
            if (filled($row->task_code)) {
                continue;
            }
            do {
                $code = strtoupper(bin2hex(random_bytes(4)));
            } while (DB::table('recurring_tasks')->where('task_code', $code)->exists());

            DB::table('recurring_tasks')->where('id', $row->id)->update(['task_code' => $code]);
        }
    }

    public function down(): void
    {
        Schema::table('recurring_tasks', function (Blueprint $table) {
            $table->dropUnique(['task_code']);
            $table->dropColumn('task_code');
        });
    }
};
