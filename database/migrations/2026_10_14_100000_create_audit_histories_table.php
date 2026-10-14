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
        if (!Schema::hasTable('audit_histories')) {
            Schema::create('audit_histories', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('asset_id');
                $table->unsignedBigInteger('auditor_id')->nullable();
                $table->string('auditor_name')->default('Auditor');
                $table->string('audit_status', 50)->default('Audited');
                $table->string('location_status')->nullable();
                $table->text('remarks')->nullable();
                $table->dateTime('audited_at')->useCurrent();
                $table->timestamps();

                $table->foreign('asset_id')->references('id')->on('assets')->onDelete('cascade');
                $table->foreign('auditor_id')->references('id')->on('users')->nullOnDelete();
                $table->index(['asset_id', 'audited_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_histories');
    }
};
