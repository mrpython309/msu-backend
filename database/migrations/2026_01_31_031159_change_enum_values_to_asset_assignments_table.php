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
            ENUM('in_transit', 'accepted', 'rejected', 'return_accepted')
            DEFAULT 'in_transit'
        ");

           $table->unsignedBigInteger('return_accepted_by')->nullable()->after('rejected_by');
           $table->dateTime('return_accepted_at')->nullable()->after('return_accepted_by');
           $table->text('return_notes')->nullable()->after('return_accepted_at');

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
            ENUM('in_transit', 'accepted', 'rejected')
            DEFAULT 'in_transit'");

             $table->dropColumn(['return_accepted_by', 'return_accepted_at', 'return_notes']);

        });

       
    }
};
