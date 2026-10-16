<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('recurring_task_occurrence_summary')) {
            Schema::create('recurring_task_occurrence_summary', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('recurring_task_id');
                $table->date('occurrence_date');
                $table->unsignedInteger('total_branch_count')->default(0);
                $table->unsignedInteger('completed_branch_count')->default(0);
                $table->unsignedInteger('pending_branch_count')->default(0);
                $table->unsignedInteger('overdue_branch_count')->default(0);
                $table->string('status_snapshot', 32)->nullable();
                $table->timestamp('generated_at')->nullable();
                $table->timestamps();

                $table->unique(
                    ['recurring_task_id', 'occurrence_date'],
                    'rt_occurrence_summary_task_date_unique'
                );
                $table->index(
                    ['recurring_task_id', 'occurrence_date'],
                    'rt_occurrence_summary_task_date_idx'
                );
            });
        }

        if (! Schema::hasTable('recurring_task_branch_progress')) {
            Schema::create('recurring_task_branch_progress', function (Blueprint $table): void {
                $table->id();
                $table->unsignedBigInteger('recurring_task_id');
                $table->unsignedBigInteger('branch_id');
                $table->string('latest_status', 32)->nullable();
                $table->timestamp('latest_completed_at')->nullable();
                $table->unsignedInteger('collaborator_count')->default(0);
                $table->date('current_occurrence_date')->nullable();
                $table->timestamp('generated_at')->nullable();
                $table->timestamps();

                $table->unique(
                    ['recurring_task_id', 'branch_id'],
                    'rt_branch_progress_task_branch_unique'
                );
                $table->index(
                    ['recurring_task_id', 'branch_id'],
                    'rt_branch_progress_task_branch_idx'
                );
                $table->index(
                    ['recurring_task_id', 'current_occurrence_date'],
                    'rt_branch_progress_task_date_idx'
                );
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('recurring_task_branch_progress');
        Schema::dropIfExists('recurring_task_occurrence_summary');
    }
};

