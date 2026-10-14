<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->string('pan_number', 20)->nullable()->after('gst_number');
        });

        Schema::table('payment_terms', function (Blueprint $table) {
            $table->string('payment_mode', 100)->nullable()->after('term_name');
        });
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropColumn('pan_number');
        });

        Schema::table('payment_terms', function (Blueprint $table) {
            $table->dropColumn('payment_mode');
        });
    }
};
