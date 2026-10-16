<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * GRN line net values are decimal(15,2). assets.purchase_cost was decimal(10,3),
     * so any invoice of 10,000,000 or more failed while approving a Capex GRN.
     */
    public function up(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        if (! Schema::hasColumn('assets', 'purchase_cost')) {
            return;
        }

        DB::statement('ALTER TABLE assets MODIFY purchase_cost DECIMAL(18,3) NULL');
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        if (! Schema::hasColumn('assets', 'purchase_cost')) {
            return;
        }

        DB::statement('ALTER TABLE assets MODIFY purchase_cost DECIMAL(10,3) NULL');
    }
};
