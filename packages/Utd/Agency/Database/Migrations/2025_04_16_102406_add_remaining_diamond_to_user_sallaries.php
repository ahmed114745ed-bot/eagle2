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
        if (! Schema::hasColumn('user_sallaries', 'remaining_diamond')) {
            Schema::table('user_sallaries', function (Blueprint $table) {
                $table->double('remaining_diamond')->default(0);
            });
        }

        if (! Schema::hasColumn('user_sallaries', 'target_id')) {
            Schema::table('user_sallaries', function (Blueprint $table) {
                $table->unsignedBigInteger('target_id')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_sallaries', function (Blueprint $table) {
            $table->dropColumn('remaining_diamond');
            $table->dropColumn('target_id');
        });
    }
};
