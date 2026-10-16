<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('msu_purchase_orders', function (Blueprint $table) {
            $table->decimal('miscellaneous_tax', 8, 2)->default(0)->after('miscellaneous_charges');
        });
    }

    public function down(): void
    {
        Schema::table('msu_purchase_orders', function (Blueprint $table) {
            $table->dropColumn('miscellaneous_tax');
        });
    }
};
