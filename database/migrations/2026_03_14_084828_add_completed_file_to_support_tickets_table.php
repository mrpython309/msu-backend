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
            $table->string('completed_file')->nullable();
            $table->text('completed_ticket_commands')->nullable();
            $table->integer('completed_time')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropColumn('completed_file');
            $table->dropColumn('completed_ticket_commands');
            $table->dropColumn('completed_time');
        });
    }
};
