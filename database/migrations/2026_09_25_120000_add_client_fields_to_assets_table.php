<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            if (! Schema::hasColumn('assets', 'hsn_code')) {
                $table->string('hsn_code')->nullable()->after('serial_no');
            }
            if (! Schema::hasColumn('assets', 'uom')) {
                $table->string('uom', 50)->nullable()->after('items_count');
            }
            if (! Schema::hasColumn('assets', 'po_number')) {
                $table->string('po_number')->nullable()->after('invoice_details');
            }
            if (! Schema::hasColumn('assets', 'po_date')) {
                $table->date('po_date')->nullable()->after('po_number');
            }
            if (! Schema::hasColumn('assets', 'invoice_date')) {
                $table->date('invoice_date')->nullable()->after('po_date');
            }
            if (! Schema::hasColumn('assets', 'purchase_type')) {
                $table->string('purchase_type', 30)->nullable()->after('invoice_date');
            }
            if (! Schema::hasColumn('assets', 'ownership_type')) {
                $table->string('ownership_type', 30)->nullable()->after('purchase_type');
            }
            if (! Schema::hasColumn('assets', 'cost_centre')) {
                $table->string('cost_centre')->nullable()->after('ownership_type');
            }
            if (! Schema::hasColumn('assets', 'custodian_id')) {
                $table->unsignedBigInteger('custodian_id')->nullable()->after('department_id');
                $table->foreign('custodian_id')->references('id')->on('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            if (Schema::hasColumn('assets', 'custodian_id')) {
                $table->dropForeign(['custodian_id']);
                $table->dropColumn('custodian_id');
            }

            foreach (['cost_centre', 'ownership_type', 'purchase_type', 'invoice_date', 'po_date', 'po_number', 'uom', 'hsn_code'] as $column) {
                if (Schema::hasColumn('assets', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
