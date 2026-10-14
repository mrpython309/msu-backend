<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {
            $table->string('category_code', 3)->nullable()->after('category_name');
        });

        $categories = DB::table('product_categories')->orderBy('id')->get(['id', 'category_name']);
        $used = [];

        foreach ($categories as $category) {
            $base = strtolower(substr(preg_replace('/[^A-Za-z0-9]/', '', (string) $category->category_name) ?? '', 0, 3));
            if ($base === '' || strlen($base) < 3) {
                $base = str_pad($base !== '' ? $base : 'cat', 3, 'x');
            }

            $code = $base;
            $n = 1;
            while (isset($used[$code])) {
                $suffix = (string) $n;
                $code = substr($base, 0, max(1, 3 - strlen($suffix))).$suffix;
                $n++;
            }
            $used[$code] = true;

            DB::table('product_categories')->where('id', $category->id)->update(['category_code' => $code]);
        }

        Schema::table('product_categories', function (Blueprint $table) {
            $table->unique('category_code');
        });
    }

    public function down(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {
            $table->dropUnique(['category_code']);
            $table->dropColumn('category_code');
        });
    }
};
