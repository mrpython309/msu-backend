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
        Schema::create('indent_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('indent_id');
            $table->string('item_name');
            $table->unsignedBigInteger('category_id')->nullable();
            $table->text('description')->nullable();
            $table->integer('quantity');
            $table->string('unit')->default('Nos');
            $table->decimal('estimated_price', 12, 2)->nullable();
            $table->decimal('total_amount', 12, 2)->nullable();
            $table->timestamps();
            
            $table->foreign('indent_id')->references('id')->on('indents')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('asset_categories')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('indent_items');
    }
};
