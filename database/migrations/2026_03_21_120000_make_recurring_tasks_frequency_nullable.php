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
            $table->string('frequency', 32)->nullable()->change();
        });
    }

    public function down(): void
    {
        DB::table('recurring_tasks')->whereNull('frequency')->update(['frequency' => 'daily']);

        Schema::table('recurring_tasks', function (Blueprint $table) {
            $table->string('frequency', 32)->default('daily')->nullable(false)->change();
        });
    }
};
