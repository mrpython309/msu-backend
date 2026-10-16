<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('asset_maintenance', 'support_ticket_id')) {
            Schema::table('asset_maintenance', function (Blueprint $table) {
                $table->dropConstrainedForeignId('support_ticket_id');
            });
        }
    }

    public function down(): void
    {
        if (
            Schema::hasTable('support_tickets')
            && ! Schema::hasColumn('asset_maintenance', 'support_ticket_id')
        ) {
            Schema::table('asset_maintenance', function (Blueprint $table) {
                $table->foreignId('support_ticket_id')
                    ->nullable()
                    ->constrained('support_tickets')
                    ->cascadeOnDelete();
            });
        }
    }
};
