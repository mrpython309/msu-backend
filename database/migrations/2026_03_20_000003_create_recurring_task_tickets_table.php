<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('recurring_task_tickets')) {
            Schema::create('recurring_task_tickets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('recurring_task_id')->constrained('recurring_tasks')->cascadeOnDelete();
                $table->date('occurrence_date');
                $table->timestamp('starts_at')->nullable();
                $table->timestamp('ends_at')->nullable();
                $table->string('status', 32)->default('pending');
                $table->timestamp('completed_at')->nullable();
                $table->text('comment')->nullable();
                $table->text('attachment_paths')->nullable();
                $table->text('completed_branch_ids')->nullable();
                $table->text('branch_completions')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_task_tickets');
    }
};
