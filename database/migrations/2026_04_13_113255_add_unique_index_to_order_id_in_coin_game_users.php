<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * IMPORTANT: This migration requires cleanup to run first.
     * It will NOT run automatically to prevent accidental execution.
     */
    public function up(): void
    {
        // Skip if not triggered via API endpoint
        if (!config('app.allow_duplicate_cleanup_migration', false)) {
            \Log::warning('Unique index migration skipped - not triggered via API endpoint');
            return;
        }
        Schema::table('coin_game_users', function (Blueprint $table) {
            // Add unique index on order_id to prevent duplicate orders
            $table->unique('order_id', 'unique_order_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('coin_game_users', function (Blueprint $table) {
            $table->dropUnique('unique_order_id');
        });
    }
};
