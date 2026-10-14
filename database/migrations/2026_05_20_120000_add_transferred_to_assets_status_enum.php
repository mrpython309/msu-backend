<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add `transferred` and migrate legacy `in_transit` rows so they match assignment "Transferred" state.
     */
    public function up(): void
    {
        $conn = Schema::getConnection();
        $isMysql = $conn->getDriverName() === 'mysql';

        if ($isMysql) {
            $conn->statement('SET @___migr_assets_sql_mode := @@SESSION.sql_mode');
            $conn->statement("SET SESSION sql_mode = REPLACE(REPLACE(REPLACE(@@SESSION.sql_mode,'NO_ZERO_DATE',''),'NO_ZERO_IN_DATE',''),'STRICT_TRANS_TABLES','')");
        }

        try {
            // MySQL may reject MODIFY TABLE if any row has invalid dates (e.g. legacy 0000-00-00).
            if (Schema::hasColumn('assets', 'warrenty_expires')) {
                DB::statement("UPDATE assets SET warrenty_expires = NULL WHERE warrenty_expires = '0000-00-00' OR CAST(warrenty_expires AS CHAR) = '0000-00-00'");
            }

            if (Schema::hasColumn('assets', 'purchase_date')) {
                DB::statement("UPDATE assets SET purchase_date = NULL WHERE purchase_date = '0000-00-00' OR CAST(purchase_date AS CHAR) = '0000-00-00'");
            }

            DB::statement("
            ALTER TABLE assets
            MODIFY COLUMN status
            ENUM('available','assigned','in_transit','transferred','under_maintenance','disposed') NULL
        ");

            DB::table('assets')->where('status', 'in_transit')->update(['status' => 'transferred']);
        } finally {
            if ($isMysql) {
                $conn->statement('SET SESSION sql_mode = @___migr_assets_sql_mode');
            }
        }
    }

    public function down(): void
    {
        $conn = Schema::getConnection();
        $isMysql = $conn->getDriverName() === 'mysql';

        if ($isMysql) {
            $conn->statement('SET @___migr_assets_sql_mode := @@SESSION.sql_mode');
            $conn->statement("SET SESSION sql_mode = REPLACE(REPLACE(REPLACE(@@SESSION.sql_mode,'NO_ZERO_DATE',''),'NO_ZERO_IN_DATE',''),'STRICT_TRANS_TABLES','')");
        }

        try {
            if (Schema::hasColumn('assets', 'warrenty_expires')) {
                DB::statement("UPDATE assets SET warrenty_expires = NULL WHERE warrenty_expires = '0000-00-00' OR CAST(warrenty_expires AS CHAR) = '0000-00-00'");
            }

            if (Schema::hasColumn('assets', 'purchase_date')) {
                DB::statement("UPDATE assets SET purchase_date = NULL WHERE purchase_date = '0000-00-00' OR CAST(purchase_date AS CHAR) = '0000-00-00'");
            }

            DB::table('assets')->where('status', 'transferred')->update(['status' => 'in_transit']);

            DB::statement("
            ALTER TABLE assets
            MODIFY COLUMN status
            ENUM('available','assigned','in_transit','under_maintenance','disposed') NULL
        ");
        } finally {
            if ($isMysql) {
                $conn->statement('SET SESSION sql_mode = @___migr_assets_sql_mode');
            }
        }
    }
};
