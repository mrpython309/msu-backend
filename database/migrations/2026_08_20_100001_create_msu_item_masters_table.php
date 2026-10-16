<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('msu_item_masters', function (Blueprint $table) {
            $table->id();
            $table->string('item_code')->unique();
            $table->string('item_name');
            $table->foreignId('product_category_id')->constrained('product_categories')->restrictOnDelete();
            $table->foreignId('product_sub_category_id')->constrained('product_sub_categories')->restrictOnDelete();
            $table->enum('expense_type', ['opex', 'capex']);
            $table->string('uom')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['product_category_id', 'product_sub_category_id', 'item_name'], 'msu_item_cat_sub_name_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('msu_item_masters');
    }
};
