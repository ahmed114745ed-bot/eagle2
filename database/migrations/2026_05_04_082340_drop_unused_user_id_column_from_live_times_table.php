<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Issue #4: Drop unused user_id column from live_times table
     *
     * Problem:
     * - The live_times table has a user_id column that is not used
     * - 99.98% of rows (16,020 out of 16,023) have NULL values
     * - The system uses 'uid' column instead
     * - An index (idx_live_times_user_id) exists on this unused column, wasting resources
     *
     * Solution:
     * - Drop the index first
     * - Drop the unused user_id column
     * - Clean up database schema to prevent developer confusion
     */
    public function up(): void
    {
        Schema::table('live_times', function (Blueprint $table) {
            // Drop index first if it exists
            $indexExists = DB::select("
                SELECT COUNT(*) as count
                FROM information_schema.statistics
                WHERE table_schema = DATABASE()
                AND table_name = 'live_times'
                AND index_name = 'idx_live_times_user_id'
            ");

            if ($indexExists[0]->count > 0) {
                $table->dropIndex('idx_live_times_user_id');
            }

            // Drop the unused user_id column
            $table->dropColumn('user_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * Note: In case of rollback, we recreate the column but without data
     * as the original data was NULL for 99.98% of rows
     */
    public function down(): void
    {
        Schema::table('live_times', function (Blueprint $table) {
            // Recreate the column as nullable
            $table->unsignedBigInteger('user_id')->nullable()->after('updated_at');

            // Recreate the index
            $table->index('user_id', 'idx_live_times_user_id');
        });
    }
};
