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
            $table->enum('asset_structure', ['single', 'group'])->default('single')->after('asset_name');
            $table->integer('items_count')->nullable()->after('asset_structure');
            $table->unsignedBigInteger('category_id')->nullable()->after('asset_structure');
            $table->foreign('category_id')->references('id')->on('asset_categories')->onDelete('cascade');
            $table->unsignedBigInteger('sub_category_id')->nullable()->after('category_id');
            $table->foreign('sub_category_id')->references('id')->on('asset_categories')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropForeign(['sub_category_id']);
            $table->dropColumn('asset_structure');
            $table->dropColumn('items_count');
            $table->dropColumn('category_id');
            $table->dropColumn('sub_category_id');
        });
    }
};
