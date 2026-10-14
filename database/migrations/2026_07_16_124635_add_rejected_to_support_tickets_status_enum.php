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
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->enum('status', [
                'open',
                'accepted',
                'work_in_progress',
                'hold',
                'in_purchase',
                'completed',
                'closed',
                'reopen',
                'rejected'
            ])->default('open')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->enum('status', [
                'open',
                'accepted',
                'work_in_progress',
                'hold',
                'in_purchase',
                'completed',
                'closed',
                'reopen'
            ])->default('open')->change();
        });
    }
};
