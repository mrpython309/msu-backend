<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            if (! Schema::hasColumn('assets', 'product_category_id')) {
                $table->unsignedBigInteger('product_category_id')->nullable()->after('sub_category_id');
            }
            if (! Schema::hasColumn('assets', 'product_sub_category_id')) {
                $table->unsignedBigInteger('product_sub_category_id')->nullable()->after('product_category_id');
            }
        });

        // Drop FKs if present, then recreate as nullable where needed
        $this->dropForeignIfExists('assets', 'assets_asset_type_id_foreign');
        $this->dropForeignIfExists('assets', 'assets_category_id_foreign');
        $this->dropForeignIfExists('assets', 'assets_sub_category_id_foreign');

        DB::statement('ALTER TABLE assets MODIFY asset_type_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE assets MODIFY category_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE assets MODIFY sub_category_id BIGINT UNSIGNED NULL');

        Schema::table('assets', function (Blueprint $table) {
            if (! $this->foreignKeyExists('assets', 'assets_product_category_id_foreign')) {
                $table->foreign('product_category_id')->references('id')->on('product_categories')->nullOnDelete();
            }
            if (! $this->foreignKeyExists('assets', 'assets_product_sub_category_id_foreign')) {
                $table->foreign('product_sub_category_id')->references('id')->on('product_sub_categories')->nullOnDelete();
            }
            if (! $this->foreignKeyExists('assets', 'assets_asset_type_id_foreign')) {
                $table->foreign('asset_type_id')->references('id')->on('asset_types')->nullOnDelete();
            }
            if (! $this->foreignKeyExists('assets', 'assets_category_id_foreign')) {
                $table->foreign('category_id')->references('id')->on('asset_categories')->nullOnDelete();
            }
            if (! $this->foreignKeyExists('assets', 'assets_sub_category_id_foreign')) {
                $table->foreign('sub_category_id')->references('id')->on('asset_categories')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            if (Schema::hasColumn('assets', 'product_sub_category_id')) {
                $table->dropForeign(['product_sub_category_id']);
                $table->dropColumn('product_sub_category_id');
            }
            if (Schema::hasColumn('assets', 'product_category_id')) {
                $table->dropForeign(['product_category_id']);
                $table->dropColumn('product_category_id');
            }
        });
    }

    private function dropForeignIfExists(string $table, string $foreign): void
    {
        if ($this->foreignKeyExists($table, $foreign)) {
            Schema::table($table, function (Blueprint $blueprint) use ($foreign) {
                $blueprint->dropForeign($foreign);
            });
        }
    }

    private function foreignKeyExists(string $table, string $foreign): bool
    {
        $db = DB::getDatabaseName();
        $row = DB::selectOne(
            'SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
             WHERE CONSTRAINT_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = ?',
            [$db, $table, $foreign, 'FOREIGN KEY']
        );

        return (bool) $row;
    }
};
