<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_details', function (Blueprint $table) {
            $table->id();
            $table->string('shipping_name');
            $table->string('gst_number', 50)->nullable();
            $table->text('address')->nullable();
            $table->string('contact_person')->nullable();
            $table->string('contact_no', 20)->nullable();
            $table->string('email')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        Schema::table('msu_purchase_orders', function (Blueprint $table) {
            $table->foreignId('shipping_detail_id')->nullable()->after('employee_master_id')
                ->constrained('shipping_details')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('msu_purchase_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shipping_detail_id');
        });

        Schema::dropIfExists('shipping_details');
    }
};
