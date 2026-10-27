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
        if (Schema::hasTable('asset_audits')) {
            Schema::table('asset_audits', function (Blueprint $table) {
                if (! Schema::hasColumn('asset_audits', 'source')) {
                    $table->string('source', 50)->default('web')->after('location_status');
                }
                if (! Schema::hasColumn('asset_audits', 'company_id')) {
                    $table->unsignedBigInteger('company_id')->nullable()->after('source');
                    $table->foreign('company_id')->references('id')->on('companies')->nullOnDelete();
                }
                if (! Schema::hasColumn('asset_audits', 'branch_id')) {
                    $table->unsignedBigInteger('branch_id')->nullable()->after('company_id');
                    $table->foreign('branch_id')->references('id')->on('branches')->nullOnDelete();
                }
                if (! Schema::hasColumn('asset_audits', 'location_id')) {
                    $table->unsignedBigInteger('location_id')->nullable()->after('branch_id');
                    $table->foreign('location_id')->references('id')->on('branch_locations')->nullOnDelete();
                }
                if (! Schema::hasColumn('asset_audits', 'latitude')) {
                    $table->decimal('latitude', 10, 8)->nullable()->after('location_id');
                }
                if (! Schema::hasColumn('asset_audits', 'longitude')) {
                    $table->decimal('longitude', 11, 8)->nullable()->after('latitude');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('asset_audits')) {
            Schema::table('asset_audits', function (Blueprint $table) {
                if (Schema::hasColumn('asset_audits', 'company_id')) {
                    $table->dropForeign(['company_id']);
                    $table->dropColumn('company_id');
                }
                if (Schema::hasColumn('asset_audits', 'branch_id')) {
                    $table->dropForeign(['branch_id']);
                    $table->dropColumn('branch_id');
                }
                if (Schema::hasColumn('asset_audits', 'location_id')) {
                    $table->dropForeign(['location_id']);
                    $table->dropColumn('location_id');
                }
                foreach (['source', 'latitude', 'longitude'] as $col) {
                    if (Schema::hasColumn('asset_audits', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};

