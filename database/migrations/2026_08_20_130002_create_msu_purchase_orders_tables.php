<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('msu_purchase_orders', function (Blueprint $table) {
            $table->id();
            $table->string('po_number')->unique();
            $table->date('po_date');
            $table->foreignId('vendor_id')->constrained('vendors')->restrictOnDelete();
            $table->text('shipping_address')->nullable();
            $table->foreignId('payment_term_id')->nullable()->constrained('payment_terms')->nullOnDelete();
            $table->decimal('total_value', 15, 2)->default(0);
            $table->decimal('miscellaneous_charges', 15, 2)->default(0);
            $table->decimal('sgst', 15, 2)->default(0);
            $table->decimal('cgst', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            $table->enum('status', ['draft', 'confirmed', 'cancelled'])->default('draft');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('msu_purchase_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('msu_purchase_order_id')->constrained('msu_purchase_orders')->cascadeOnDelete();
            $table->foreignId('msu_item_master_id')->constrained('msu_item_masters')->restrictOnDelete();
            $table->string('item_code')->nullable();
            $table->string('item_name')->nullable();
            $table->string('hsn')->nullable();
            $table->decimal('quantity', 15, 3)->default(0);
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('tax', 8, 2)->default(0)->comment('Tax percent');
            $table->decimal('net_value', 15, 2)->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('msu_purchase_order_items');
        Schema::dropIfExists('msu_purchase_orders');
    }
};
