<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('support_ticket_commands', function (Blueprint $table) {
            $table->json('filenames')->nullable()->after('filename');
        });
    }

    public function down(): void
    {
        Schema::table('support_ticket_commands', function (Blueprint $table) {
            $table->dropColumn('filenames');
        });
    }
};
