<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('purchase_requisitions', function (Blueprint $table) {
            if (! Schema::hasColumn('purchase_requisitions', 'created_by')) {
                $table->foreignId('created_by')->nullable()->after('purchase_officer_id')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('purchase_requisitions', 'closed_by')) {
                $table->foreignId('closed_by')->nullable()->after('remarks')->constrained('users')->nullOnDelete();
            }
            if (! Schema::hasColumn('purchase_requisitions', 'closed_at')) {
                $table->timestamp('closed_at')->nullable()->after('closed_by');
            }
            if (! Schema::hasColumn('purchase_requisitions', 'close_reason')) {
                $table->text('close_reason')->nullable()->after('closed_at');
            }
            if (! Schema::hasColumn('purchase_requisitions', 'rejected_reason')) {
                $table->text('rejected_reason')->nullable()->after('close_reason');
            }
            if (! Schema::hasColumn('purchase_requisitions', 'processed_at')) {
                $table->timestamp('processed_at')->nullable()->after('rejected_reason');
            }
        });
    }

    public function down(): void
    {
        Schema::table('purchase_requisitions', function (Blueprint $table) {
            foreach (['created_by', 'closed_by'] as $fk) {
                if (Schema::hasColumn('purchase_requisitions', $fk)) {
                    $table->dropConstrainedForeignId($fk);
                }
            }
            foreach (['closed_at', 'close_reason', 'rejected_reason', 'processed_at'] as $col) {
                if (Schema::hasColumn('purchase_requisitions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
