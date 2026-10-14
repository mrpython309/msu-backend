<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('po_approval_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('po_id')->constrained('purchase_orders')->cascadeOnDelete();
            $table->foreignId('template_step_id')->nullable()->constrained('approval_template_steps')->nullOnDelete();
            $table->unsignedInteger('sequence');
            $table->string('assignee_type')->default('user'); // user | role | permission
            $table->foreignId('approver_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('assignee_role')->nullable();
            $table->string('assignee_permission')->nullable();
            $table->string('assignee_label')->nullable();
            $table->boolean('is_mandatory')->default(true);
            $table->string('status')->default('Pending'); // Pending | Approved | Rejected | Sent Back | Skipped
            $table->foreignId('acted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('remarks')->nullable();
            $table->timestamp('acted_at')->nullable();
            $table->timestamps();

            $table->unique(['po_id', 'sequence'], 'po_approval_steps_seq_unique');
        });

        Schema::table('purchase_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_orders', 'current_sequence')) {
                $table->unsignedInteger('current_sequence')->nullable()->after('approved_at');
            }
            if (! Schema::hasColumn('purchase_orders', 'assigned_approver_id')) {
                $table->foreignId('assigned_approver_id')->nullable()->after('current_sequence')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('purchase_orders', 'approval_template_id')) {
                $table->foreignId('approval_template_id')->nullable()->after('assigned_approver_id')->constrained('approval_templates')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            foreach (['current_sequence', 'assigned_approver_id', 'approval_template_id'] as $col) {
                if (Schema::hasColumn('purchase_orders', $col)) {
                    if (in_array($col, ['assigned_approver_id', 'approval_template_id'])) {
                        $table->dropConstrainedForeignId($col);
                    } else {
                        $table->dropColumn($col);
                    }
                }
            }
        });

        Schema::dropIfExists('po_approval_steps');
    }
};
