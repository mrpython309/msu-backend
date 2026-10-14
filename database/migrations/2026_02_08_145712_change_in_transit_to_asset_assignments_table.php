<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('asset_assignments', function (Blueprint $table) {
              DB::statement("
            ALTER TABLE asset_assignments 
            MODIFY transfer_status 
            ENUM('in_transit', 'accepted', 'rejected', 'return_accepted','return')
            DEFAULT 'in_transit'
        ");

         $table->unsignedBigInteger('returned_by')->nullable()->after('rejected_by');
         $table->dateTime('returned_at')->nullable()->after('return_accepted_by');
         $table->text('return_accepted_notes')->nullable()->after('return_accepted_at');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_assignments', function (Blueprint $table) {
            DB::statement("
            ALTER TABLE asset_assignments 
            MODIFY transfer_status 
            ENUM('in_transit', 'accepted', 'rejected', 'return_accepted')
            DEFAULT 'in_transit'
        ");

         $table->dropColumn(['returned_by', 'returned_at', 'return_accepted_notes']);
        });
    }
};
