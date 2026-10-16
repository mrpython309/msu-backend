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
        Schema::create('purchase_requisitions', function (Blueprint $table) {
            $table->id();
            $table->string('pr_no')->unique();
            $table->unsignedBigInteger('indent_id');
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->date('required_date')->nullable();
            $table->unsignedBigInteger('purchase_officer_id')->nullable();
            $table->string('status')->default('Pending');
            $table->timestamps();
            
            $table->foreign('indent_id')->references('id')->on('indents')->onDelete('cascade');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('set null');
            $table->foreign('purchase_officer_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_requisitions');
    }
};
