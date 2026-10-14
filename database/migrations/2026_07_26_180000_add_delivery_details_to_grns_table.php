<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('grns', function (Blueprint $table) {
            if (! Schema::hasColumn('grns', 'delivered_by')) {
                $table->string('delivered_by')->nullable()->after('received_by');
            }
            if (! Schema::hasColumn('grns', 'delivered_by_phone')) {
                $table->string('delivered_by_phone')->nullable()->after('delivered_by');
            }
            if (! Schema::hasColumn('grns', 'vehicle_no')) {
                $table->string('vehicle_no')->nullable()->after('delivered_by_phone');
            }
            if (! Schema::hasColumn('grns', 'received_by_phone')) {
                $table->string('received_by_phone')->nullable()->after('received_by');
            }
        });
    }

    public function down(): void
    {
        Schema::table('grns', function (Blueprint $table) {
            $table->dropColumn([
                'delivered_by',
                'delivered_by_phone',
                'vehicle_no',
                'received_by_phone',
            ]);
        });
    }
};
