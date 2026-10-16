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
        Schema::table('po_items', function (Blueprint $table) {
            if (!Schema::hasColumn('po_items', 'has_free_qty')) {
                $table->boolean('has_free_qty')->default(false)->after('quantity');
            }
            if (Schema::hasColumn('po_items', 'free_qty')) {
                $table->decimal('free_qty', 15, 2)->default(0)->change();
            } else {
                $table->decimal('free_qty', 15, 2)->default(0)->after('has_free_qty');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('po_items', function (Blueprint $table) {
            if (Schema::hasColumn('po_items', 'has_free_qty')) {
                $table->dropColumn('has_free_qty');
            }
            // Note: Not reverting free_qty back to integer to avoid data loss
        });
    }
};
