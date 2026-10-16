<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            if (! Schema::hasColumn('assets', 'audited_by')) {
                $table->unsignedBigInteger('audited_by')->nullable()->after('custodian_id');
                $table->foreign('audited_by')->references('id')->on('users')->nullOnDelete();
            }

            if (! Schema::hasColumn('assets', 'audited_at')) {
                $table->dateTime('audited_at')->nullable()->after('audited_by');
            }

            if (! Schema::hasColumn('assets', 'audit_status')) {
                $table->string('audit_status', 30)->nullable()->after('audited_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            if (Schema::hasColumn('assets', 'audited_by')) {
                $table->dropForeign(['audited_by']);
                $table->dropColumn('audited_by');
            }

            foreach (['audited_at', 'audit_status'] as $column) {
                if (Schema::hasColumn('assets', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
