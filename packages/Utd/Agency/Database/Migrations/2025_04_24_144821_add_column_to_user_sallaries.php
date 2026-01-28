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
        if (!Schema::hasTable('user_sallaries')) return;
        Schema::table('user_sallaries', function (Blueprint $table) {
           if (!Schema::hasColumn('user_sallaries', 'dB')) {
               $table->double('dB')->nullable();
           }
           if (!Schema::hasColumn('user_sallaries', 'app_profit')) {
               $table->double('app_profit')->nullable();
           }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_sallaries', function (Blueprint $table) {
           $table->dropColumn('app_profit');
           $table->dropColumn('dB');
        });
    }
};
