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
        Schema::create('material_requests', function (Blueprint $table) {
            $table->id();
            $table->string('mr_number')->unique();
            $table->date('request_date');
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('employee_master_id')->nullable()->constrained('employee_masters')->nullOnDelete();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->enum('status', ['draft', 'submitted', 'partially_issued', 'fully_issued'])->default('draft');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('material_request_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_request_id')->constrained('material_requests')->cascadeOnDelete();
            $table->foreignId('msu_item_master_id')->constrained('msu_item_masters')->restrictOnDelete();
            $table->decimal('requested_qty', 15, 3)->default(0);
            $table->decimal('issued_qty', 15, 3)->default(0);
            $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamps();
        });

        Schema::create('material_issues', function (Blueprint $table) {
            $table->id();
            $table->string('mi_number')->unique();
            $table->date('issue_date');
            $table->foreignId('department_id')->nullable()->constrained('departments')->nullOnDelete();
            $table->foreignId('employee_master_id')->nullable()->constrained('employee_masters')->nullOnDelete();
            $table->foreignId('material_request_id')->constrained('material_requests')->restrictOnDelete();
            $table->enum('status', ['draft', 'submitted'])->default('submitted');
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('material_issue_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('material_issue_id')->constrained('material_issues')->cascadeOnDelete();
            $table->foreignId('material_request_item_id')->constrained('material_request_items')->restrictOnDelete();
            $table->foreignId('msu_item_master_id')->constrained('msu_item_masters')->restrictOnDelete();
            $table->decimal('issue_qty', 15, 3)->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_requests_and_issues_tables');
    }
};
