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
        Schema::table('support_ticket_commands', function (Blueprint $table) {

            $table->text('ticket_response')->nullable()->change();

            $table->string('voice_note')->nullable()->after('ticket_response');

            $table->integer('recording_duration')->nullable()->after('voice_note');

           
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('support_ticket_commands', function (Blueprint $table) {
             // Remove the new fields
            $table->dropColumn(['voice_note', 'recording_duration']);            
            
            $table->text('ticket_response')->nullable(false)->change();
        });
    }
};
