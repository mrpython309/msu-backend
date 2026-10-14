<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('payment_terms') && Schema::hasColumn('payment_terms', 'payment_mode')) {
            DB::statement('ALTER TABLE payment_terms MODIFY payment_mode VARCHAR(500) NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('payment_terms') && Schema::hasColumn('payment_terms', 'payment_mode')) {
            DB::statement('ALTER TABLE payment_terms MODIFY payment_mode VARCHAR(100) NULL');
        }
    }
};
