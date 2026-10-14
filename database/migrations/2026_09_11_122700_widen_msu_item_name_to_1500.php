<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Item Master names now allow 1500 chars. PO/GRN line snapshots must
     * match, otherwise creating a PO/GRN with a long name fails with
     * SQLSTATE[22001] / 1406 Data too long for column 'item_name'.
     *
     * Unique indexes that include item_name are dropped first because a
     * VARCHAR(1500) utf8mb4 key exceeds InnoDB's 3072-byte limit.
     * Uniqueness remains enforced in MsuItemMasterRequest.
     */
    public function up(): void
    {
        // phpMyAdmin equivalent (run in this order):
        // SHOW INDEX FROM msu_item_masters WHERE Column_name = 'item_name';
        // ALTER TABLE msu_item_masters DROP INDEX msu_item_cat_sub_name_unique;
        // ALTER TABLE msu_item_masters CHANGE item_name item_name VARCHAR(1500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL;
        // ALTER TABLE msu_purchase_order_items MODIFY item_name VARCHAR(1500) NULL;
        // ALTER TABLE msu_grn_items MODIFY item_name VARCHAR(1500) NULL;
        $this->dropNamedIndexIfExists('msu_item_masters', 'msu_item_cat_sub_name_unique');
        $this->dropItemNameUniqueIndexes('msu_item_masters');

        if (Schema::hasTable('msu_item_masters') && Schema::hasColumn('msu_item_masters', 'item_name')) {
            DB::statement('ALTER TABLE msu_item_masters MODIFY item_name VARCHAR(1500) NOT NULL');
        }

        if (Schema::hasTable('msu_purchase_order_items') && Schema::hasColumn('msu_purchase_order_items', 'item_name')) {
            DB::statement('ALTER TABLE msu_purchase_order_items MODIFY item_name VARCHAR(1500) NULL');
        }

        if (Schema::hasTable('msu_grn_items') && Schema::hasColumn('msu_grn_items', 'item_name')) {
            DB::statement('ALTER TABLE msu_grn_items MODIFY item_name VARCHAR(1500) NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('msu_grn_items') && Schema::hasColumn('msu_grn_items', 'item_name')) {
            DB::statement('ALTER TABLE msu_grn_items MODIFY item_name VARCHAR(700) NULL');
        }

        if (Schema::hasTable('msu_purchase_order_items') && Schema::hasColumn('msu_purchase_order_items', 'item_name')) {
            DB::statement('ALTER TABLE msu_purchase_order_items MODIFY item_name VARCHAR(700) NULL');
        }

        if (Schema::hasTable('msu_item_masters') && Schema::hasColumn('msu_item_masters', 'item_name')) {
            DB::statement('ALTER TABLE msu_item_masters MODIFY item_name VARCHAR(700) NOT NULL');
        }
    }

    private function dropNamedIndexIfExists(string $table, string $indexName): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        foreach (Schema::getIndexes($table) as $index) {
            if (($index['name'] ?? '') === $indexName) {
                Schema::table($table, function (Blueprint $blueprint) use ($indexName) {
                    $blueprint->dropUnique($indexName);
                });
                return;
            }
        }
    }

    private function dropItemNameUniqueIndexes(string $table): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'item_name')) {
            return;
        }

        $dropped = [];
        foreach (Schema::getIndexes($table) as $index) {
            $name = $index['name'] ?? '';
            $columns = $index['columns'] ?? [];
            $isUnique = (bool) ($index['unique'] ?? false);

            if ($name === '' || $name === 'primary' || in_array($name, $dropped, true)) {
                continue;
            }

            if (! $isUnique || ! in_array('item_name', $columns, true)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($name) {
                $blueprint->dropUnique($name);
            });
            $dropped[] = $name;
        }
    }
};
