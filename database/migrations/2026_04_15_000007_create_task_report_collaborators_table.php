<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('task_report_collaborators')) {
            return;
        }

        Schema::create('task_report_collaborators', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('task_report_id')->constrained('task_reports')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['task_report_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_report_collaborators');
    }
};

