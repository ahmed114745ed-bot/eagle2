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
     * 
     * It also refunds users for extra deductions caused by duplicate orders.
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

        // Step 1: Calculate refunds for users affected by duplicate deductions
        $refundData = $this->calculateRefunds($fromDate);
        Log::info('Refund calculation complete', [
            'affected_users' => count($refundData),
            'total_refund_amount' => array_sum(array_column($refundData, 'refund_amount'))
        ]);

        // Step 2: Get only relevant partitions (last 7 days = current month + maybe previous month)
        $partitions = $this->getRelevantPartitions($fromDate);

        Log::info('Relevant partitions found: ' . count($partitions));

        if (empty($partitions)) {
            Log::warning('No relevant partitions found for the last 7 days');
            return;
        }

        $totalRemoved = 0;

        // Step 3: Process each relevant partition separately
        foreach ($partitions as $partitionName) {
            Log::info("Processing partition: {$partitionName}");

            $removedInPartition = $this->cleanPartition($partitionName, $fromDate);
            $totalRemoved += $removedInPartition;

            Log::info("Partition {$partitionName} done — removed: {$removedInPartition}");
        }

        // Step 4: Apply refunds to users
        $refundsSummary = $this->applyRefunds($refundData);

        // Step 5: Final verification (scoped to last 7 days only)
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
            'users_refunded'        => $refundsSummary['users_refunded'],
            'total_refunded'        => $refundsSummary['total_refunded'],
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
     * 
     * Only deletes from coin_game_users_archive, keeping the original record
     */
    private function cleanPartition(string $partitionName, string $fromDate): int
    {
        $totalRemoved = 0;

        do {
            // Find duplicate IDs to delete within the date range (keep MIN id per order_id)
            // Only delete from coin_game_users_archive, not from coin_game_users
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
     * Calculate refunds needed for users affected by duplicate deductions
     * 
     * For each order_id with duplicates in coin_game_users_archive:
     * - Keep only the first (original) deduction
     * - Calculate refund for extra deductions
     */
    private function calculateRefunds(string $fromDate): array
    {
        $refundData = [];

        // Get all duplicate orders from coin_game_users_archive only
        // (since we're only cleaning up duplicates in the archive table)
        $duplicateOrders = DB::select("
            SELECT 
                order_id,
                user_id,
                COUNT(*) as record_count,
                SUM(CASE WHEN type = 1 THEN coins ELSE 0 END) as total_deduct,
                MIN(id) as first_id
            FROM coin_game_users_archive
            WHERE order_id IS NOT NULL
              AND type = 1
              AND created_at >= '{$fromDate}'
            GROUP BY order_id, user_id
            HAVING COUNT(*) > 1
        ");

        foreach ($duplicateOrders as $order) {
            // Get the first (original) deduction amount from archive
            $firstRecord = DB::selectOne("
                SELECT coins
                FROM coin_game_users_archive
                WHERE order_id = ?
                AND type = 1
                ORDER BY id ASC
                LIMIT 1
            ", [$order->order_id]);

            if (!$firstRecord) {
                continue;
            }

            $originalDeduction = $firstRecord->coins;
            $totalDeducted = $order->total_deduct;
            $extraDeduction = $totalDeducted - $originalDeduction;

            if ($extraDeduction > 0) {
                if (!isset($refundData[$order->user_id])) {
                    $refundData[$order->user_id] = [
                        'user_id' => $order->user_id,
                        'refund_amount' => 0,
                        'affected_orders' => []
                    ];
                }

                $refundData[$order->user_id]['refund_amount'] += $extraDeduction;
                $refundData[$order->user_id]['affected_orders'][] = [
                    'order_id' => $order->order_id,
                    'extra_deduction' => $extraDeduction
                ];
            }
        }

        return $refundData;
    }

    /**
     * Apply refunds to users' accounts
     * 
     * For type=1 (deductions): Refund the extra amount that was deducted
     * For type=2 (additions): Deduct the extra amount that was added
     */
    private function applyRefunds(array $refundData): array
    {
        $usersRefunded = 0;
        $totalRefunded = 0;

        foreach ($refundData as $userId => $data) {
            try {
                DB::transaction(function () use ($userId, $data, &$usersRefunded, &$totalRefunded) {
                    // Lock user row for update
                    $user = DB::table('users')
                        ->where('id', $userId)
                        ->lockForUpdate()
                        ->first();

                    if (!$user) {
                        Log::warning("User {$userId} not found for refund");
                        return;
                    }

                    // Process each affected order
                    foreach ($data['affected_orders'] as $orderInfo) {
                        $orderId = $orderInfo['order_id'];
                        $extraAmount = $orderInfo['extra_deduction'];

                        // Get the type of the original transaction
                        $originalRecord = DB::selectOne("
                            SELECT type
                            FROM (
                                SELECT type FROM coin_game_users WHERE order_id = ? LIMIT 1
                                UNION ALL
                                SELECT type FROM coin_game_users_archive WHERE order_id = ? LIMIT 1
                            ) t
                            LIMIT 1
                        ", [$orderId, $orderId]);

                        if (!$originalRecord) {
                            Log::warning("Could not find original record for order {$orderId}");
                            continue;
                        }

                        $transactionType = (int)$originalRecord->type;

                        // type = 0 means user lost coins (deduction)
                        // type = 1 means user gained coins (addition)
                        // For type=0 (deductions): Refund the extra amount (add coins back)
                        // For type=1 (additions): Deduct the extra amount (remove coins)
                        if ($transactionType == 0) {
                            // Type 0: Deduction - refund by adding coins back
                            DB::table('users')
                                ->where('id', $userId)
                                ->increment('di', $extraAmount);

                            Log::info("Refund (type=0 deduction) applied to user {$userId}", [
                                'order_id' => $orderId,
                                'refund_amount' => $extraAmount
                            ]);
                        } elseif ($transactionType == 1) {
                            // Type 1: Addition - deduct the extra amount (remove coins)
                            DB::table('users')
                                ->where('id', $userId)
                                ->decrement('di', $extraAmount);

                            Log::info("Deduction (type=1 addition) applied to user {$userId}", [
                                'order_id' => $orderId,
                                'deduction_amount' => $extraAmount
                            ]);
                        }

                        $usersRefunded++;
                        $totalRefunded += $extraAmount;
                    }
                });
            } catch (\Exception $e) {
                Log::error("Failed to apply refund to user {$userId}: " . $e->getMessage());
            }
        }

        return [
            'users_refunded' => $usersRefunded,
            'total_refunded' => $totalRefunded
        ];
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
