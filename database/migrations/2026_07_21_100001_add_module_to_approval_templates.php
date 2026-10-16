<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('approval_templates', function (Blueprint $table) {
            if (! Schema::hasColumn('approval_templates', 'module')) {
                // 'indent' (default/null) | 'po'
                $table->string('module')->nullable()->default(null)->after('category_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('approval_templates', function (Blueprint $table) {
            if (Schema::hasColumn('approval_templates', 'module')) {
                $table->dropColumn('module');
            }
        });
    }
};
