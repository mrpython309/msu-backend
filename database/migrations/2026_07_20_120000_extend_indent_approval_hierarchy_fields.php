<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('indents', function (Blueprint $table) {
            if (! Schema::hasColumn('indents', 'from_store_id')) {
                $table->foreignId('from_store_id')->nullable()->after('department_id')
                    ->constrained('departments')->nullOnDelete();
            }
            if (! Schema::hasColumn('indents', 'item_category_id')) {
                $table->foreignId('item_category_id')->nullable()->after('from_store_id')
                    ->constrained('asset_categories')->nullOnDelete();
            }
            if (! Schema::hasColumn('indents', 'requested_at')) {
                $table->timestamp('requested_at')->nullable()->after('request_date');
            }
        });

        DB::table('indents')->whereNull('requested_at')->orderBy('id')->chunkById(200, function ($rows) {
            foreach ($rows as $row) {
                $at = $row->submitted_at
                    ?? $row->created_at
                    ?? ($row->request_date ? $row->request_date.' 00:00:00' : null);

                if ($at) {
                    DB::table('indents')->where('id', $row->id)->update(['requested_at' => $at]);
                }
            }
        });

        Schema::table('approval_template_steps', function (Blueprint $table) {
            if (! Schema::hasColumn('approval_template_steps', 'assignee_type')) {
                $table->string('assignee_type', 20)->default('user')->after('sequence');
            }
            if (! Schema::hasColumn('approval_template_steps', 'assignee_role')) {
                $table->string('assignee_role')->nullable()->after('approver_user_id');
            }
            if (! Schema::hasColumn('approval_template_steps', 'assignee_permission')) {
                $table->string('assignee_permission')->nullable()->after('assignee_role');
            }
        });

        $this->makeUserIdNullable('approval_template_steps');

        Schema::table('indent_approval_steps', function (Blueprint $table) {
            if (! Schema::hasColumn('indent_approval_steps', 'assignee_type')) {
                $table->string('assignee_type', 20)->default('user')->after('sequence');
            }
            if (! Schema::hasColumn('indent_approval_steps', 'assignee_role')) {
                $table->string('assignee_role')->nullable()->after('approver_user_id');
            }
            if (! Schema::hasColumn('indent_approval_steps', 'assignee_permission')) {
                $table->string('assignee_permission')->nullable()->after('assignee_role');
            }
            if (! Schema::hasColumn('indent_approval_steps', 'assignee_label')) {
                $table->string('assignee_label')->nullable()->after('assignee_permission');
            }
        });

        $this->makeUserIdNullable('indent_approval_steps');
    }

    public function down(): void
    {
        Schema::table('indent_approval_steps', function (Blueprint $table) {
            foreach (['assignee_type', 'assignee_role', 'assignee_permission', 'assignee_label'] as $col) {
                if (Schema::hasColumn('indent_approval_steps', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('approval_template_steps', function (Blueprint $table) {
            foreach (['assignee_type', 'assignee_role', 'assignee_permission'] as $col) {
                if (Schema::hasColumn('approval_template_steps', $col)) {
                    $table->dropColumn($col);
                }
            }
        });

        Schema::table('indents', function (Blueprint $table) {
            if (Schema::hasColumn('indents', 'item_category_id')) {
                $table->dropConstrainedForeignId('item_category_id');
            }
            if (Schema::hasColumn('indents', 'from_store_id')) {
                $table->dropConstrainedForeignId('from_store_id');
            }
            if (Schema::hasColumn('indents', 'requested_at')) {
                $table->dropColumn('requested_at');
            }
        });
    }

    private function makeUserIdNullable(string $table): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver !== 'mysql') {
            return;
        }

        $fk = DB::selectOne(
            'SELECT CONSTRAINT_NAME AS name FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL
             LIMIT 1',
            [$table, 'approver_user_id']
        );

        if ($fk?->name) {
            DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$fk->name}`");
        }

        DB::statement("ALTER TABLE `{$table}` MODIFY `approver_user_id` BIGINT UNSIGNED NULL");
        DB::statement(
            "ALTER TABLE `{$table}` ADD CONSTRAINT `{$table}_approver_user_id_foreign`
             FOREIGN KEY (`approver_user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL"
        );
    }
};
