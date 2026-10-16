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
        // Change material_requests status
        // Since MySQL enum changes can be tricky, it's often safer to change to string, or add the new enum values, then update, then change back.
        // Or simply define the new enum with all possible statuses past and present, or just the new ones.
        // We'll change it to string (varchar 50) for flexibility and safety across DB types.
        Schema::table('material_requests', function (Blueprint $table) {
            $table->string('status', 50)->default('created')->change();
        });

        // Map existing data for MR
        DB::table('material_requests')->where('status', 'draft')->update(['status' => 'created']);
        DB::table('material_requests')->where('status', 'submitted')->update(['status' => 'approved']);

        // Change material_issues status
        Schema::table('material_issues', function (Blueprint $table) {
            $table->string('status', 50)->default('approved')->change();
        });

        // Map existing data for MI
        DB::table('material_issues')->where('status', 'draft')->update(['status' => 'created']);
        DB::table('material_issues')->where('status', 'submitted')->update(['status' => 'approved']);
    }

    public function down(): void
    {
        DB::table('material_requests')->where('status', 'created')->update(['status' => 'draft']);
        DB::table('material_requests')->where('status', 'approved')->update(['status' => 'submitted']);
        
        Schema::table('material_requests', function (Blueprint $table) {
            $table->enum('status', ['draft', 'submitted', 'partially_issued', 'fully_issued'])->default('draft')->change();
        });

        DB::table('material_issues')->where('status', 'created')->update(['status' => 'draft']);
        DB::table('material_issues')->where('status', 'approved')->update(['status' => 'submitted']);

        Schema::table('material_issues', function (Blueprint $table) {
            $table->enum('status', ['draft', 'submitted'])->default('submitted')->change();
        });
    }
};
