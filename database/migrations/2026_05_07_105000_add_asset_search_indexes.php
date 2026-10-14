<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('assets')) {
            return;
        }

        Schema::table('assets', function (Blueprint $table): void {
            if (! $this->indexExists('assets', 'assets_asset_code_idx')) {
                $table->index('asset_code', 'assets_asset_code_idx');
            }
            if (! $this->indexExists('assets', 'assets_serial_no_idx')) {
                $table->index('serial_no', 'assets_serial_no_idx');
            }
            if (! $this->indexExists('assets', 'assets_asset_name_idx')) {
                $table->index('asset_name', 'assets_asset_name_idx');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('assets')) {
            return;
        }

        Schema::table('assets', function (Blueprint $table): void {
            if ($this->indexExists('assets', 'assets_asset_code_idx')) {
                $table->dropIndex('assets_asset_code_idx');
            }
            if ($this->indexExists('assets', 'assets_serial_no_idx')) {
                $table->dropIndex('assets_serial_no_idx');
            }
            if ($this->indexExists('assets', 'assets_asset_name_idx')) {
                $table->dropIndex('assets_asset_name_idx');
            }
        });
    }

    private function indexExists(string $table, string $indexName): bool
    {
        $row = DB::selectOne(
            'SELECT 1
             FROM information_schema.statistics
             WHERE table_schema = DATABASE()
               AND table_name = ?
               AND index_name = ?
             LIMIT 1',
            [$table, $indexName]
        );

        return $row !== null;
    }
};

