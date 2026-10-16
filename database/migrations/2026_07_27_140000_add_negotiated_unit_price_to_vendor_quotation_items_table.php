<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_quotation_items', function (Blueprint $table) {
            if (! Schema::hasColumn('vendor_quotation_items', 'negotiated_unit_price')) {
                $table->decimal('negotiated_unit_price', 15, 2)->nullable()->after('unit_price');
            }
            if (! Schema::hasColumn('vendor_quotation_items', 'negotiated_total')) {
                $table->decimal('negotiated_total', 15, 2)->nullable()->after('total');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vendor_quotation_items', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('vendor_quotation_items', 'negotiated_unit_price')) {
                $columns[] = 'negotiated_unit_price';
            }
            if (Schema::hasColumn('vendor_quotation_items', 'negotiated_total')) {
                $columns[] = 'negotiated_total';
            }
            if (! empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
