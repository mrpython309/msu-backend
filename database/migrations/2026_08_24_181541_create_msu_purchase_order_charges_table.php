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
        Schema::create('msu_purchase_order_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('msu_purchase_order_id')->constrained('msu_purchase_orders')->cascadeOnDelete();
            $table->string('charge_name');
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('tax_percentage', 8, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('msu_purchase_order_charges');
    }
};
