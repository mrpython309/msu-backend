<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_orders', 'grn_store')) {
                $table->string('grn_store')->nullable()->after('delivery_date');
            }

            if (! Schema::hasColumn('purchase_orders', 'po_type')) {
                $table->string('po_type', 50)->nullable()->after('grn_store');
            }

            if (! Schema::hasColumn('purchase_orders', 'department_name')) {
                $table->string('department_name')->nullable()->after('po_type');
            }

            if (! Schema::hasColumn('purchase_orders', 'discount_type')) {
                $table->string('discount_type', 20)->nullable()->after('discount');
            }
        });

        Schema::table('po_items', function (Blueprint $table) {
            if (! Schema::hasColumn('po_items', 'item_code')) {
                $table->string('item_code', 255)->nullable()->after('item_name');
            }
            if (! Schema::hasColumn('po_items', 'hsn_code')) {
                $table->string('hsn_code', 50)->nullable()->after('item_code');
            }

            if (! Schema::hasColumn('po_items', 'manufacturer')) {
                $table->string('manufacturer', 255)->nullable()->after('description');
            }
            if (! Schema::hasColumn('po_items', 'marketer_name')) {
                $table->string('marketer_name', 255)->nullable()->after('manufacturer');
            }

            if (! Schema::hasColumn('po_items', 'free_qty')) {
                $table->integer('free_qty')->default(0)->after('quantity');
            }

            if (! Schema::hasColumn('po_items', 'epr')) {
                $table->decimal('epr', 12, 2)->nullable()->after('free_qty');
            }
            if (! Schema::hasColumn('po_items', 'uepr')) {
                $table->decimal('uepr', 12, 2)->nullable()->after('epr');
            }

            if (! Schema::hasColumn('po_items', 'item_category_name')) {
                $table->string('item_category_name')->nullable()->after('item_type');
            }
            if (! Schema::hasColumn('po_items', 'item_sub_category_name')) {
                $table->string('item_sub_category_name')->nullable()->after('item_category_name');
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            foreach (['grn_store', 'po_type', 'department_name', 'discount_type'] as $col) {
                if (Schema::hasColumn('purchase_orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('po_items', function (Blueprint $table) {
            foreach ([
                'item_code',
                'hsn_code',
                'manufacturer',
                'marketer_name',
                'free_qty',
                'epr',
                'uepr',
                'item_category_name',
                'item_sub_category_name',
            ] as $col) {
                if (Schema::hasColumn('po_items', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};

