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
        Schema::create('indents', function (Blueprint $table) {
            $table->id();
            $table->string('indent_no')->unique();
            $table->date('request_date');
            $table->unsignedBigInteger('department_id');
            $table->unsignedBigInteger('requested_by');
            $table->enum('priority', ['Low', 'Medium', 'High', 'Urgent'])->default('Medium');
            $table->date('required_date')->nullable();
            $table->text('purpose')->nullable();
            $table->string('status')->default('Draft');
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('restrict');
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('indents');
    }
};
