<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('recurring_task_tickets', function (Blueprint $table) {
            $table->timestamp('start_notification_sent_at')->nullable();
            $table->timestamp('end_notification_sent_at')->nullable();
            $table->timestamp('fallback_notification_sent_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('recurring_task_tickets', function (Blueprint $table) {
            $table->dropColumn([
                'start_notification_sent_at',
                'end_notification_sent_at',
                'fallback_notification_sent_at'
            ]);
        });
    }
};
