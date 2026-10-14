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
        Schema::table('msu_grns', function (Blueprint $table) {
            $table->decimal('miscellaneous_charges', 15, 2)->default(0)->after('status');
            $table->decimal('miscellaneous_tax', 8, 2)->default(0)->after('miscellaneous_charges');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('msu_grns', function (Blueprint $table) {
            $table->dropColumn(['miscellaneous_charges', 'miscellaneous_tax']);
        });
    }
};
