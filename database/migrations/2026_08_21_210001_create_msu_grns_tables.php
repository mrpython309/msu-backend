<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('msu_grns', function (Blueprint $table) {
            $table->id();
            $table->string('grn_number')->unique();
            $table->string('receipt_number')->nullable();
            $table->foreignId('msu_purchase_order_id')->constrained('msu_purchase_orders')->restrictOnDelete();
            $table->foreignId('vendor_id')->nullable()->constrained('vendors')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('employee_master_id')->nullable()->constrained('employee_masters')->nullOnDelete();
            $table->string('vendor_invoice_number')->nullable();
            $table->date('vendor_invoice_date')->nullable();
            $table->date('grn_date');
            $table->string('status', 50)->default('created');
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->index(['grn_date', 'vendor_id']);
        });

        Schema::create('msu_grn_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('msu_grn_id')->constrained('msu_grns')->cascadeOnDelete();
            $table->foreignId('msu_purchase_order_item_id')->constrained('msu_purchase_order_items')->restrictOnDelete();
            $table->foreignId('msu_item_master_id')->nullable()->constrained('msu_item_masters')->nullOnDelete();
            $table->string('item_code')->nullable();
            $table->string('item_name')->nullable();
            $table->string('hsn')->nullable();
            $table->decimal('ordered_quantity', 15, 3)->default(0);
            $table->decimal('previously_received_quantity', 15, 3)->default(0);
            $table->decimal('received_quantity', 15, 3)->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('msu_grn_items');
        Schema::dropIfExists('msu_grns');
    }
};
