<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('task_reports')) {
            return;
        }

        Schema::create('task_reports', function (Blueprint $table): void {
            $table->id();
            $table->unsignedBigInteger('recurring_task_id')->nullable()->index();
            $table->unsignedBigInteger('branch_id')->nullable()->index();
            $table->date('occurrence_date')->nullable()->index();
            $table->string('status')->nullable();
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->dateTime('completed_at')->nullable();
            $table->text('comment')->nullable();
            $table->string('attachment_path')->nullable();
            $table->json('attachment_paths')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_reports');
    }
};
