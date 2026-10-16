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
        Schema::table('users', function (Blueprint $table) {

            // BASIC EMPLOYEE INFO
            $table->string('emp_code')->unique()->nullable()->after('id');
            $table->string('mile_id')->nullable()->after('emp_code');
            $table->boolean('reporting_status')->default(0);
            $table->unsignedBigInteger('company_id')->nullable();

            // FLAGS
            $table->enum('is_left', ['yes', 'no'])->default('no')->after('company_id');
            $table->enum('is_user', ['yes', 'no'])->after('is_left');

            // FOREIGN KEYS
            $table->foreignId('designation_id')->nullable()
                ->after('is_user')
                ->constrained()
                ->nullOnDelete();

            $table->foreignId('segment_id')->nullable()
                ->after('designation_id')
                ->constrained()
                ->nullOnDelete();

            // MULTI SELECT (JSON)
            $table->json('department_id')->nullable()->after('segment_id');
            $table->json('dealer_location_id')->nullable()->after('department_id');
            $table->json('subsegment_id')->nullable()->after('dealer_location_id');
            $table->json('reporting_to')->nullable()->after('subsegment_id');

            // CONTACT
            $table->string('mobile_number', 50)->nullable()->after('reporting_to');

            // STATUS
            $table->date('date_of_joining')->nullable()->after('mobile_number');
            $table->enum('employee_status', ['active', 'inactive'])
                ->default('active')
                ->after('date_of_joining');

            $table->date('left_date')->nullable()->after('employee_status');

            // ROLE
            $table->unsignedBigInteger('role_id')->nullable()->after('left_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {

            // DROP FOREIGN KEYS FIRST
            $table->dropForeign(['designation_id']);
            $table->dropForeign(['segment_id']);

            // DROP COLUMNS
            $table->dropColumn([
                'emp_code',
                'mile_id',
                'company_id',
                'is_left',
                'is_user',
                'designation_id',
                'segment_id',
                'department_id',
                'dealer_location_id',
                'subsegment_id',
                'reporting_to',
                'mobile_number',
                'date_of_joining',
                'employee_status',
                'left_date',
                'role_id',
            ]);
        });
    }

};
