<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Add composite index to optimize slow gift_logs query with GROUP BY sender_id + SUM(giftPrice)
     * This addresses the performance issue reported in the alert analysis (302K requests)
     */
    public function up(): void
    {
        Schema::table('gift_logs', function (Blueprint $table) {
            // Composite index for GROUP BY sender_id ORDER BY SUM(giftPrice)
            $table->index(['created_at', 'sender_id', 'giftPrice'], 'idx_gift_logs_rankings');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('gift_logs', function (Blueprint $table) {
            $table->dropIndex('idx_gift_logs_rankings');
        });
    }
};
