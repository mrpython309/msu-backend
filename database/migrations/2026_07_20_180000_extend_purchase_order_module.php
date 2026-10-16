<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_orders', 'indent_id')) {
                $table->foreignId('indent_id')->nullable()->after('pr_id')->constrained('indents')->nullOnDelete();
            }
            if (! Schema::hasColumn('purchase_orders', 'po_source')) {
                $table->string('po_source', 30)->nullable()->after('po_type');
            }
            if (! Schema::hasColumn('purchase_orders', 'department_id')) {
                $table->foreignId('department_id')->nullable()->after('department_name')->constrained('departments')->nullOnDelete();
            }
            if (! Schema::hasColumn('purchase_orders', 'store_id')) {
                $table->foreignId('store_id')->nullable()->after('department_id')->constrained('departments')->nullOnDelete();
            }
            if (! Schema::hasColumn('purchase_orders', 'notes')) {
                $table->text('notes')->nullable()->after('remarks');
            }
            if (! Schema::hasColumn('purchase_orders', 'submitted_at')) {
                $table->timestamp('submitted_at')->nullable()->after('notes');
            }
            if (! Schema::hasColumn('purchase_orders', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('submitted_at');
            }
        });

        Schema::table('po_items', function (Blueprint $table) {
            if (! Schema::hasColumn('po_items', 'conversion')) {
                $table->string('conversion')->nullable()->after('purchase_unit');
            }
            if (! Schema::hasColumn('po_items', 'discount')) {
                $table->decimal('discount', 12, 2)->nullable()->after('uepr');
            }
            if (! Schema::hasColumn('po_items', 'discount_type')) {
                $table->string('discount_type', 20)->nullable()->after('discount');
            }
            if (! Schema::hasColumn('po_items', 'remarks')) {
                $table->text('remarks')->nullable()->after('discount_type');
            }
            if (! Schema::hasColumn('po_items', 'item_category_id')) {
                $table->foreignId('item_category_id')->nullable()->after('item_sub_category_name')->constrained('asset_categories')->nullOnDelete();
            }
            if (! Schema::hasColumn('po_items', 'item_sub_category_id')) {
                $table->foreignId('item_sub_category_id')->nullable()->after('item_category_id')->constrained('asset_categories')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_orders', 'indent_id')) {
                $table->dropConstrainedForeignId('indent_id');
            }
            if (Schema::hasColumn('purchase_orders', 'store_id')) {
                $table->dropConstrainedForeignId('store_id');
            }
            if (Schema::hasColumn('purchase_orders', 'department_id')) {
                $table->dropConstrainedForeignId('department_id');
            }
            foreach (['po_source', 'notes', 'submitted_at', 'approved_at'] as $col) {
                if (Schema::hasColumn('purchase_orders', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('po_items', function (Blueprint $table) {
            foreach (['item_sub_category_id', 'item_category_id'] as $fk) {
                if (Schema::hasColumn('po_items', $fk)) {
                    $table->dropConstrainedForeignId($fk);
                }
            }
            foreach (['conversion', 'discount', 'discount_type', 'remarks'] as $col) {
                if (Schema::hasColumn('po_items', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
