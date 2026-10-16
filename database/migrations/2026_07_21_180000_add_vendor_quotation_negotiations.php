<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_quotations', function (Blueprint $table) {
            $table->decimal('negotiated_total_cost', 15, 2)->nullable()->after('total_cost');
            $table->string('negotiation_status', 50)->nullable()->after('negotiated_total_cost');
            $table->text('buyer_remarks')->nullable()->after('negotiation_status');
            $table->text('vendor_response')->nullable()->after('buyer_remarks');
            $table->timestamp('negotiations_locked_at')->nullable()->after('vendor_response');
        });

        Schema::create('quotation_negotiation_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_quotation_id')->constrained('vendor_quotations')->cascadeOnDelete();
            $table->unsignedInteger('round');
            $table->decimal('old_price', 15, 2);
            $table->decimal('new_price', 15, 2);
            $table->text('buyer_remarks')->nullable();
            $table->text('vendor_response')->nullable();
            $table->string('status', 50)->nullable();
            $table->foreignId('negotiated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('negotiated_at')->nullable();
            $table->timestamps();

            $table->index(['vendor_quotation_id', 'round']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quotation_negotiation_histories');

        Schema::table('vendor_quotations', function (Blueprint $table) {
            $table->dropColumn([
                'negotiated_total_cost',
                'negotiation_status',
                'buyer_remarks',
                'vendor_response',
                'negotiations_locked_at',
            ]);
        });
    }
};
