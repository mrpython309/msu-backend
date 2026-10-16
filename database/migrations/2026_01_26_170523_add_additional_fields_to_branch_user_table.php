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
        Schema::table('branch_user', function (Blueprint $table) {
             $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete();
         $table->unique(['branch_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branch_user', function (Blueprint $table) {
            $table->dropForeign(['branch_id']);
            $table->dropForeign(['user_id']);
            $table->dropColumn('branch_id');
            $table->dropColumn('user_id');
        });
    }
};
