<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // phpMyAdmin equivalent:
        // ALTER TABLE msu_grns ADD COLUMN branch_id BIGINT UNSIGNED NULL AFTER msu_purchase_order_id;
        // ALTER TABLE msu_grns ADD CONSTRAINT msu_grns_branch_id_foreign FOREIGN KEY (branch_id) REFERENCES branches(id) ON DELETE SET NULL;
        // UPDATE msu_grns g INNER JOIN msu_purchase_orders po ON po.id = g.msu_purchase_order_id SET g.branch_id = po.branch_id WHERE g.branch_id IS NULL;
        if (! Schema::hasTable('msu_grns') || Schema::hasColumn('msu_grns', 'branch_id')) {
            $this->backfillFromPurchaseOrders();

            return;
        }

        Schema::table('msu_grns', function (Blueprint $table) {
            $table->foreignId('branch_id')
                ->nullable()
                ->after('msu_purchase_order_id')
                ->constrained('branches')
                ->nullOnDelete();
        });

        $this->backfillFromPurchaseOrders();
    }

    public function down(): void
    {
        if (! Schema::hasTable('msu_grns') || ! Schema::hasColumn('msu_grns', 'branch_id')) {
            return;
        }

        Schema::table('msu_grns', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });
    }

    private function backfillFromPurchaseOrders(): void
    {
        if (! Schema::hasTable('msu_grns')
            || ! Schema::hasColumn('msu_grns', 'branch_id')
            || ! Schema::hasTable('msu_purchase_orders')
            || ! Schema::hasColumn('msu_purchase_orders', 'branch_id')) {
            return;
        }

        DB::statement('
            UPDATE msu_grns g
            INNER JOIN msu_purchase_orders po ON po.id = g.msu_purchase_order_id
            SET g.branch_id = po.branch_id
            WHERE g.branch_id IS NULL
        ');
    }
};
