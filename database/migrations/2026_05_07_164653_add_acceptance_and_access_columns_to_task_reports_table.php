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
        Schema::table('task_reports', function (Blueprint $table) {
            if (! Schema::hasColumn('task_reports', 'accepted_by_user_id')) {
                $table->unsignedBigInteger('accepted_by_user_id')->nullable()->index();
            }
            if (! Schema::hasColumn('task_reports', 'accepted_at')) {
                $table->timestamp('accepted_at')->nullable();
            }
            if (! Schema::hasColumn('task_reports', 'completed_by_user_id')) {
                $table->unsignedBigInteger('completed_by_user_id')->nullable()->index();
            }
            if (! Schema::hasColumn('task_reports', 'collaborator_user_ids')) {
                $table->json('collaborator_user_ids')->nullable();
            }
            if (! Schema::hasColumn('task_reports', 'access_user_ids')) {
                $table->json('access_user_ids')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('task_reports', function (Blueprint $table) {
            $drop = [];
            if (Schema::hasColumn('task_reports', 'accepted_by_user_id')) {
                $drop[] = 'accepted_by_user_id';
            }
            if (Schema::hasColumn('task_reports', 'accepted_at')) {
                $drop[] = 'accepted_at';
            }
            if (Schema::hasColumn('task_reports', 'completed_by_user_id')) {
                $drop[] = 'completed_by_user_id';
            }
            if (Schema::hasColumn('task_reports', 'collaborator_user_ids')) {
                $drop[] = 'collaborator_user_ids';
            }
            if (Schema::hasColumn('task_reports', 'access_user_ids')) {
                $drop[] = 'access_user_ids';
            }
            if ($drop !== []) {
                $table->dropColumn($drop);
            }
        });
    }
};
