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
        Schema::table('indents', function (Blueprint $table) {
            $table->foreignId('to_store_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('request_type')->nullable();
            $table->boolean('is_urgent')->default(false);
            $table->string('tag')->nullable();
        });

        Schema::table('indent_items', function (Blueprint $table) {
            $table->boolean('is_kit')->default(false);
            $table->string('composition')->nullable();
            $table->string('item_type')->nullable();
            $table->decimal('stock_in_hand', 15, 2)->nullable();
            $table->string('conversion')->nullable();
            $table->decimal('facility_stock_in_hand', 15, 2)->nullable();
            $table->decimal('to_stock_in_hand', 15, 2)->nullable();
            $table->decimal('indent_min_qty', 15, 2)->nullable();
            $table->decimal('indent_max_qty', 15, 2)->nullable();
            $table->text('item_remarks')->nullable();
            $table->string('manufacturer')->nullable();
        });

        Schema::table('purchase_requisitions', function (Blueprint $table) {
            $table->string('fan_no')->nullable();
            $table->foreignId('requisition_store_id')->nullable()->constrained('departments')->nullOnDelete();
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->foreignId('grn_store_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('po_type')->nullable();
        });

        Schema::table('po_items', function (Blueprint $table) {
            $table->string('manufacturer')->nullable();
            $table->string('hsn_code')->nullable();
            $table->decimal('qoh', 15, 2)->nullable();
            $table->date('estimated_delivery_date')->nullable();
            $table->decimal('discount', 15, 2)->nullable();
            $table->string('discount_type')->nullable();
            $table->text('remarks')->nullable();
            $table->decimal('free_qty', 15, 2)->nullable();
            $table->string('marketer_name')->nullable();
            $table->string('purchase_unit')->nullable();
            $table->decimal('epr', 15, 2)->nullable();
            $table->decimal('uepr', 15, 2)->nullable();
            $table->string('item_code')->nullable();
            $table->string('item_type')->nullable();
            $table->string('conversion')->nullable();
            $table->string('item_sub_category')->nullable();
        });

        Schema::table('grns', function (Blueprint $table) {
            $table->date('bill_date')->nullable();
            $table->string('delivery_memo_no')->nullable();
            $table->string('cost_center_name')->nullable();
            $table->dateTime('gate_entry_date')->nullable();
            $table->string('characteristic')->nullable();
            $table->decimal('bill_amount', 15, 2)->nullable();
            $table->string('gate_entry_no')->nullable();
            $table->date('delivery_memo_date')->nullable();
            $table->string('po_type')->nullable();
        });

        Schema::table('grn_items', function (Blueprint $table) {
            $table->string('hsn_code')->nullable();
            $table->string('marketer_name')->nullable();
            $table->string('issue_unit')->nullable();
            $table->decimal('free_qty', 15, 2)->nullable();
            $table->string('price_type')->nullable();
            $table->string('purchase_unit')->nullable();
            $table->decimal('stock_in_hand', 15, 2)->nullable();
            $table->string('item_code')->nullable();
            $table->string('manufacturer')->nullable();
            $table->string('conversion')->nullable();
            $table->decimal('challan_quantity', 15, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('indents', function (Blueprint $table) {
            $table->dropForeign(['to_store_id']);
            $table->dropColumn(['to_store_id', 'request_type', 'is_urgent', 'tag']);
        });

        Schema::table('indent_items', function (Blueprint $table) {
            $table->dropColumn([
                'is_kit', 'composition', 'item_type', 'stock_in_hand', 'conversion',
                'facility_stock_in_hand', 'to_stock_in_hand', 'indent_min_qty',
                'indent_max_qty', 'item_remarks', 'manufacturer'
            ]);
        });

        Schema::table('purchase_requisitions', function (Blueprint $table) {
            $table->dropForeign(['requisition_store_id']);
            $table->dropColumn(['fan_no', 'requisition_store_id']);
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->dropForeign(['grn_store_id']);
            $table->dropColumn(['grn_store_id', 'po_type']);
        });

        Schema::table('po_items', function (Blueprint $table) {
            $table->dropColumn([
                'manufacturer', 'hsn_code', 'qoh', 'estimated_delivery_date', 'discount',
                'discount_type', 'remarks', 'free_qty', 'marketer_name', 'purchase_unit',
                'epr', 'uepr', 'item_code', 'item_type', 'conversion', 'item_sub_category'
            ]);
        });

        Schema::table('grns', function (Blueprint $table) {
            $table->dropColumn([
                'bill_date', 'delivery_memo_no', 'cost_center_name', 'gate_entry_date',
                'characteristic', 'bill_amount', 'gate_entry_no', 'delivery_memo_date', 'po_type'
            ]);
        });

        Schema::table('grn_items', function (Blueprint $table) {
            $table->dropColumn([
                'hsn_code', 'marketer_name', 'issue_unit', 'free_qty', 'price_type',
                'purchase_unit', 'stock_in_hand', 'item_code', 'manufacturer',
                'conversion', 'challan_quantity'
            ]);
        });
    }
};
