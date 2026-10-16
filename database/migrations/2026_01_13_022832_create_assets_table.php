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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_name');
            $table->unsignedBigInteger('asset_type_id');
            $table->foreign('asset_type_id')->references('id')->on('asset_types')->onDelete('cascade');
            $table->string('serial_no')->nullable();
            $table->string('asset_code')->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_cost',10,3)->nullable();
            $table->enum('status',['available','assigned','under_maintenance','disposed'])->nullable();
            $table->enum('condition', ['new','good', 'fair', 'poor'])->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('location_id')->nullable();
            $table->foreign('location_id')->references('id')->on('branch_locations')->onDelete('cascade');
            $table->string('supplier')->nullable();
            $table->text('warrenty_information')->nullable();
            $table->date('warrenty_expires')->nullable();
            $table->enum('depreciation_method', ['no_depreciation','straight_line', 'reducing_balance'])->nullable();
            $table->string('userful_life')->nullable();
            $table->decimal('salvage_value',10,2)->nullable();
           $table->unsignedBigInteger('created_by')->nullable();
$table->unsignedBigInteger('updated_by')->nullable();

$table->foreign('created_by')
      ->references('id')
      ->on('users')
      ->onDelete('cascade');

$table->foreign('updated_by')
      ->references('id')
      ->on('users')
      ->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
