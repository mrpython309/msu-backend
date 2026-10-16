<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('asset_names')) {
            return;
        }

        Schema::create('asset_names', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('name_normalized')->unique();
            $table->string('status')->default('active');
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_names');
    }
};

