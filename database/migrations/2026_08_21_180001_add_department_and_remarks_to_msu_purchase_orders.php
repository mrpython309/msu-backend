<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('msu_purchase_orders', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->after('employee_master_id')
                ->constrained('departments')->nullOnDelete();
            $table->text('remarks')->nullable()->after('shipping_contact_person');
        });
    }

    public function down(): void
    {
        Schema::table('msu_purchase_orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('department_id');
            $table->dropColumn('remarks');
        });
    }
};
