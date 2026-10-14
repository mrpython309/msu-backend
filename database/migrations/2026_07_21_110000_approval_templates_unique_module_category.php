<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('approval_templates', function (Blueprint $table) {
            // FK was using the unique index; drop FK first so we can replace uniqueness.
            $table->dropForeign(['category_id']);
        });

        Schema::table('approval_templates', function (Blueprint $table) {
            $table->dropUnique('approval_templates_category_id_unique');
        });

        Schema::table('approval_templates', function (Blueprint $table) {
            // Same category can exist once for Indent (module null) and once for PO (module = po).
            $table->unique(['module', 'category_id'], 'approval_templates_module_category_unique');
            $table->foreign('category_id')
                ->references('id')
                ->on('asset_categories')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('approval_templates', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
            $table->dropUnique('approval_templates_module_category_unique');
        });

        Schema::table('approval_templates', function (Blueprint $table) {
            $table->unique('category_id', 'approval_templates_category_id_unique');
            $table->foreign('category_id')
                ->references('id')
                ->on('asset_categories')
                ->nullOnDelete();
        });
    }
};
