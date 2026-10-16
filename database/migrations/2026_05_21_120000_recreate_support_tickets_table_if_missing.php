<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Recreate support_tickets when the table was dropped but migrations remain recorded.
     */
    public function up(): void
    {
        if (Schema::hasTable('support_tickets')) {
            return;
        }

        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->string('subject');
            $table->unsignedBigInteger('company_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->enum('priority', ['low', 'high', 'urgent', 'normal'])->default('normal');
            $table->date('due_date')->nullable();
            $table->unsignedBigInteger('asset_id')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', [
                'open',
                'accepted',
                'work_in_progress',
                'hold',
                'in_purchase',
                'completed',
                'closed',
                'reopen',
            ])->default('open');
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->string('ticket_file')->nullable();
            $table->string('filename')->nullable();
            $table->unsignedBigInteger('vendor_id')->nullable();
            $table->decimal('total_cost', 10, 2)->nullable();
            $table->text('close_ticket_commands')->nullable();
            $table->string('completed_file')->nullable();
            $table->text('completed_ticket_commands')->nullable();
            $table->integer('completed_time')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('company_id')->references('id')->on('companies')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->foreign('asset_id')->references('id')->on('assets')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        // Intentionally no-op: do not drop a table that may have been recreated with data.
    }
};
