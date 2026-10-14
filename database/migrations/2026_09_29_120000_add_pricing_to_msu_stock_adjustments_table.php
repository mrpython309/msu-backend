<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('msu_stock_adjustments', function (Blueprint $table) {
            $table->string('unit')->nullable()->after('quantity');
            $table->decimal('price', 12, 2)->default(0)->after('unit');
            $table->decimal('total', 12, 2)->default(0)->after('price');
            $table->decimal('gst_percentage', 8, 2)->default(0)->after('total');
            $table->decimal('gst_amount', 12, 2)->default(0)->after('gst_percentage');
            $table->decimal('grand_total', 12, 2)->default(0)->after('gst_amount');
        });
    }

    public function down(): void
    {
        Schema::table('msu_stock_adjustments', function (Blueprint $table) {
            $table->dropColumn([
                'unit',
                'price',
                'total',
                'gst_percentage',
                'gst_amount',
                'grand_total',
            ]);
        });
    }
};
