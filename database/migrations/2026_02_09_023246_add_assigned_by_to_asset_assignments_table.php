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
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->foreign('assigned_by')->references('id')->on('users')->onDelete('cascade');
            $table->dateTime('assigned_at')->nullable();
            $table->text('assigned_notes')->nullable();
            $table->unsignedBigInteger('assiged_emoloyee')->nullable();
            $table->foreign('assiged_emoloyee')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_assignments', function (Blueprint $table) {
            $table->dropForeign(['assigned_by']);
            $table->dropForeign(['assiged_emoloyee']);
            $table->dropColumn('assigned_by');
            $table->dropColumn('assigned_at');
            $table->dropColumn('assigned_notes');
            $table->dropColumn('assiged_emoloyee');
        });
    }
};
