<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('category_id')->nullable()->constrained('asset_categories')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['category_id']);
            $table->index(['is_active']);
        });

        Schema::create('approval_template_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('approval_template_id')->constrained('approval_templates')->cascadeOnDelete();
            $table->unsignedInteger('sequence');
            $table->foreignId('approver_user_id')->constrained('users')->restrictOnDelete();
            $table->boolean('is_mandatory')->default(true);
            $table->timestamps();

            $table->unique(['approval_template_id', 'sequence'], 'approval_template_steps_seq_unique');
            $table->index(['approver_user_id']);
        });

        // Snapshot of steps cloned onto an indent at submit time.
        Schema::create('indent_approval_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indent_id')->constrained('indents')->cascadeOnDelete();
            $table->foreignId('template_step_id')->nullable()->constrained('approval_template_steps')->nullOnDelete();
            $table->unsignedInteger('sequence');
            $table->foreignId('approver_user_id')->constrained('users')->restrictOnDelete();
            $table->boolean('is_mandatory')->default(true);
            $table->string('status')->default('Pending'); // Pending, Approved, Rejected, Skipped, Sent Back
            $table->foreignId('acted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamp('acted_at')->nullable();
            $table->timestamps();

            $table->unique(['indent_id', 'sequence'], 'indent_approval_steps_seq_unique');
            $table->index(['approver_user_id', 'status']);
        });

        Schema::table('indents', function (Blueprint $table) {
            if (! Schema::hasColumn('indents', 'approval_template_id')) {
                $table->foreignId('approval_template_id')->nullable()->after('department_id')
                    ->constrained('approval_templates')->nullOnDelete();
            }
            if (! Schema::hasColumn('indents', 'assigned_approver_id')) {
                $table->foreignId('assigned_approver_id')->nullable()->after('assigned_permission')
                    ->constrained('users')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('indents', function (Blueprint $table) {
            if (Schema::hasColumn('indents', 'assigned_approver_id')) {
                $table->dropConstrainedForeignId('assigned_approver_id');
            }
            if (Schema::hasColumn('indents', 'approval_template_id')) {
                $table->dropConstrainedForeignId('approval_template_id');
            }
        });

        Schema::dropIfExists('indent_approval_steps');
        Schema::dropIfExists('approval_template_steps');
        Schema::dropIfExists('approval_templates');
    }
};
