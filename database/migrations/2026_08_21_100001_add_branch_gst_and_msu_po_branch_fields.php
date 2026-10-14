<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->string('gst_number', 50)->nullable()->after('email');
            $table->string('contact_person', 255)->nullable()->after('gst_number');
        });

        Schema::table('msu_purchase_orders', function (Blueprint $table) {
            $table->foreignId('branch_id')
                ->nullable()
                ->after('vendor_id')
                ->constrained('branches')
                ->nullOnDelete();
            $table->date('delivery_date')->nullable()->after('po_date');
            $table->date('date_of_commencement')->nullable()->after('delivery_date');
            $table->string('shipping_gst', 50)->nullable()->after('shipping_address');
            $table->string('shipping_contact_person', 255)->nullable()->after('shipping_gst');
        });
    }

    public function down(): void
    {
        Schema::table('msu_purchase_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
            $table->dropColumn([
                'delivery_date',
                'date_of_commencement',
                'shipping_gst',
                'shipping_contact_person',
            ]);
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn(['gst_number', 'contact_person']);
        });
    }
};
