<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Batch size per delete cycle — tune based on server capacity
     */
    private int $batchSize = 1000;

    /**
     * Only clean duplicates within this many days back
     */
    private int $daysBack = 7;

    /**
     * Run the migrations.
     *
     * IMPORTANT: This migration is controlled via API endpoint only.
     * It will NOT run automatically to prevent accidental execution.
     *
     * This migration cleans duplicate orders from coin_game_users_archive table
     * for the last 7 days only, using partition-aware batch processing.
     */
    public function up(): void
    {
        // Skip if not triggered via API endpoint
        if (!config('app.allow_duplicate_cleanup_migration', false)) {
            Log::warning('Archive Cleanup migration skipped - not triggered via API endpoint');
            return;
        }

        $fromDate = now()->subDays($this->daysBack)->format('Y-m-d H:i:s');

        Log::info('=== Starting Duplicate Orders Cleanup (Archive - Last 7 Days) ===');
        Log::info("Date filter: created_at >= {$fromDate}");

        // Step 1: Get only relevant partitions (last 7 days = current month + maybe previous month)
        $partitions = $this->getRelevantPartitions($fromDate);

        Log::info('Relevant partitions found: ' . count($partitions));

        if (empty($partitions)) {
            Log::warning('No relevant partitions found for the last 7 days');
            return;
        }

        $totalRemoved = 0;

        // Step 2: Process each relevant partition separately
        foreach ($partitions as $partitionName) {
            Log::info("Processing partition: {$partitionName}");

            $removedInPartition = $this->cleanPartition($partitionName, $fromDate);
            $totalRemoved += $removedInPartition;

            Log::info("Partition {$partitionName} done — removed: {$removedInPartition}");
        }

        // Step 3: Final verification (scoped to last 7 days only)
        $remainingDuplicates = $this->countDuplicates($fromDate);

        if ($remainingDuplicates > 0) {
            Log::warning("WARNING: Still have {$remainingDuplicates} duplicate orders after cleanup!");
        } else {
            Log::info("SUCCESS: All duplicates in last {$this->daysBack} days cleaned successfully!");
        }

        Log::info('=== Cleanup Summary (Archive - Last 7 Days) ===', [
            'from_date'             => $fromDate,
            'total_removed'         => $totalRemoved,
            'remaining_duplicates'  => $remainingDuplicates,
        ]);
    }

    /**
     * Get only partitions that contain data from the last 7 days
     * (current month + previous month to be safe)
     */
    private function getRelevantPartitions(string $fromDate): array
    {
        $currentYm  = (int) date('Ym');
        $previousYm = (int) date('Ym', strtotime('-1 month'));

        $partitions = DB::select("
            SELECT PARTITION_NAME
            FROM INFORMATION_SCHEMA.PARTITIONS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'coin_game_users_archive'
              AND PARTITION_NAME IS NOT NULL
              AND PARTITION_NAME IN ('p{$currentYm}', 'p{$previousYm}', 'pMax')
            ORDER BY PARTITION_ORDINAL_POSITION
        ");

        return array_column($partitions, 'PARTITION_NAME');
    }

    /**
     * Clean duplicates inside a single partition using batches,
     * scoped to the last 7 days only
     */
    private function cleanPartition(string $partitionName, string $fromDate): int
    {
        $totalRemoved = 0;

        do {
            // Find duplicate IDs to delete within the date range (keep MIN id per order_id)
            $idsToDelete = DB::select("
                SELECT t1.id
                FROM coin_game_users_archive PARTITION ({$partitionName}) t1
                INNER JOIN (
                    SELECT order_id, MIN(id) as keep_id
                    FROM coin_game_users_archive PARTITION ({$partitionName})
                    WHERE order_id IS NOT NULL
                      AND created_at >= '{$fromDate}'
                    GROUP BY order_id
                    HAVING COUNT(*) > 1
                ) t2 ON t1.order_id = t2.order_id
                WHERE t1.id != t2.keep_id
                  AND t1.created_at >= '{$fromDate}'
                LIMIT {$this->batchSize}
            ");

            if (empty($idsToDelete)) {
                break;
            }

            $ids = array_column($idsToDelete, 'id');

            DB::statement("
                DELETE FROM coin_game_users_archive PARTITION ({$partitionName})
                WHERE id IN (" . implode(',', $ids) . ")
            ");

            $removed = count($ids);
            $totalRemoved += $removed;

            Log::info("Partition {$partitionName} — batch deleted: {$removed}");

            // Small sleep to reduce DB pressure
            usleep(50000); // 50ms

        } while (true);

        return $totalRemoved;
    }

    /**
     * Count remaining duplicates scoped to last 7 days only
     */
    private function countDuplicates(string $fromDate): int
    {
        $result = DB::select("
            SELECT COUNT(*) as count
            FROM (
                SELECT order_id
                FROM coin_game_users_archive
                WHERE order_id IS NOT NULL
                  AND created_at >= '{$fromDate}'
                GROUP BY order_id
                HAVING COUNT(*) > 1
            ) as dups
        ");

        return $result[0]->count ?? 0;
    }

    /**
     * Reverse the migrations — no automatic rollback due to table size
     */
    public function down(): void
    {
        Log::warning('Archive cleanup migration has no automatic rollback due to table size.');
        Log::warning('If rollback is needed, restore from database backup.');
    }
};
