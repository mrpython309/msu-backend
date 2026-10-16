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
        Schema::create('file_storage', function (Blueprint $table) {
            $table->id();
             $table->string('path');
            $table->string('filename');
            $table->string('type',50)->nullable();
            $table->integer('size');
            $table->enum('storage_location',['local','aws_s3']); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('file_storage');
    }
};
