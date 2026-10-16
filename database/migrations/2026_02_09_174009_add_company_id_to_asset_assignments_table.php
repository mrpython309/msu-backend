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
        Schema::table('asset_assignments', function (Blueprint $table) {
            $table->unsignedBigInteger('from_company_id')->nullable();
             $table->foreign('from_company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->unsignedBigInteger('to_company_id')->nullable();
             $table->foreign('to_company_id')->references('id')->on('companies')->onDelete('cascade');
             $table->enum('assigned_to',['employee','branch'])->default('branch');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_assignments', function (Blueprint $table) {
              $table->dropForeign(['from_company_id']);
            $table->dropColumn('from_company_id');
              $table->dropForeign(['to_company_id']);
            $table->dropColumn('to_company_id');
            $table->dropColumn('assigned_to');
        });
    }
};
