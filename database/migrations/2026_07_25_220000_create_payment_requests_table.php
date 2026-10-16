<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_requests', function (Blueprint $table) {
            $table->id();
            $table->string('payment_request_no')->unique();
            $table->foreignId('purchase_order_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users');
            $table->dateTime('request_date');
            $table->decimal('amount', 15, 2);
            $table->string('payment_method', 50)->nullable();
            $table->string('status', 30)->default('Pending'); // Pending, Approved, Paid, Rejected
            $table->date('payment_date')->nullable();
            $table->string('reference_number')->nullable();
            $table->text('remarks')->nullable();
            $table->string('attachment')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('rejected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['purchase_order_id', 'status']);
            $table->index(['status', 'request_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_requests');
    }
};
