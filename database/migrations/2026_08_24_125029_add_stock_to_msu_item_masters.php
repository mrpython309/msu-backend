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
        Schema::table('msu_item_masters', function (Blueprint $table) {
            $table->decimal('opening_stock', 15, 3)->default(0)->after('uom');
            $table->decimal('current_stock', 15, 3)->default(0)->after('opening_stock');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('msu_item_masters', function (Blueprint $table) {
            $table->dropColumn(['opening_stock', 'current_stock']);
        });
    }
};
