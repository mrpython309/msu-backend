<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('msu_item_masters', function (Blueprint $table) {
            $table->decimal('threshold_quantity', 15, 3)->nullable()->after('current_stock');
        });
    }

    public function down(): void
    {
        Schema::table('msu_item_masters', function (Blueprint $table) {
            $table->dropColumn('threshold_quantity');
        });
    }
};
