<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CompensateCashbackLossJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $timeout = 600; // 10 minutes
    public $tries = 1;

    private $startDate;
    private $endDate;
    private $batchId;

    public function __construct($startDate = null, $endDate = null)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->batchId = 'comp_' . date('YmdHis');
    }

    public function handle()
    {
        try {
            Log::info('Starting cashback compensation job', [
                'batch_id' => $this->batchId,
                'start_date' => $this->startDate,
                'end_date' => $this->endDate,
            ]);

            // تحديد الفترة الزمنية
            $dateFilter = $this->startDate && $this->endDate
                ? "AND created_at BETWEEN '{$this->startDate}' AND '{$this->endDate}'"
                : 'AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)';

            // الخطوة 1: جلب المستخدمين المتضررين
            $affectedUsers = DB::select("
                WITH all_logs_with_next AS (
                    SELECT
                        user_id,
                        type,
                        amount,
                        amount_before,
                        (amount_before + amount) AS calculated_after,
                        LEAD(amount_before) OVER (PARTITION BY user_id ORDER BY id) AS next_amount_before,
                        created_at
                    FROM user_coin_logs
                    WHERE 1=1 {$dateFilter}
                ),
                cashback_issues AS (
                    SELECT
                        user_id,
                        amount AS cashback_amount,
                        CASE
                            WHEN next_amount_before IS NOT NULL
                                AND calculated_after > next_amount_before
                            THEN (calculated_after - next_amount_before)
                            ELSE 0
                        END AS missing_amount,
                        created_at
                    FROM all_logs_with_next
                    WHERE type = 'cashback'
                )
                SELECT
                    user_id,
                    CAST(SUM(missing_amount) AS SIGNED) AS compensation_amount,
                    COUNT(*) AS affected_operations
                FROM cashback_issues
                WHERE user_id NOT IN (
                    -- استبعاد المستخدمين اللي اتعوضوا قبل كده
                    SELECT DISTINCT user_id
                    FROM user_coin_logs
                    WHERE type = 'compensation'
                        AND sub_type = 'cashback_loss_refund'
                )
                GROUP BY user_id
                HAVING compensation_amount > 0
                ORDER BY compensation_amount DESC
            ");

            if (empty($affectedUsers)) {
                Log::info('No users to compensate', ['batch_id' => $this->batchId]);
                return;
            }

            $totalUsers = count($affectedUsers);
            $totalAmount = array_sum(array_column($affectedUsers, 'compensation_amount'));
            $processedUsers = 0;
            $failedUsers = [];

            Log::info('Found users to compensate', [
                'batch_id' => $this->batchId,
                'total_users' => $totalUsers,
                'total_amount' => $totalAmount,
            ]);

            // الخطوة 2: معالجة كل مستخدم
            foreach ($affectedUsers as $user) {
                try {
                    DB::beginTransaction();

                    // جلب المستخدم مع قفل
                    $dbUser = DB::table('users')
                        ->where('id', $user->user_id)
                        ->lockForUpdate()
                        ->first();

                    if (!$dbUser) {
                        throw new \Exception('المستخدم غير موجود');
                    }

                    $balanceBefore = $dbUser->di;
                    $balanceAfter = $balanceBefore + $user->compensation_amount;

                    // تحديث رصيد المستخدم
                    $updated = DB::table('users')
                        ->where('id', $user->user_id)
                        ->where('di', $balanceBefore)  // Optimistic locking
                        ->update([
                            'di' => $balanceAfter,
                            'updated_at' => now(),
                        ]);

                    if (!$updated) {
                        throw new \Exception('فشل التحديث - تم تعديل الرصيد من طلب آخر');
                    }

                    // تسجيل في user_coin_logs باستخدام Helper
                    \App\Helpers\UserCoinLogHelper::logByType(
                        $user->user_id,
                        $user->compensation_amount,
                        $balanceBefore,
                        \App\Enums\UserCoinLogType::COMPENSATION,
                        'Cashback Loss Refund',  // item_name
                        0  // helper_amount
                    );

                    DB::commit();
                    $processedUsers++;

                } catch (\Exception $e) {
                    DB::rollBack();
                    $failedUsers[] = [
                        'user_id' => $user->user_id,
                        'error' => $e->getMessage(),
                    ];

                    Log::error('Failed to compensate user', [
                        'batch_id' => $this->batchId,
                        'user_id' => $user->user_id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }

            // تسجيل النتائج النهائية
            Log::info('Compensation job completed', [
                'batch_id' => $this->batchId,
                'total_users' => $totalUsers,
                'processed' => $processedUsers,
                'failed' => count($failedUsers),
                'total_amount' => $totalAmount,
                'failed_users' => $failedUsers,
            ]);

        } catch (\Exception $e) {
            Log::error('Compensation job failed', [
                'batch_id' => $this->batchId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }
}
