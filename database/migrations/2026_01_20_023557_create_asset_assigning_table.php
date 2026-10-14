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
        Schema::create('asset_assignments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_id');
            $table->foreign('asset_id')->references('id')->on('assets')->onDelete('cascade');
            $table->enum('assigned_to_type', ['branch', 'user']);
            $table->unsignedBigInteger('assigned_to_id');
            $table->date('assign_date')->nullable();
            $table->enum('status', ['assigned', 'returned','transferred'])->default('transferred');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['assigned_to_type', 'assigned_to_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asset_assignments');
    }
};
