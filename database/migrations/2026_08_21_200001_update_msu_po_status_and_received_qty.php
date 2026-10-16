<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('msu_purchase_order_items', function (Blueprint $table) {
            if (! Schema::hasColumn('msu_purchase_order_items', 'received_quantity')) {
                $table->decimal('received_quantity', 15, 3)->default(0)->after('quantity');
            }
        });

        // Expand status column for new values (MySQL enum → string)
        Schema::table('msu_purchase_orders', function (Blueprint $table) {
            $table->string('status', 50)->default('po_created')->change();
        });

        DB::table('msu_purchase_orders')->whereIn('status', ['draft', 'confirmed'])->update(['status' => 'po_created']);
        // cancelled stays cancelled
    }

    public function down(): void
    {
        DB::table('msu_purchase_orders')->where('status', 'po_created')->update(['status' => 'draft']);
        DB::table('msu_purchase_orders')->where('status', 'grn_received')->update(['status' => 'confirmed']);
        DB::table('msu_purchase_orders')->where('status', 'grn_partially_received')->update(['status' => 'confirmed']);

        Schema::table('msu_purchase_orders', function (Blueprint $table) {
            $table->enum('status', ['draft', 'confirmed', 'cancelled'])->default('draft')->change();
        });

        Schema::table('msu_purchase_order_items', function (Blueprint $table) {
            if (Schema::hasColumn('msu_purchase_order_items', 'received_quantity')) {
                $table->dropColumn('received_quantity');
            }
        });
    }
};
