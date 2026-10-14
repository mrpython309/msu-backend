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
        Schema::table('support_ticket_assignments', function (Blueprint $table) {
           $table->boolean('is_active')->default(true)->after('assigned_at');
           $table->timestamp('released_at')->nullable()->after('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('support_ticket_assignments', function (Blueprint $table) {
            $table->dropColumn('is_active');
            $table->dropColumn('released_at');
        });
    }
};
