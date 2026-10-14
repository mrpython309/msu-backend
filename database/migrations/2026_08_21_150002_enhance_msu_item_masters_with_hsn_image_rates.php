<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('msu_item_masters', function (Blueprint $table) {
            $table->foreignId('hsn_id')->nullable()->after('unit_id')
                ->constrained('hsns')->nullOnDelete();
            $table->string('image')->nullable()->after('hsn_id');
        });

        Schema::create('msu_item_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('msu_item_master_id')->constrained('msu_item_masters')->cascadeOnDelete();
            $table->decimal('rate', 15, 2)->default(0);
            $table->decimal('tax', 8, 2)->default(0);
            $table->date('effective_from');
            $table->timestamps();

            $table->index(['msu_item_master_id', 'effective_from']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('msu_item_rates');

        Schema::table('msu_item_masters', function (Blueprint $table) {
            $table->dropConstrainedForeignId('hsn_id');
            $table->dropColumn('image');
        });
    }
};
