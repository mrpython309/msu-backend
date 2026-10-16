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

            // ✅ Drop index ONLY if it exists
            $indexes = DB::select("SHOW INDEX FROM asset_assignments");

            $indexNames = collect($indexes)->pluck('Key_name')->toArray();

            if (in_array('asset_assignments_assigned_to_type_assigned_to_id_index', $indexNames)) {
                DB::statement("ALTER TABLE asset_assignments DROP INDEX asset_assignments_assigned_to_type_assigned_to_id_index");
            }

            // Drop columns safely
            if (Schema::hasColumn('asset_assignments', 'assigned_to_type')) {
                $table->dropColumn(['assigned_to_type', 'assigned_to_id']);
            }

            $table->unsignedBigInteger('from_branch')->nullable()->after('asset_id');
            $table->unsignedBigInteger('from_location')->nullable()->after('from_branch');
            $table->unsignedBigInteger('to_branch')->after('from_location');
            $table->unsignedBigInteger('to_location')->nullable()->after('to_branch');
            $table->enum('transfer_status', ['in_transit', 'accepted', 'rejected'])->default('in_transit')->after('to_location');
            $table->dateTime('accepted_at')->nullable()->after('transfer_status');
            $table->dateTime('rejected_at')->nullable()->after('accepted_at');
            $table->unsignedBigInteger('accepted_by')->nullable()->after('rejected_at');
            $table->unsignedBigInteger('rejected_by')->nullable()->after('accepted_by');
            $table->text('accepted_notes')->nullable()->after('rejected_by');
            $table->text('rejected_notes')->nullable()->after('accepted_notes');

                });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asset_assignments', function (Blueprint $table) {
            $table->enum('assigned_to_type', ['branch', 'user'])->after('asset_id');
            $table->unsignedBigInteger('assigned_to_id')->after('assigned_to_type');
            $table->dropColumn(['from_branch', 'from_location', 'to_branch', 'to_location', 'transfer_status', 'accepted_at', 'rejected_at', 'accepted_by', 'rejected_by', 'accepted_notes', 'rejected_notes']);
            $table->index(['assigned_to_type', 'assigned_to_id'], 'idx_assigned_to');
        });
    }
};
