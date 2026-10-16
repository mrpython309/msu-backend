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
        Schema::create('grns', function (Blueprint $table) {
            $table->id();
            $table->string('grn_no')->unique();
            $table->unsignedBigInteger('po_id');
            $table->unsignedBigInteger('vendor_id');
            $table->date('received_date');
            $table->unsignedBigInteger('received_by');
            $table->string('warehouse')->nullable();
            $table->string('invoice_number')->nullable();
            $table->date('invoice_date')->nullable();
            $table->string('status')->default('Pending Inspection');
            $table->string('invoice_path')->nullable();
            $table->string('delivery_challan_path')->nullable();
            $table->timestamps();
            
            $table->foreign('po_id')->references('id')->on('purchase_orders')->onDelete('cascade');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
            $table->foreign('received_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grns');
    }
};
