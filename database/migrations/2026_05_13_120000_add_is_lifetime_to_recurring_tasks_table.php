<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('recurring_tasks', 'is_lifetime')) {
            return;
        }
        Schema::table('recurring_tasks', function (Blueprint $table) {
            $table->boolean('is_lifetime')->default(false)->after('schedule_config');
        });
    }

    public function down(): void
    {
        Schema::table('recurring_tasks', function (Blueprint $table) {
            $table->dropColumn('is_lifetime');
        });
    }
};
