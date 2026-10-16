<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->string('company_code', 20)->nullable()->unique()->after('id');
        });

        $vendors = DB::table('vendors')->orderBy('id')->get(['id']);
        $sequence = 1;
        foreach ($vendors as $vendor) {
            DB::table('vendors')
                ->where('id', $vendor->id)
                ->update([
                    'company_code' => 'A'.str_pad((string) $sequence, 4, '0', STR_PAD_LEFT),
                ]);
            $sequence++;
        }
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropUnique(['company_code']);
            $table->dropColumn('company_code');
        });
    }
};
