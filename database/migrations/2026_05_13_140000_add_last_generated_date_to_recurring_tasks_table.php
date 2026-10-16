<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recurring_tasks', function (Blueprint $table): void {
            if (! Schema::hasColumn('recurring_tasks', 'last_generated_date')) {
                $table->date('last_generated_date')->nullable()->after('is_lifetime');
            }
        });
    }

    public function down(): void
    {
        Schema::table('recurring_tasks', function (Blueprint $table): void {
            if (Schema::hasColumn('recurring_tasks', 'last_generated_date')) {
                $table->dropColumn('last_generated_date');
            }
        });
    }
};
