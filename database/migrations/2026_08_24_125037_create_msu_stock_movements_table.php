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
        Schema::create('msu_stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('msu_item_master_id')->constrained('msu_item_masters')->restrictOnDelete();
            $table->enum('transaction_type', ['in', 'out', 'adjustment']);
            $table->decimal('quantity', 15, 3);
            $table->decimal('stock_before', 15, 3);
            $table->decimal('stock_after', 15, 3);
            $table->string('reference_type')->nullable(); // Polymorphic relation class
            $table->unsignedBigInteger('reference_id')->nullable(); // Polymorphic relation id
            $table->text('remarks')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('msu_stock_movements');
    }
};
