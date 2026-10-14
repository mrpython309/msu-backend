<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
              if (!Schema::hasColumn('assets', 'asset_prefix')) {
            $table->string('asset_prefix')->nullable();
              }
                if (!Schema::hasColumn('assets', 'asset_nos')) {
            $table->string('asset_nos')->nullable();
                }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropColumn('asset_prefix');
            $table->dropColumn('asset_nos');
        });
    }
};
