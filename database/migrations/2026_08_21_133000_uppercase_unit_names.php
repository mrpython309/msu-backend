<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('units')->orderBy('id')->get(['id', 'unit_name'])->each(function ($row) {
            $name = strtoupper(preg_replace('/[^A-Za-z]/', '', (string) $row->unit_name) ?? '');
            if ($name !== '' && $name !== $row->unit_name) {
                DB::table('units')->where('id', $row->id)->update(['unit_name' => $name]);
            }
        });
    }

    public function down(): void
    {
        //
    }
};
