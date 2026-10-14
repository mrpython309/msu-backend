<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            if (! Schema::hasColumn('assets', 'msu_grn_item_id')) {
                $table->unsignedBigInteger('msu_grn_item_id')->nullable()->after('grn_item_id');
                $table->foreign('msu_grn_item_id')->references('id')->on('msu_grn_items')->nullOnDelete();
                $table->unique(['msu_grn_item_id', 'source_unit_index'], 'assets_msu_grn_item_unit_unique');
            }
        });

        Schema::table('msu_grns', function (Blueprint $table) {
            if (! Schema::hasColumn('msu_grns', 'is_added_to_asset')) {
                $table->boolean('is_added_to_asset')->default(false)->after('is_added_to_stock');
            }
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            if (Schema::hasColumn('assets', 'msu_grn_item_id')) {
                $table->dropUnique('assets_msu_grn_item_unit_unique');
                $table->dropForeign(['msu_grn_item_id']);
                $table->dropColumn('msu_grn_item_id');
            }
        });

        Schema::table('msu_grns', function (Blueprint $table) {
            if (Schema::hasColumn('msu_grns', 'is_added_to_asset')) {
                $table->dropColumn('is_added_to_asset');
            }
        });
    }
};
