<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Remove repair / not_working / retired from assets.operational_status enum (MySQL)
     * and normalize existing rows.
     */
    public function up(): void
    {
        $map = [
            'repair' => 'under_maintenance',
            'not_working' => 'damaged',
            'retired' => 'disposed',
        ];

        foreach ($map as $from => $to) {
            DB::table('assets')->where('operational_status', $from)->update(['operational_status' => $to]);
        }

        if (Schema::hasTable('asset_replacements')) {
            foreach ($map as $from => $to) {
                DB::table('asset_replacements')->where('old_operational_status', $from)->update(['old_operational_status' => $to]);
                DB::table('asset_replacements')->where('new_operational_status', $from)->update(['new_operational_status' => $to]);
            }
        }

        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        // MySQL re-validates all rows on ENUM change; fix zero/invalid timestamps first.
        DB::statement("
            UPDATE assets
            SET created_at = COALESCE(NULLIF(created_at, '0000-00-00 00:00:00'), NOW()),
                updated_at = COALESCE(NULLIF(updated_at, '0000-00-00 00:00:00'), NOW())
            WHERE created_at IS NULL
               OR created_at < '1970-01-02'
               OR updated_at IS NULL
               OR updated_at < '1970-01-02'
        ");

        DB::statement("
            ALTER TABLE assets
            MODIFY COLUMN operational_status
            ENUM('working','under_maintenance','damaged','disposed')
            NULL DEFAULT 'working'
        ");
    }

    public function down(): void
    {
        if (Schema::getConnection()->getDriverName() !== 'mysql') {
            return;
        }

        DB::statement("
            ALTER TABLE assets
            MODIFY COLUMN operational_status
            ENUM('working','under_maintenance','repair','damaged','not_working','retired','disposed')
            NULL DEFAULT 'working'
        ");
    }
};
