<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('asset_categories', function (Blueprint $table) {
            if (! Schema::hasColumn('asset_categories', 'item_type')) {
                $table->string('item_type')->default('asset')->after('status')->index();
            }
        });

        Schema::table('assets', function (Blueprint $table) {
            if (! Schema::hasColumn('assets', 'indent_id')) {
                $table->foreignId('indent_id')->nullable()->after('purchase_order_id')->constrained('indents')->nullOnDelete();
            }
            if (! Schema::hasColumn('assets', 'department_id')) {
                $table->foreignId('department_id')->nullable()->after('indent_id')->constrained('departments')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            if (Schema::hasColumn('assets', 'department_id')) {
                $table->dropConstrainedForeignId('department_id');
            }
            if (Schema::hasColumn('assets', 'indent_id')) {
                $table->dropConstrainedForeignId('indent_id');
            }
        });

        Schema::table('asset_categories', function (Blueprint $table) {
            if (Schema::hasColumn('asset_categories', 'item_type')) {
                $table->dropColumn('item_type');
            }
        });
    }
};
