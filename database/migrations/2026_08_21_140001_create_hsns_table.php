<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hsns', function (Blueprint $table) {
            $table->id();
            $table->string('hsn_code', 20)->unique();
            $table->string('description');
            $table->decimal('tax_rate', 8, 2)->default(0);
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hsns');
    }
};
