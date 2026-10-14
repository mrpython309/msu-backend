<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_masters', function (Blueprint $table) {
            $table->id();
            $table->string('employee_name');
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->string('mobile_number', 20)->nullable();
            $table->string('email')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        Schema::table('msu_purchase_orders', function (Blueprint $table) {
            $table->foreignId('employee_master_id')->nullable()->after('branch_id')
                ->constrained('employee_masters')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('msu_purchase_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('employee_master_id');
        });

        Schema::dropIfExists('employee_masters');
    }
};
