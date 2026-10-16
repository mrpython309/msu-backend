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
        Schema::create('indent_approvals', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('indent_id');
            $table->unsignedBigInteger('approver_id');
            $table->enum('level', ['L1', 'L2', 'L3']);
            $table->string('action'); // Approved, Rejected, Sent Back
            $table->text('comments')->nullable();
            $table->timestamps();
            
            $table->foreign('indent_id')->references('id')->on('indents')->onDelete('cascade');
            $table->foreign('approver_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('indent_approvals');
    }
};
