<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('approval_workflows', function (Blueprint $table) {
            $table->id();
            $table->string('workflow_name');
            $table->string('module')->default('Indent')->index();
            $table->foreignId('category_id')->nullable()->constrained('asset_categories')->nullOnDelete();
            $table->unsignedInteger('sequence');
            $table->string('permission_name');
            $table->boolean('is_mandatory')->default(true);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['module', 'category_id', 'sequence'], 'approval_workflows_lookup_index');
        });

        Schema::create('approval_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indent_id')->constrained('indents')->cascadeOnDelete();
            $table->foreignId('workflow_id')->nullable()->constrained('approval_workflows')->nullOnDelete();
            $table->unsignedInteger('sequence')->nullable();
            $table->foreignId('approver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->text('remarks')->nullable();
            $table->timestamp('action_date')->nullable();
            $table->timestamps();

            $table->index(['indent_id', 'action']);
        });

        Schema::table('indents', function (Blueprint $table) {
            if (! Schema::hasColumn('indents', 'current_sequence')) {
                $table->unsignedInteger('current_sequence')->nullable()->after('current_level');
            }
            if (! Schema::hasColumn('indents', 'approval_status')) {
                $table->string('approval_status')->nullable()->after('current_sequence')->index();
            }
            if (! Schema::hasColumn('indents', 'assigned_permission')) {
                $table->string('assigned_permission')->nullable()->after('approval_status')->index();
            }
            if (! Schema::hasColumn('indents', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('assigned_permission');
            }
        });
    }

    public function down(): void
    {
        Schema::table('indents', function (Blueprint $table) {
            foreach (['current_sequence', 'approval_status', 'assigned_permission', 'approved_at'] as $column) {
                if (Schema::hasColumn('indents', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::dropIfExists('approval_history');
        Schema::dropIfExists('approval_workflows');
    }
};
