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
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            // BASIC DETAILS
            $table->string('emp_code')->unique();
            $table->string('mile_id');
            $table->string('employee_name');
            $table->integer('company_id');
            $table->enum('is_left', ['yes', 'no'])->default('no');
            $table->enum('is_user', ['yes', 'no'])->default('no');

            // FOREIGN KEYS
            $table->foreignId('designation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('segment_id')->constrained()->cascadeOnDelete();

            // MULTI SELECT (JSON)
            $table->json('department_id')->nullable();
            $table->json('dealer_location_id');
            $table->json('subsegment_id');
            $table->json('reporting_to');

            // CONTACT
            $table->string('mobile_number', 50);
            $table->string('email')->unique();

            // STATUS
            $table->date('date_of_joining');
            $table->enum('employee_status', ['active', 'inactive']);
            $table->date('left_date')->nullable();
            $table->integer('role_id');

            // USER LINK (optional)
            $table->foreignId('user_id')->nullable()
                ->constrained()->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};
