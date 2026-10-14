<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grns', function (Blueprint $table) {
            if (! Schema::hasColumn('grns', 'challan_number')) {
                $table->string('challan_number')->nullable()->after('invoice_date');
            }
            if (! Schema::hasColumn('grns', 'challan_date')) {
                $table->date('challan_date')->nullable()->after('challan_number');
            }
            if (! Schema::hasColumn('grns', 'department_name')) {
                $table->string('department_name')->nullable()->after('po_type');
            }
            if (! Schema::hasColumn('grns', 'inspection_status')) {
                $table->string('inspection_status', 30)->default('pending')->after('characteristic');
            }
            if (! Schema::hasColumn('grns', 'inspection_remarks')) {
                $table->text('inspection_remarks')->nullable()->after('inspection_status');
            }
            if (! Schema::hasColumn('grns', 'approved_by')) {
                $table->foreignId('approved_by')->nullable()->after('inspected_by')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('grns', 'deleted_at')) {
                $table->softDeletes();
            }
        });

        Schema::table('grn_items', function (Blueprint $table) {
            if (! Schema::hasColumn('grn_items', 'batch_number')) {
                $table->string('batch_number')->nullable()->after('model_name');
            }
            if (! Schema::hasColumn('grn_items', 'stock_after')) {
                $table->decimal('stock_after', 15, 3)->nullable()->after('stock_in_hand');
            }
            if (! Schema::hasColumn('grn_items', 'unit_rate')) {
                $table->decimal('unit_rate', 15, 3)->nullable()->after('ordered_quantity');
            }
        });
    }

    public function down(): void
    {
        Schema::table('grn_items', function (Blueprint $table) {
            foreach (['batch_number', 'stock_after', 'unit_rate'] as $col) {
                if (Schema::hasColumn('grn_items', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('grns', function (Blueprint $table) {
            if (Schema::hasColumn('grns', 'approved_by')) {
                $table->dropConstrainedForeignId('approved_by');
            }

            foreach (['challan_number', 'challan_date', 'department_name', 'inspection_status', 'inspection_remarks', 'deleted_at'] as $col) {
                if (Schema::hasColumn('grns', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
