<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $backfillCapex = ! Schema::hasColumn('msu_grn_items', 'is_capex');

        if ($backfillCapex) {
            Schema::table('msu_grn_items', function (Blueprint $table) {
                $table->boolean('is_capex')->default(false)->after('received_quantity');
            });
        }

        if (! Schema::hasColumn('msu_grn_items', 'is_group_asset')) {
            Schema::table('msu_grn_items', function (Blueprint $table) {
                $table->boolean('is_group_asset')->default(false)->after('is_capex');
            });
        }

        if ($backfillCapex && Schema::hasColumn('msu_grns', 'expense_type')) {
            DB::statement("
                UPDATE msu_grn_items i
                INNER JOIN msu_grns g ON g.id = i.msu_grn_id
                SET i.is_capex = 1,
                    i.is_group_asset = IF(i.received_quantity > 1, 1, 0)
                WHERE LOWER(TRIM(g.expense_type)) = 'capex'
            ");
        }
    }

    public function down(): void
    {
        Schema::table('msu_grn_items', function (Blueprint $table) {
            if (Schema::hasColumn('msu_grn_items', 'is_group_asset')) {
                $table->dropColumn('is_group_asset');
            }
            if (Schema::hasColumn('msu_grn_items', 'is_capex')) {
                $table->dropColumn('is_capex');
            }
        });
    }
};
