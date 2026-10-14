<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('msu_work_orders', function (Blueprint $table) {
            $table->id();
            $table->string('wo_number')->unique();
            $table->date('wo_date');
            
            // New fields specific to Work Order
            $table->date('contract_period_from')->nullable();
            $table->date('contract_period_to')->nullable();
            $table->text('subject')->nullable();
            
            // Fields mirroring Purchase Order
            $table->date('delivery_date')->nullable();
            $table->date('date_of_commencement')->nullable();
            $table->foreignId('vendor_id')->constrained('vendors')->restrictOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('employee_master_id')->nullable()->constrained('employee_masters')->nullOnDelete();
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('shipping_detail_id')->nullable()->constrained('shipping_details')->nullOnDelete();
            
            $table->text('shipping_address')->nullable();
            $table->string('shipping_contact_person')->nullable();
            $table->string('shipping_gst')->nullable();
            $table->text('remarks')->nullable();
            
            $table->foreignId('payment_term_id')->nullable()->constrained('payment_terms')->nullOnDelete();
            $table->foreignId('miscellaneous_charge_id')->nullable()->constrained('miscellaneous_charges')->nullOnDelete();
            
            $table->decimal('total_value', 15, 2)->default(0);
            $table->decimal('miscellaneous_charges', 15, 2)->default(0);
            $table->decimal('miscellaneous_tax', 15, 2)->default(0);
            $table->decimal('sgst', 15, 2)->default(0);
            $table->decimal('cgst', 15, 2)->default(0);
            $table->decimal('igst', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);
            
            $table->string('status')->default('wo_created'); // wo_created, partially_passed, passed, cancelled
            $table->text('cancel_reason')->nullable();
            
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('msu_work_order_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('msu_work_order_id')->constrained('msu_work_orders')->cascadeOnDelete();
            $table->text('work_description');
            $table->string('hsn')->nullable();
            $table->decimal('quantity', 15, 3)->default(0);
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('tax', 8, 2)->default(0)->comment('Tax percent');
            $table->decimal('net_value', 15, 2)->default(0);
            $table->decimal('received_quantity', 15, 3)->default(0)->comment('Invoiced quantity');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
        
        Schema::create('msu_work_order_charges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('msu_work_order_id')->constrained('msu_work_orders')->cascadeOnDelete();
            $table->string('charge_name');
            $table->decimal('amount', 15, 2)->default(0);
            $table->decimal('tax_percentage', 8, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('msu_work_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('wi_number')->unique();
            $table->date('invoice_date');
            $table->string('invoice_number')->nullable();
            $table->foreignId('msu_work_order_id')->constrained('msu_work_orders')->restrictOnDelete();
            $table->foreignId('vendor_id')->constrained('vendors')->restrictOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->string('document_path')->nullable();
            
            $table->decimal('total_value', 15, 2)->default(0);
            $table->decimal('sgst', 15, 2)->default(0);
            $table->decimal('cgst', 15, 2)->default(0);
            $table->decimal('igst', 15, 2)->default(0);
            $table->decimal('grand_total', 15, 2)->default(0);

            $table->string('status')->default('passed');
            
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });
        
        Schema::create('msu_work_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('msu_work_invoice_id')->constrained('msu_work_invoices')->cascadeOnDelete();
            $table->foreignId('msu_work_order_item_id')->constrained('msu_work_order_items')->restrictOnDelete();
            $table->text('work_description');
            $table->decimal('quantity', 15, 3)->default(0);
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('tax', 8, 2)->default(0)->comment('Tax percent');
            $table->decimal('net_value', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('msu_work_invoice_items');
        Schema::dropIfExists('msu_work_invoices');
        Schema::dropIfExists('msu_work_order_charges');
        Schema::dropIfExists('msu_work_order_items');
        Schema::dropIfExists('msu_work_orders');
    }
};
