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
        Schema::create('asset_warranties', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_id');
            $table->foreign('asset_id')->references('id')->on('assets')->onDelete('cascade');
            $table->string('covered_parts')->nullable();
            $table->enum('warranty_type', ['manufacturer','vendor','extended','amc'])->default('manufacturer');
            $table->string('warranty_number')->nullable();
            $table->date('warranty_expire_date')->nullable();
            $table->string('usage_metric')->nullable(); // hours, km, pages
            $table->integer('max_usage')->nullable();
            $table->enum('status', ['active', 'expired'])->default('active');
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_warranties');
    }
};
