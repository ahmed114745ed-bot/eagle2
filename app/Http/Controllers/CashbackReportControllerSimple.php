<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CashbackReportControllerSimple extends Controller
{
    /**
     * عرض التقرير مع pagination (بدون Job)
     * Route: GET /admin/cashback-report-simple
     */
    public function index(Request $request)
    {
        $page = $request->get('page', 1);
        $perPage = 50; // عدد المستخدمين في كل صفحة
        $offset = ($page - 1) * $perPage;

        try {
            // جلب البيانات مع pagination
            $users = DB::select("
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
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
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
                    COUNT(*) AS total_cashback_operations,
                    SUM(CASE WHEN missing_amount > 0 THEN 1 ELSE 0 END) AS times_missing,
                    CAST(SUM(cashback_amount) AS SIGNED) AS total_cashback_logged,
                    CAST(SUM(missing_amount) AS SIGNED) AS his_right,
                    ROUND((SUM(missing_amount) / NULLIF(SUM(cashback_amount), 0)) * 100, 2) AS loss_percentage,
                    MIN(created_at) AS first_cashback_at,
                    MAX(created_at) AS last_cashback_at
                FROM cashback_issues
                GROUP BY user_id
                HAVING his_right > 0
                ORDER BY his_right DESC
                LIMIT ? OFFSET ?
            ", [$perPage, $offset]);

            // حساب الإجمالي (من الكاش)
            $summary = $this->getSummary();

            return response()->json([
                'status' => 'success',
                'data' => $users,
                'summary' => $summary,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'has_more' => count($users) === $perPage,
                ],
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to generate cashback report', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'فشل تحميل التقرير: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * جلب الإحصائيات الإجمالية (مع caching)
     */
    private function getSummary()
    {
        return Cache::remember('cashback_summary', 300, function () {
            $result = DB::selectOne("
                WITH all_logs AS (
                    SELECT
                        user_id,
                        type,
                        amount,
                        amount_before,
                        (amount_before + amount) AS calculated_after,
                        LEAD(amount_before) OVER (PARTITION BY user_id ORDER BY id) AS next_amount_before
                    FROM user_coin_logs
                    WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                ),
                cashback_stats AS (
                    SELECT
                        user_id,
                        SUM(amount) AS total_cashback,
                        SUM(
                            CASE
                                WHEN type = 'cashback'
                                    AND next_amount_before IS NOT NULL
                                    AND calculated_after > next_amount_before
                                THEN (calculated_after - next_amount_before)
                                ELSE 0
                            END
                        ) AS total_missing
                    FROM all_logs
                    WHERE type = 'cashback'
                    GROUP BY user_id
                    HAVING total_missing > 0
                )
                SELECT
                    COUNT(DISTINCT user_id) AS total_affected_users,
                    CAST(SUM(total_missing) AS SIGNED) AS total_missing_cashback,
                    CAST(SUM(total_cashback) AS SIGNED) AS total_cashback_logged,
                    ROUND(AVG((total_missing / NULLIF(total_cashback, 0)) * 100), 2) AS average_loss_percentage
                FROM cashback_stats
            ");

            return [
                'total_affected_users' => $result->total_affected_users ?? 0,
                'total_missing_cashback' => $result->total_missing_cashback ?? 0,
                'total_cashback_logged' => $result->total_cashback_logged ?? 0,
                'average_loss_percentage' => $result->average_loss_percentage ?? 0,
            ];
        });
    }

    /**
     * تصدير كل البيانات (chunked) - بدون timeout
     */
    public function exportCsv(Request $request)
    {
        try {
            return response()->stream(function () {
                $handle = fopen('php://output', 'w');

                // كتابة الـ headers
                fputcsv($handle, [
                    'User ID',
                    'Total Cashback Operations',
                    'Times Missing',
                    'Total Cashback Logged',
                    'Missing Cashback (His Right)',
                    'Loss Percentage',
                    'First Cashback',
                    'Last Cashback'
                ]);

                $offset = 0;
                $limit = 100;

                // جلب البيانات على دفعات
                while (true) {
                    $users = DB::select("
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
                            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
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
                            COUNT(*) AS total_cashback_operations,
                            SUM(CASE WHEN missing_amount > 0 THEN 1 ELSE 0 END) AS times_missing,
                            CAST(SUM(cashback_amount) AS SIGNED) AS total_cashback_logged,
                            CAST(SUM(missing_amount) AS SIGNED) AS his_right,
                            ROUND((SUM(missing_amount) / NULLIF(SUM(cashback_amount), 0)) * 100, 2) AS loss_percentage,
                            MIN(created_at) AS first_cashback_at,
                            MAX(created_at) AS last_cashback_at
                        FROM cashback_issues
                        GROUP BY user_id
                        HAVING his_right > 0
                        ORDER BY his_right DESC
                        LIMIT ? OFFSET ?
                    ", [$limit, $offset]);

                    if (empty($users)) {
                        break;
                    }

                    foreach ($users as $user) {
                        fputcsv($handle, [
                            $user->user_id,
                            $user->total_cashback_operations,
                            $user->times_missing,
                            $user->total_cashback_logged,
                            $user->his_right,
                            $user->loss_percentage,
                            $user->first_cashback_at,
                            $user->last_cashback_at,
                        ]);
                    }

                    $offset += $limit;

                    // إرسال البيانات مباشرة للمتصفح
                    if (ob_get_level() > 0) {
                        ob_flush();
                    }
                    flush();
                }

                fclose($handle);
            }, 200, [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="cashback_report_' . date('Y-m-d_H-i-s') . '.csv"',
                'X-Accel-Buffering' => 'no', // Disable nginx buffering
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'فشل التصدير: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * صفحة HTML بسيطة
     */
    public function html()
    {
        return view('admin.cashback-report-simple');
    }

    /**
     * مسح الكاش
     */
    public function clearCache()
    {
        Cache::forget('cashback_summary');

        return response()->json([
            'status' => 'success',
            'message' => 'تم مسح الكاش بنجاح',
        ]);
    }
}
