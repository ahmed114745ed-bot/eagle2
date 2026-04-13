<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration removes duplicate orders from coin_game_users table
     * before adding the unique constraint in the next migration.
     *
     * IMPORTANT: This migration is controlled via API endpoint only.
     * It will NOT run automatically to prevent accidental execution.
     */
    public function up(): void
    {
        // Skip if not triggered via API endpoint
        if (!config('app.allow_duplicate_cleanup_migration', false)) {
            Log::warning('Cleanup migration skipped - not triggered via API endpoint');
            return;
        }
        Log::info('=== Starting Duplicate Orders Cleanup ===');

        // Step 1: Create backup table
        DB::statement('DROP TABLE IF EXISTS coin_game_users_duplicates_backup');

        DB::statement('
            CREATE TABLE coin_game_users_duplicates_backup AS
            SELECT t1.*
            FROM coin_game_users t1
            INNER JOIN (
                SELECT order_id
                FROM coin_game_users
                WHERE order_id IS NOT NULL
                GROUP BY order_id
                HAVING COUNT(*) > 1
            ) t2 ON t1.order_id = t2.order_id
        ');

        $backupCount = DB::table('coin_game_users_duplicates_backup')->count();
        Log::info("Backup created: {$backupCount} duplicate records backed up");

        // Step 2: Count duplicates before deletion
        $duplicatesBefore = DB::select('
            SELECT COUNT(*) as count
            FROM (
                SELECT order_id, COUNT(*) as cnt
                FROM coin_game_users
                WHERE order_id IS NOT NULL
                GROUP BY order_id
                HAVING cnt > 1
            ) as dups
        ')[0]->count ?? 0;

        Log::info("Duplicates found: {$duplicatesBefore} orders");

        // Step 3: Delete duplicates, keeping only the oldest record for each order_id
        DB::statement('
            DELETE FROM coin_game_users
            WHERE id IN (
                SELECT id FROM (
                    SELECT t1.id
                    FROM coin_game_users t1
                    INNER JOIN (
                        SELECT order_id, MIN(id) as keep_id
                        FROM coin_game_users
                        WHERE order_id IS NOT NULL
                        GROUP BY order_id
                        HAVING COUNT(*) > 1
                    ) t2 ON t1.order_id = t2.order_id
                    WHERE t1.id != t2.keep_id
                ) as to_delete
            )
        ');

        // Step 4: Verify cleanup
        $duplicatesAfter = DB::select('
            SELECT COUNT(*) as count
            FROM (
                SELECT order_id, COUNT(*) as cnt
                FROM coin_game_users
                WHERE order_id IS NOT NULL
                GROUP BY order_id
                HAVING cnt > 1
            ) as dups
        ')[0]->count ?? 0;

        Log::info("Cleanup completed. Remaining duplicates: {$duplicatesAfter}");

        if ($duplicatesAfter > 0) {
            Log::warning("WARNING: Still have {$duplicatesAfter} duplicate orders after cleanup!");
        } else {
            Log::info("SUCCESS: All duplicates cleaned successfully!");
        }

        // Step 5: Log summary
        $totalOrders = DB::table('coin_game_users')->whereNotNull('order_id')->count();
        $uniqueOrders = DB::table('coin_game_users')->whereNotNull('order_id')->distinct('order_id')->count('order_id');

        Log::info('=== Cleanup Summary ===', [
            'total_orders' => $totalOrders,
            'unique_orders' => $uniqueOrders,
            'backup_records' => $backupCount,
            'duplicates_removed' => $backupCount - $duplicatesBefore,
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restore from backup if it exists
        if (DB::select("SHOW TABLES LIKE 'coin_game_users_duplicates_backup'")) {
            Log::info('Restoring duplicates from backup...');

            DB::statement('
                INSERT INTO coin_game_users
                SELECT * FROM coin_game_users_duplicates_backup
                WHERE id NOT IN (SELECT id FROM coin_game_users)
            ');

            Log::info('Duplicates restored from backup');
        }
    }
};
