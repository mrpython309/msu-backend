<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payment_terms', function (Blueprint $table) {
            $table->string('code', 50)->nullable()->after('term_name');
        });

        $used = [];
        foreach (DB::table('payment_terms')->orderBy('id')->get() as $row) {
            $base = strtoupper(Str::slug((string) $row->term_name, '_')) ?: 'TERM';
            $base = substr($base, 0, 40);
            $code = $base;
            $i = 1;
            while (isset($used[$code]) || DB::table('payment_terms')->where('code', $code)->where('id', '!=', $row->id)->exists()) {
                $code = substr($base, 0, 36).'_'.$i;
                $i++;
            }
            $used[$code] = true;
            DB::table('payment_terms')->where('id', $row->id)->update(['code' => $code]);
        }

        Schema::table('payment_terms', function (Blueprint $table) {
            $table->unique('code');
        });

        Schema::table('miscellaneous_charges', function (Blueprint $table) {
            $table->string('code', 50)->nullable()->after('charge_name');
        });

        $used = [];
        foreach (DB::table('miscellaneous_charges')->orderBy('id')->get() as $row) {
            $base = strtoupper(Str::slug((string) $row->charge_name, '_')) ?: 'MISC';
            $base = substr($base, 0, 40);
            $code = $base;
            $i = 1;
            while (isset($used[$code]) || DB::table('miscellaneous_charges')->where('code', $code)->where('id', '!=', $row->id)->exists()) {
                $code = substr($base, 0, 36).'_'.$i;
                $i++;
            }
            $used[$code] = true;
            DB::table('miscellaneous_charges')->where('id', $row->id)->update(['code' => $code]);
        }

        Schema::table('miscellaneous_charges', function (Blueprint $table) {
            $table->unique('code');
        });

        if (Schema::hasTable('msu_purchase_orders') && ! Schema::hasColumn('msu_purchase_orders', 'miscellaneous_charge_id')) {
            Schema::table('msu_purchase_orders', function (Blueprint $table) {
                $table->foreignId('miscellaneous_charge_id')
                    ->nullable()
                    ->after('payment_term_id')
                    ->constrained('miscellaneous_charges')
                    ->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('msu_purchase_orders') && Schema::hasColumn('msu_purchase_orders', 'miscellaneous_charge_id')) {
            Schema::table('msu_purchase_orders', function (Blueprint $table) {
                $table->dropConstrainedForeignId('miscellaneous_charge_id');
            });
        }

        Schema::table('miscellaneous_charges', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->dropColumn('code');
        });

        Schema::table('payment_terms', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->dropColumn('code');
        });
    }
};
