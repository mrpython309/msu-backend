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
        Schema::create('grn_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('grn_id');
            $table->unsignedBigInteger('po_item_id');
            $table->integer('ordered_quantity');
            $table->integer('received_quantity');
            $table->integer('accepted_quantity')->default(0);
            $table->integer('rejected_quantity')->default(0);
            $table->string('damage_reason')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            
            $table->foreign('grn_id')->references('id')->on('grns')->onDelete('cascade');
            $table->foreign('po_item_id')->references('id')->on('po_items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grn_items');
    }
};
