<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('msu_work_orders', function (Blueprint $table) {
            $table->string('frequency')->nullable()->after('contract_period_to');
            $table->foreignId('recurrence_source_id')
                ->nullable()
                ->after('frequency')
                ->constrained('msu_work_orders')
                ->nullOnDelete();
            $table->date('next_occurrence_date')->nullable()->after('recurrence_source_id');
            $table->index('next_occurrence_date');
        });
    }

    public function down(): void
    {
        Schema::table('msu_work_orders', function (Blueprint $table) {
            $table->dropIndex(['next_occurrence_date']);
            $table->dropConstrainedForeignId('recurrence_source_id');
            $table->dropColumn(['frequency', 'next_occurrence_date']);
        });
    }
};
