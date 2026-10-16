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
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_orders', 'minimum_advance_payment_percentage')) {
                $table->decimal('minimum_advance_payment_percentage', 5, 2)->default(0)->after('payment_terms');
            }
            if (! Schema::hasColumn('purchase_orders', 'total_quantity')) {
                $table->integer('total_quantity')->default(0)->after('shipping');
            }
            if (! Schema::hasColumn('purchase_orders', 'sub_total')) {
                $table->decimal('sub_total', 15, 2)->default(0)->after('total_quantity');
            }
            if (! Schema::hasColumn('purchase_orders', 'total_discount')) {
                $table->decimal('total_discount', 15, 2)->default(0)->after('sub_total');
            }
            if (! Schema::hasColumn('purchase_orders', 'taxable_amount')) {
                $table->decimal('taxable_amount', 15, 2)->default(0)->after('total_discount');
            }
            if (! Schema::hasColumn('purchase_orders', 'total_gst')) {
                $table->decimal('total_gst', 15, 2)->default(0)->after('taxable_amount');
            }
            if (! Schema::hasColumn('purchase_orders', 'total_cgst')) {
                $table->decimal('total_cgst', 15, 2)->default(0)->after('total_gst');
            }
            if (! Schema::hasColumn('purchase_orders', 'total_sgst')) {
                $table->decimal('total_sgst', 15, 2)->default(0)->after('total_cgst');
            }
            if (! Schema::hasColumn('purchase_orders', 'total_igst')) {
                $table->decimal('total_igst', 15, 2)->default(0)->after('total_sgst');
            }
        });

        Schema::table('po_items', function (Blueprint $table) {
            if (! Schema::hasColumn('po_items', 'mrp')) {
                $table->decimal('mrp', 12, 2)->default(0)->after('price');
            }
            if (! Schema::hasColumn('po_items', 'gst_percent')) {
                $table->decimal('gst_percent', 5, 2)->default(0)->after('mrp');
            }
            if (! Schema::hasColumn('po_items', 'gst_amount')) {
                $table->decimal('gst_amount', 12, 2)->default(0)->after('gst_percent');
            }
            if (! Schema::hasColumn('po_items', 'cgst_amount')) {
                $table->decimal('cgst_amount', 12, 2)->default(0)->after('gst_amount');
            }
            if (! Schema::hasColumn('po_items', 'sgst_amount')) {
                $table->decimal('sgst_amount', 12, 2)->default(0)->after('cgst_amount');
            }
            if (! Schema::hasColumn('po_items', 'igst_amount')) {
                $table->decimal('igst_amount', 12, 2)->default(0)->after('sgst_amount');
            }
            if (! Schema::hasColumn('po_items', 'rate_after_discount')) {
                $table->decimal('rate_after_discount', 12, 2)->default(0)->after('igst_amount');
            }
            if (! Schema::hasColumn('po_items', 'taxable_amount')) {
                $table->decimal('taxable_amount', 12, 2)->default(0)->after('rate_after_discount');
            }
            if (! Schema::hasColumn('po_items', 'base_amount')) {
                $table->decimal('base_amount', 12, 2)->default(0)->after('taxable_amount');
            }
            if (! Schema::hasColumn('po_items', 'discount_amount')) {
                $table->decimal('discount_amount', 12, 2)->default(0)->after('base_amount');
            }
            if (! Schema::hasColumn('po_items', 'epr')) {
                $table->decimal('epr', 12, 2)->nullable()->default(0)->after('discount_amount');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $cols = [
                'minimum_advance_payment_percentage', 'total_quantity', 'sub_total', 'total_discount',
                'taxable_amount', 'total_gst', 'total_cgst', 'total_sgst', 'total_igst'
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('purchase_orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('po_items', function (Blueprint $table) {
            $cols = [
                'mrp', 'gst_percent', 'gst_amount', 'cgst_amount', 'sgst_amount',
                'igst_amount', 'rate_after_discount', 'taxable_amount', 'base_amount', 'discount_amount'
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('po_items', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
