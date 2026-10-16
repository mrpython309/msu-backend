<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('product_categories')->whereNotNull('category_code')->orderBy('id')->get(['id', 'category_code'])
            ->each(function ($row) {
                DB::table('product_categories')->where('id', $row->id)->update([
                    'category_code' => strtoupper((string) $row->category_code),
                ]);
            });

        DB::table('msu_item_masters')->orderBy('id')->get(['id', 'item_code'])
            ->each(function ($row) {
                if ($row->item_code) {
                    DB::table('msu_item_masters')->where('id', $row->id)->update([
                        'item_code' => strtoupper((string) $row->item_code),
                    ]);
                }
            });
    }

    public function down(): void
    {
        // Irreversible data normalization
    }
};
