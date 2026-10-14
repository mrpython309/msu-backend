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
        Schema::table('assets', function (Blueprint $table) {
             if (!Schema::hasColumn('assets', 'brand_id')) {
            $table->unsignedBigInteger('brand_id')->nullable()->after('supplier');
            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('cascade');
             }
              if (!Schema::hasColumn('assets', 'invoice_details')) {
            $table->string('invoice_details')->nullable()->after('brand_id');
              }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropForeign(['brand_id']);
            $table->dropColumn('brand_id');
            $table->dropColumn('invoice_details');
        });
    }
};
