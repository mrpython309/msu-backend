<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('rfqs', function (Blueprint $table) {
            if (! Schema::hasColumn('rfqs', 'rfq_date')) {
                $table->date('rfq_date')->nullable()->after('rfq_no');
            }
            if (! Schema::hasColumn('rfqs', 'priority')) {
                $table->string('priority')->nullable()->after('pr_id');
            }
            if (! Schema::hasColumn('rfqs', 'expected_delivery_date')) {
                $table->date('expected_delivery_date')->nullable()->after('closes_at');
            }
            if (! Schema::hasColumn('rfqs', 'delivery_store')) {
                $table->string('delivery_store')->nullable()->after('expected_delivery_date');
            }
            if (! Schema::hasColumn('rfqs', 'currency')) {
                $table->string('currency', 10)->nullable()->after('delivery_store');
            }
            if (! Schema::hasColumn('rfqs', 'payment_terms')) {
                $table->string('payment_terms')->nullable()->after('currency');
            }
            if (! Schema::hasColumn('rfqs', 'delivery_terms')) {
                $table->string('delivery_terms')->nullable()->after('payment_terms');
            }
            if (! Schema::hasColumn('rfqs', 'warranty_required')) {
                $table->boolean('warranty_required')->default(false)->after('delivery_terms');
            }
            if (! Schema::hasColumn('rfqs', 'amc_required')) {
                $table->boolean('amc_required')->default(false)->after('warranty_required');
            }
            if (! Schema::hasColumn('rfqs', 'tax_included')) {
                $table->boolean('tax_included')->default(false)->after('amc_required');
            }
            if (! Schema::hasColumn('rfqs', 'freight_included')) {
                $table->boolean('freight_included')->default(false)->after('tax_included');
            }
            if (! Schema::hasColumn('rfqs', 'notes_to_vendor')) {
                $table->text('notes_to_vendor')->nullable()->after('notes');
            }
        });

        if (! Schema::hasTable('rfq_attachments')) {
            Schema::create('rfq_attachments', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('rfq_id');
                $table->string('original_name');
                $table->string('file_path');
                $table->string('mime_type')->nullable();
                $table->unsignedBigInteger('file_size')->nullable();
                $table->unsignedBigInteger('uploaded_by')->nullable();
                $table->timestamps();

                $table->foreign('rfq_id')->references('id')->on('rfqs')->cascadeOnDelete();
                $table->foreign('uploaded_by')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rfq_attachments');

        Schema::table('rfqs', function (Blueprint $table) {
            foreach ([
                'rfq_date',
                'priority',
                'expected_delivery_date',
                'delivery_store',
                'currency',
                'payment_terms',
                'delivery_terms',
                'warranty_required',
                'amc_required',
                'tax_included',
                'freight_included',
                'notes_to_vendor',
            ] as $column) {
                if (Schema::hasColumn('rfqs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
