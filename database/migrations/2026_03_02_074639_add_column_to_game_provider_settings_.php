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
        Schema::table('game_provider_settings', function (Blueprint $table) {
            $table->string('channel')->nullable();
            $table->string('app_id')->nullable();
            $table->string('gsp')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('game_provider_settings', function (Blueprint $table) {
            $table->dropColumn(['channel', 'app_id', 'gsp']);
        });
    }
};
