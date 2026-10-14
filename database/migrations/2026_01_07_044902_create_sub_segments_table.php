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
        Schema::create('sub_segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('segment_id')
                  ->constrained('segments')
                  ->cascadeOnDelete();
            $table->string('subsegment_name');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();

            $table->unique(['segment_id', 'subsegment_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_segments');
    }
};
