<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('msu_item_masters', function (Blueprint $table) {
            $table->foreignId('unit_id')->nullable()->after('expense_type')->constrained('units')->nullOnDelete();
        });

        // Seed units from existing free-text uom values and link items
        $uoms = DB::table('msu_item_masters')
            ->whereNotNull('uom')
            ->where('uom', '!=', '')
            ->distinct()
            ->pluck('uom');

        foreach ($uoms as $uom) {
            $unitName = trim((string) $uom);
            if ($unitName === '') {
                continue;
            }

            $unitId = DB::table('units')->where('unit_name', $unitName)->value('id');
            if (! $unitId) {
                $unitId = DB::table('units')->insertGetId([
                    'unit_name' => $unitName,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            DB::table('msu_item_masters')
                ->where('uom', $uom)
                ->update(['unit_id' => $unitId]);
        }
    }

    public function down(): void
    {
        Schema::table('msu_item_masters', function (Blueprint $table) {
            $table->dropConstrainedForeignId('unit_id');
        });
    }
};
