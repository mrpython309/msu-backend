<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('grn_items', function (Blueprint $table) {
            if (! Schema::hasColumn('grn_items', 'inspection_status')) {
                $table->string('inspection_status', 30)->default('Pending')->after('stock_after');
            }
            if (! Schema::hasColumn('grn_items', 'inspection_remarks')) {
                $table->text('inspection_remarks')->nullable()->after('inspection_status');
            }
        });

        Schema::create('quality_inspections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('grn_id')->constrained('grns')->cascadeOnDelete();
            $table->foreignId('grn_item_id')->constrained('grn_items')->cascadeOnDelete();
            $table->foreignId('inspected_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('inspected_at')->useCurrent();
            $table->string('inspection_status', 30);
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->index(['grn_id', 'grn_item_id']);
            $table->index('inspection_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('quality_inspections');

        Schema::table('grn_items', function (Blueprint $table) {
            if (Schema::hasColumn('grn_items', 'inspection_status')) {
                $table->dropColumn('inspection_status');
            }
            if (Schema::hasColumn('grn_items', 'inspection_remarks')) {
                $table->dropColumn('inspection_remarks');
            }
        });
    }
};
