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
        Schema::create('msu_daily_item_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('msu_item_master_id')->constrained('msu_item_masters')->cascadeOnDelete();
            $table->date('date');
            $table->decimal('opening_stock', 15, 2)->default(0);
            $table->decimal('stock_in', 15, 2)->default(0);
            $table->decimal('stock_out', 15, 2)->default(0);
            $table->decimal('closing_stock', 15, 2)->default(0);
            $table->timestamps();

            $table->unique(['msu_item_master_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('msu_daily_item_stocks');
    }
};
