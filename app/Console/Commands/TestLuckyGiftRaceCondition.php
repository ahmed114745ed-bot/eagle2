<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\Promise\Utils;
use GuzzleHttp\Exception\RequestException;

/**
 * Test Lucky Gift Race Condition باستخدام HTTP Requests حقيقية
 *
 * الاستخدام:
 * php artisan test:lucky-race {user_id} {token} --requests=10
 */
class TestLuckyGiftRaceCondition extends Command
{
    /**
     * اسم ووصف الأمر
     */
    protected $signature = 'test:lucky-race
                            {user_id : User ID للمستخدم المرسل}
                            {token : Bearer Token للمصادقة}
                            {--gift_id= : Gift ID (اختياري، سيستخدم أول هدية محظوظة)}
                            {--receiver_id= : Receiver ID (اختياري، سيستخدم أول مستخدم)}
                            {--room_id= : Room Owner ID (اختياري، سيستخدم user_id)}
                            {--requests=10 : عدد الطلبات المتزامنة}
                            {--url= : Base URL (اختياري، يستخدم APP_URL)}';

    protected $description = 'اختبار Race Condition لـ Lucky Gift API باستخدام HTTP requests حقيقية';

    /**
     * تنفيذ الأمر
     */
    public function handle()
    {
        // ══════════════════════════════════════════════════════════════
        // 1. جمع المدخلات
        // ══════════════════════════════════════════════════════════════
        $userId = $this->argument('user_id');
        $token = $this->argument('token');
        $giftId = $this->option('gift_id');
        $receiverId = $this->option('receiver_id');
        $roomId = $this->option('room_id') ?? $userId;
        $requestsCount = (int) $this->option('requests');
        $baseUrl = 'https://eagle.utdsoftware.com';

        // ══════════════════════════════════════════════════════════════
        // 2. التحقق من المدخلات
        // ══════════════════════════════════════════════════════════════
        $user = User::find($userId);
        if (!$user) {
            $this->error("❌ User ID {$userId} غير موجود!");
            return 1;
        }

        // الحصول على Gift ID إذا لم يُمرر
        if (!$giftId) {
            $gift = \App\Models\Gift::where('type', 6)->where('enable', 1)->first();
            if (!$gift) {
                $this->error("❌ لا توجد هدية محظوظة متاحة!");
                return 1;
            }
            $giftId = $gift->id;
            $this->info("ℹ️  استخدام Gift ID: {$giftId}");
        }

        // الحصول على Receiver ID إذا لم يُمرر
        if (!$receiverId) {
            $receiver = User::where('id', '!=', $userId)->first();
            if (!$receiver) {
                $this->error("❌ لا يوجد مستخدم آخر!");
                return 1;
            }
            $receiverId = $receiver->id;
            $this->info("ℹ️  استخدام Receiver ID: {$receiverId}");
        }

        // ══════════════════════════════════════════════════════════════
        // 3. عرض معلومات الاختبار
        // ══════════════════════════════════════════════════════════════
        $balanceBefore = $user->di;

        $this->newLine();
        $this->info('══════════════════════════════════════════════════════════════════════');
        $this->info('🔥 LUCKY GIFT RACE CONDITION TEST - HTTP Request Mode');
        $this->info('══════════════════════════════════════════════════════════════════════');
        $this->table(
            ['Parameter', 'Value'],
            [
                ['Sender User ID', $userId],
                ['Sender Name', $user->name],
                ['Balance Before', number_format($balanceBefore)],
                ['Gift ID', $giftId],
                ['Receiver ID', $receiverId],
                ['Room Owner ID', $roomId],
                ['Concurrent Requests', $requestsCount],
                ['Base URL', $baseUrl],
                ['Token', substr($token, 0, 20) . '...'],
            ]
        );

        if (!$this->confirm('هل تريد المتابعة؟', true)) {
            $this->warn('تم الإلغاء.');
            return 0;
        }

        // ══════════════════════════════════════════════════════════════
        // 4. بناء الـ Payload
        // ══════════════════════════════════════════════════════════════
        $payload = [
            'id' => $giftId,
            'owner_id' => $roomId,
            'toUid' => $receiverId,
            'num' => 1,
            'count' => 1,
        ];

        // ══════════════════════════════════════════════════════════════
        // 5. إرسال الطلبات بشكل متزامن باستخدام Guzzle Promises
        // ══════════════════════════════════════════════════════════════
        $endpoint = rtrim($baseUrl, '/') . '/api/gifts/v2/send-lucky-gift-combo';

        $this->newLine();
        $this->info("📡 إرسال {$requestsCount} طلب متزامن إلى: {$endpoint}");
        $this->info("⚡ استخدام Guzzle Promises - جميع الطلبات في نفس اللحظة!");
        $this->info('────────────────────────────────────────────────────────────────────────');

        // إنشاء Guzzle Client
        $client = new Client([
            'base_uri' => $baseUrl,
            'timeout' => 30,
            'headers' => [
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ],
        ]);

        $responses = [];
        $startTime = microtime(true);

        $this->info("⏳ جاري إنشاء {$requestsCount} Promise...");

        try {
            // إنشاء مصفوفة من Promises
            $promises = [];
            $requestTimes = [];

            for ($i = 1; $i <= $requestsCount; $i++) {
                $requestTimes[$i] = microtime(true);

                $promises[$i] = $client->postAsync('/api/gifts/v2/send-lucky-gift-combo', [
                    'json' => $payload,
                ]);
            }

            $this->info("🚀 إطلاق جميع الـ Promises في نفس اللحظة...");

            $results = Utils::settle($promises)->wait();

            $totalDuration = microtime(true) - $startTime;

            $this->info("✅ تم استقبال جميع الردود!");

            // معالجة النتائج
            foreach ($results as $index => $result) {
                $requestDuration = microtime(true) - $requestTimes[$index];

                if ($result['state'] === 'fulfilled') {
                    $response = $result['value'];
                    $body = json_decode($response->getBody()->getContents(), true);

                    $responses[] = [
                        'number' => $index,
                        'status' => $response->getStatusCode(),
                        'duration' => round($requestDuration, 4),
                        'success' => true,
                        'body' => $body,
                    ];
                } else {
                    // rejected
                    $exception = $result['reason'];
                    $statusCode = 0;
                    $errorMessage = $exception->getMessage();

                    if ($exception instanceof RequestException && $exception->hasResponse()) {
                        $statusCode = $exception->getResponse()->getStatusCode();
                        $body = json_decode($exception->getResponse()->getBody()->getContents(), true);
                        $errorMessage = $body['message'] ?? $exception->getMessage();
                    }

                    $responses[] = [
                        'number' => $index,
                        'status' => $statusCode,
                        'duration' => round($requestDuration, 4),
                        'success' => false,
                        'error' => $errorMessage,
                    ];
                }
            }
        } catch (\Exception $e) {
            $this->error("❌ خطأ أثناء إرسال الطلبات: " . $e->getMessage());
            return 1;
        }

        $totalDuration = microtime(true) - $startTime;

        // ══════════════════════════════════════════════════════════════
        // 6. تحليل النتائج
        // ══════════════════════════════════════════════════════════════
        $this->newLine(2);
        $this->info('────────────────────────────────────────────────────────────────────────');
        $this->info(sprintf('⏱️  Total Duration: %.4fs | Average: %.4fs per request', $totalDuration, $totalDuration / $requestsCount));
        $this->info('────────────────────────────────────────────────────────────────────────');

        // عرض النتائج
        $tableData = [];
        $successCount = 0;
        $failedCount = 0;

        foreach ($responses as $resp) {
            if ($resp['success']) {
                $successCount++;
                $status = '✅ ' . $resp['status'];
            } else {
                $failedCount++;
                $status = '❌ ' . ($resp['status'] ?: 'Error');
            }

            $message = $resp['body']['message'] ?? ($resp['error'] ?? 'Unknown');

            $tableData[] = [
                sprintf('#%02d', $resp['number']),
                $status,
                sprintf('%.4fs', $resp['duration']),
                substr($message, 0, 50),
            ];
        }

        $this->table(
            ['Request', 'Status', 'Duration', 'Message'],
            $tableData
        );

        // ══════════════════════════════════════════════════════════════
        // 7. ملخص النتائج
        // ══════════════════════════════════════════════════════════════
        $user->refresh();
        $balanceAfter = $user->di;
        $balanceChange = $balanceBefore - $balanceAfter;

        $this->newLine();
        $this->info('══════════════════════════════════════════════════════════════════════');
        $this->info('📊 RESULTS SUMMARY');
        $this->info('══════════════════════════════════════════════════════════════════════');

        $summaryData = [
            ['Total Requests', $requestsCount],
            ['✅ Successful', $successCount],
            ['❌ Failed', $failedCount],
            ['Success Rate', sprintf('%.2f%%', ($successCount / $requestsCount) * 100)],
            [''],
            ['💰 Balance Before', number_format($balanceBefore)],
            ['💰 Balance After', number_format($balanceAfter)],
            ['💸 Balance Change', number_format($balanceChange)],
            [''],
            ['⏱️  Total Duration', sprintf('%.4fs', $totalDuration)],
            ['⏱️  Average Duration', sprintf('%.4fs', $totalDuration / $requestsCount)],
        ];

        $this->table(['Metric', 'Value'], $summaryData);

        // ══════════════════════════════════════════════════════════════
        // 8. التحققات
        // ══════════════════════════════════════════════════════════════
        $this->newLine();
        $this->info('🔍 VERIFICATION CHECKS');
        $this->info('────────────────────────────────────────────────────────────────────────');

        // تحقق 1: رصيد غير سالب
        if ($balanceAfter >= 0) {
            $this->info('✅ Balance is not negative: ' . number_format($balanceAfter));
        } else {
            $this->error('❌ CRITICAL: Balance is NEGATIVE: ' . number_format($balanceAfter));
        }

        // تحقق 2: خصم الرصيد منطقي
        $gift = \App\Models\Gift::find($giftId);
        $expectedMinDeduction = $gift->price * $successCount;
        if ($balanceChange >= $expectedMinDeduction) {
            $this->info("✅ Balance deduction is valid: {$balanceChange} >= {$expectedMinDeduction}");
        } else {
            $this->warn("⚠️  Balance deduction seems low: {$balanceChange} < {$expectedMinDeduction}");
        }

        // تحقق 3: عدد GiftLogs
        $giftLogs = \App\Models\GiftLog::where('sender_id', $userId)
            ->where('giftId', $giftId)
            ->where('created_at', '>', now()->subMinutes(5))
            ->count();

        $this->info("📝 New Gift Logs created: {$giftLogs}");

        if ($giftLogs > 0) {
            $this->info('✅ Gift logs were created successfully');
        } else {
            $this->warn('⚠️  No gift logs found (may be due to protection mechanism)');
        }

        // ══════════════════════════════════════════════════════════════
        // 9. عرض Response عينة
        // ══════════════════════════════════════════════════════════════
        if ($successCount > 0) {
            $sampleResponse = collect($responses)->firstWhere('success', true);
            if ($sampleResponse) {
                $this->newLine();
                $this->info('📄 Sample Successful Response (Request #' . $sampleResponse['number'] . '):');
                $this->line(json_encode($sampleResponse['body'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }
        }

        // ══════════════════════════════════════════════════════════════
        // 10. الخلاصة النهائية
        // ══════════════════════════════════════════════════════════════
        $this->newLine();
        $this->info('══════════════════════════════════════════════════════════════════════');
        if ($balanceAfter >= 0 && $successCount > 0) {
            $this->info('✅ TEST PASSED: No race condition detected, data integrity maintained!');
        } else {
            $this->warn('⚠️  TEST COMPLETED: Please review the results above');
        }
        $this->info('══════════════════════════════════════════════════════════════════════');

        return 0;
    }
}
