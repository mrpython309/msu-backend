<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * MsuItemMaster allows names up to 700 chars, but PO/GRN line snapshots
     * used VARCHAR(255). Creating a PO with a long item_name fails with
     * SQLSTATE[22001] / 1406 Data too long for column 'item_name'.
     *
     * Matches live hotfix: item_name VARCHAR(700).
     */
    public function up(): void
    {
        if (Schema::hasTable('msu_purchase_order_items') && Schema::hasColumn('msu_purchase_order_items', 'item_name')) {
            DB::statement('ALTER TABLE msu_purchase_order_items MODIFY item_name VARCHAR(700) NULL');
        }

        if (Schema::hasTable('msu_grn_items') && Schema::hasColumn('msu_grn_items', 'item_name')) {
            DB::statement('ALTER TABLE msu_grn_items MODIFY item_name VARCHAR(700) NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('msu_grn_items') && Schema::hasColumn('msu_grn_items', 'item_name')) {
            DB::statement('ALTER TABLE msu_grn_items MODIFY item_name VARCHAR(255) NULL');
        }

        if (Schema::hasTable('msu_purchase_order_items') && Schema::hasColumn('msu_purchase_order_items', 'item_name')) {
            DB::statement('ALTER TABLE msu_purchase_order_items MODIFY item_name VARCHAR(255) NULL');
        }
    }
};
