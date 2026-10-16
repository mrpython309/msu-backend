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
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_no')->unique();
            $table->unsignedBigInteger('quotation_id')->nullable();
            $table->unsignedBigInteger('vendor_id');
            $table->date('purchase_date');
            $table->date('delivery_date')->nullable();
            $table->string('payment_terms')->nullable();
            $table->string('currency')->default('USD');
            $table->decimal('tax', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('shipping', 12, 2)->default(0);
            $table->decimal('grand_total', 12, 2)->default(0);
            $table->string('status')->default('Draft');
            $table->timestamps();
            
            $table->foreign('quotation_id')->references('id')->on('vendor_quotations')->onDelete('set null');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_orders');
    }
};
