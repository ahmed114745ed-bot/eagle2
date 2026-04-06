<?php

namespace Tests\Feature;

use Tests\TestCase;
use Mockery;
use App\Models\User;
use App\Models\Gift;
use App\Models\Room;
use App\Models\CoreWallet;
use App\Services\Gifts\LuckyGiftService;
use App\Services\FairLuck\V7\FairLuckServiceV7;
use App\Classes\Gifts\UpdateUserWhenSendGift;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Load Test — POST /api/gifts/v7/send-lucky-gift-combo
 *
 * Run:
 */
class SendLuckyGiftComboLoadTest extends TestCase
{
    // ─── Constants ────────────────────────────────────────────────────────────
    private const GIFT_ID     = 384;
    private const ROOM_ID     = 1174;   // toUid (room uid used as owner_id)
    private const NUM         = 1;
    private const USERS_COUNT = 100;
    private const REQUESTS_PER_USER = 2000; // 20 × 100 = 2000 request
    private const INITIAL_COINS = 10000;

    // ─── Properties ───────────────────────────────────────────────────────────
    private Gift   $gift;
    private ?Room  $room = null;
    private int    $ownerId;

    // ─── setUp ────────────────────────────────────────────────────────────────
    protected function setUp(): void
    {
        parent::setUp();

        // ── Gift ──────────────────────────────────────────────────────────────
        // Try to find the real gift first; create it if it doesn't exist.
        $this->gift = Gift::find(self::GIFT_ID)
            ?? Gift::factory()->create([
                'id'     => self::GIFT_ID,
                'type'   => 6,
                'price'  => 100,
                'enable' => 1,
                'name'   => 'Lucky Gift V7 Test',
                'e_name' => 'Lucky Gift V7 Test',
                'img'    => 'test.png',
            ]);

        // ── Room ──────────────────────────────────────────────────────────────
        // Try to find the real room first; create it via DB::table if it doesn't exist.
        // Room model does NOT use HasFactory, so we use DB::table()->insertOrIgnore().
        $this->room = Room::withoutAppends()->find(self::ROOM_ID);

        if (!$this->room) {
            // Create a room owner user first
            $roomOwner = User::factory()->create(['di' => 0]);

            DB::table('rooms')->insertOrIgnore([
                'id'          => self::ROOM_ID,
                'uid'         => $roomOwner->id,
                'type'        => 'audio',
                'room_status' => 1,
                'session'     => 0,
                'room_name'   => 'Test Room V7',
                'numid'       => self::ROOM_ID,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            $this->room = Room::withoutAppends()->find(self::ROOM_ID);
        }

        // ── owner_id ──────────────────────────────────────────────────────────
        // The service looks up the room by `uid` (owner_id field).
        // If the room has a uid, use it; otherwise fall back to the room's id.
        $this->ownerId = $this->room->uid ?? $this->room->id;

        // ── Core Wallets ──────────────────────────────────────────────────────
        // Ensure both core wallets exist so the service doesn't throw.
        foreach (['app_wallet', 'owner_wallet'] as $walletName) {
            CoreWallet::firstOrCreate(
                ['name' => $walletName],
                ['coins' => 10_000_000]
            );
        }

        // ── Settings ──────────────────────────────────────────────────────────
        settings()->set('stop_luckyGift', 0);
        settings()->set('room_boom', 0);

        // ── Middleware ────────────────────────────────────────────────────────
        // Disable all middleware that would interfere with testing:
        // - CheckCpu: CPU load check (not relevant in tests)
        // - AppFeatureEnable: feature flag check
        // - CheckLatestToken: token ordering check (fails with actingAs())
        // - UpdateLastSeen: DB write on every request
        // - GeneralBanMiddleware / UserBanMiddleware: ban checks
        $this->withoutMiddleware([
            \App\Http\Middleware\CheckCpu::class,
            \App\Http\Middleware\AppFeatureEnable::class,
            \App\Http\Middleware\CheckLatestToken::class,
            \App\Http\Middleware\UpdateLastSeen::class,
            \App\Http\Middleware\EncryptCookies::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
        ]);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TEST 1 — Load test: 100 users × 20 requests = 2000 total
    // Generates detailed HTML report with user balances
    // ─────────────────────────────────────────────────────────────────────────
    public function test_send_lucky_gift_combo_with_2000_requests(): void
    {
        $this->runLoadTest(self::USERS_COUNT, self::REQUESTS_PER_USER);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TEST 1b — Small test: 1 user × 20 requests for quick testing
    // ─────────────────────────────────────────────────────────────────────────
    public function test_single_user_20_requests(): void
    {
        $this->runLoadTest(1, 20);
    }

    /**
     * Run load test with specified users and requests per user
     */
    private function runLoadTest(int $userCount, int $requestsPerUser): void
    {
        $giftPrice = $this->gift->price ?? 100;
        
        // ── No Mock for FairLuckServiceV7 - Use REAL service for actual wallet balance tracking ─────────────────────────────────────────────────
        // This will interact with the real Unified Vault and track actual balance changes

        // ── Mock UpdateUserWhenSendGift ───────────────────────────────────────
        $mockUpdate = Mockery::mock(UpdateUserWhenSendGift::class);
        $mockUpdate->shouldReceive('updateUsers')->andReturn(true);
        $mockUpdate->shouldReceive('getSenderLevel')->andReturn(1);
        $this->app->instance(UpdateUserWhenSendGift::class, $mockUpdate);

        // ── Create {$userCount} users with 20000 coins each ───────────────────────────
        $users = User::factory()->count($userCount)->create([
            'di'                 => self::INITIAL_COINS,
            'total_diamond_send' => 0,
            'sub_sender_level'   => 0,
        ]);

        // Store initial state
        $userStats = [];
        foreach ($users as $user) {
            $userStats[$user->id] = [
                'id'               => $user->id,
                'initial_balance'  => self::INITIAL_COINS,
                'current_balance'  => self::INITIAL_COINS,
                'total_wins'       => 0,
                'total_losses'     => 0,
                'win_count'        => 0,
                'loss_count'       => 0,
                'multiplier_hits'  => [],
                'requests_sent'    => 0,
                'successful_reqs'  => 0,
                'failed_reqs'      => 0,
                'max_balance'      => self::INITIAL_COINS, // Track highest balance reached
            ];
        }

        // ── Get game wallet balance before test ──────────────────────────────
        // PoolManager uses TYPE_GLOBAL_VAULT, not TYPE_UNIFIED_VAULT
        $initialVaultBalance = \App\Models\FairLuckWallet::getRedisBalance(\App\Models\FairLuckWallet::TYPE_GLOBAL_VAULT);
        Log::info("[LoadTest] Initial vault balance (GLOBAL_VAULT): {$initialVaultBalance}");
        $allResults = [];

        foreach ($users as $user) {
            for ($i = 0; $i < $requestsPerUser; $i++) {
                $start = microtime(true);

                $response = $this->actingAs($user)
                    ->postJson('/api/gifts/v7/send-lucky-gift-combo', [
                        'id'       => self::GIFT_ID,
                        'toUid'    => (string) $this->ownerId,
                        'owner_id' => $this->ownerId,
                        'num'      => self::NUM,
                    ]);

                $elapsed = (int) round((microtime(true) - $start) * 1000);

                $httpStatus = $response->status();
                $apiStatus  = $response->json('success') ?? $response->json('status') ?? false;
                $data       = $response->json('data') ?? [];

                $isSuccess = ($httpStatus === 200 && $apiStatus == true);

                $userStats[$user->id]['requests_sent']++;

                if ($isSuccess) {
                    $userStats[$user->id]['successful_reqs']++;

                    // Deduct gift cost
                    $userStats[$user->id]['current_balance'] -= $giftPrice;
                    $userStats[$user->id]['total_losses'] += $giftPrice;

                    // Process combo array for wins
                    $combo = $data['combo'] ?? [];
                    
                    Log::info("[LoadTest] Response received", [
                        'user_id' => $user->id,
                        'combo_count' => count($combo),
                        'sample_combo' => $combo[0] ?? null,
                    ]);
                    
                    foreach ($combo as $item) {
                        $itemData = $item['data'] ?? null;
                        if ($itemData && ($item['status'] ?? 1) == 0) {
                            $winCoins = $itemData['win_coins'] ?? 0;
                            $isWin = $itemData['is_win'] ?? false;
                            
                            if ($isWin && $winCoins > 0) {
                                // Calculate multiplier: win_coins / (giftPrice * num)
                                $multiplier = $winCoins / ($giftPrice * self::NUM);
                                
                                Log::info("[LoadTest] Win detected", [
                                    'user_id' => $user->id,
                                    'win_coins' => $winCoins,
                                    'calculated_multiplier' => $multiplier,
                                    'winner_comment' => $itemData['winner_comment'] ?? null,
                                    'comment_message' => $itemData['comment_message'] ?? null,
                                    'giftPrice' => $giftPrice,
                                    'num' => self::NUM,
                                ]);
                                
                                $userStats[$user->id]['current_balance'] += $winCoins;
                                
                                // Track max balance reached
                                if ($userStats[$user->id]['current_balance'] > $userStats[$user->id]['max_balance']) {
                                    $userStats[$user->id]['max_balance'] = $userStats[$user->id]['current_balance'];
                                }
                                
                                $userStats[$user->id]['total_wins'] += $winCoins;
                                $userStats[$user->id]['win_count']++;

                                if ($multiplier > 0) {
                                    // Round to nearest valid multiplier (5, 10, 20, 50, 70, 100, 250, 500, 1000)
                                    $multKey = $this->roundToValidMultiplier((int) $multiplier);
                                    $userStats[$user->id]['multiplier_hits'][$multKey] =
                                        ($userStats[$user->id]['multiplier_hits'][$multKey] ?? 0) + 1;
                                }
                            } else {
                                $userStats[$user->id]['loss_count']++;
                            }
                        }
                    }
                    
                    // If no combo data, count as loss
                    if (empty($combo)) {
                        $userStats[$user->id]['loss_count']++;
                    }
                } else {
                    $userStats[$user->id]['failed_reqs']++;
                    Log::warning("[LoadTest] Failed request", [
                        'user_id' => $user->id,
                        'http_status' => $httpStatus,
                        'api_status' => $apiStatus,
                    ]);
                }

                $allResults[] = [
                    'user_id'     => $user->id,
                    'http'        => $httpStatus,
                    'success'     => $isSuccess,
                    'time_ms'     => $elapsed,
                ];
            }
        }

        // ── Get game wallet balance after test ───────────────────────────────
        // Read from database directly (not Redis) to get actual committed balance
        $wallet = \App\Models\FairLuckWallet::where('wallet_type', \App\Models\FairLuckWallet::TYPE_GLOBAL_VAULT)->first();
        $finalVaultBalance = $wallet ? $wallet->balance : 0;
        $vaultChange = $finalVaultBalance - $initialVaultBalance;
        Log::info("[LoadTest] Final vault balance from DB: {$finalVaultBalance}, Change: {$vaultChange}");

        // ── Generate HTML Report ───────────────────────────────────────────────
        $this->generateHtmlReport($userStats, $allResults, $initialVaultBalance, $finalVaultBalance);

        // ── Assertions ────────────────────────────────────────────────────────
        $totalSuccess = array_sum(array_column($userStats, 'successful_reqs'));
        $minExpected = (int) ($userCount * $requestsPerUser * 0.9); // 90% success rate
        $this->assertGreaterThanOrEqual(
            $minExpected,
            $totalSuccess,
            "Success rate too low: only {$totalSuccess}/" . ($userCount * $requestsPerUser) . " succeeded."
        );
    }

    /**
     * Generate detailed HTML report for the load test
     */
    private function generateHtmlReport(array $userStats, array $allResults, int $initialVaultBalance = 0, int $finalVaultBalance = 0): void
    {
        $totalRequests = count($allResults);
        $totalSuccess = array_sum(array_column($userStats, 'successful_reqs'));
        $totalFailed = array_sum(array_column($userStats, 'failed_reqs'));

        $totalInitial = count($userStats) * self::INITIAL_COINS;
        $totalFinal = array_sum(array_column($userStats, 'current_balance'));
        $totalWins = array_sum(array_column($userStats, 'total_wins'));
        $totalLosses = array_sum(array_column($userStats, 'total_losses'));

        // Aggregate multiplier hits across all users
        $allMultipliers = [];
        foreach ($userStats as $stats) {
            foreach ($stats['multiplier_hits'] as $mult => $count) {
                $allMultipliers[$mult] = ($allMultipliers[$mult] ?? 0) + $count;
            }
        }
        ksort($allMultipliers);

        $responseTimes = array_column($allResults, 'time_ms');
        $avgTime = count($responseTimes) > 0 ? round(array_sum($responseTimes) / count($responseTimes), 2) : 0;
        $minTime = count($responseTimes) > 0 ? min($responseTimes) : 0;
        $maxTime = count($responseTimes) > 0 ? max($responseTimes) : 0;

        $html = $this->buildReportHtml([
            'userStats' => $userStats,
            'totalRequests' => $totalRequests,
            'totalSuccess' => $totalSuccess,
            'totalFailed' => $totalFailed,
            'totalInitial' => $totalInitial,
            'totalFinal' => $totalFinal,
            'totalWins' => $totalWins,
            'totalLosses' => $totalLosses,
            'allMultipliers' => $allMultipliers,
            'avgTime' => $avgTime,
            'minTime' => $minTime,
            'maxTime' => $maxTime,
            'initialVaultBalance' => $initialVaultBalance,
            'finalVaultBalance' => $finalVaultBalance,
        ]);

        // Save report
        $reportsDir = public_path('reports');
        if (!is_dir($reportsDir)) {
            mkdir($reportsDir, 0755, true);
        }

        $filename = 'load_test_report_' . now()->format('Y-m-d_H-i-s') . '.html';
        $filepath = $reportsDir . '/' . $filename;
        file_put_contents($filepath, $html);

        echo "\n📄 HTML Report generated: {$filepath}\n";
    }

    /**
     * Build the HTML report content
     */
    private function buildReportHtml(array $data): string
    {
        extract($data);

        $vaultChange = ($finalVaultBalance ?? 0) - ($initialVaultBalance ?? 0);
        $vaultChangeClass = $vaultChange >= 0 ? 'profit' : 'loss';

        $userRows = '';
        $rank = 1;
        // Sort by current balance descending
        uasort($userStats, fn($a, $b) => $b['current_balance'] <=> $a['current_balance']);

        foreach ($userStats as $uid => $stats) {
            $netProfit = $stats['current_balance'] - $stats['initial_balance'];
            $netClass = $netProfit >= 0 ? 'profit' : 'loss';
            $multiplierSummary = [];
            foreach ($stats['multiplier_hits'] as $m => $c) {
                $multiplierSummary[] = "{$m}x ({$c})";
            }
            $multiplierStr = implode(', ', $multiplierSummary) ?: '-';

            $userRows .= "<tr>
                <td>{$rank}</td>
                <td>#{$uid}</td>
                <td>" . number_format($stats['initial_balance']) . "</td>
                <td>" . number_format($stats['current_balance']) . "</td>
                <td>" . number_format($stats['max_balance']) . "</td>
                <td class='{$netClass}'>" . ($netProfit >= 0 ? '+' : '') . number_format($netProfit) . "</td>
                <td>" . number_format($stats['total_wins']) . "</td>
                <td>" . number_format($stats['total_losses']) . "</td>
                <td>{$stats['win_count']}</td>
                <td>{$stats['loss_count']}</td>
                <td>{$multiplierStr}</td>
                <td>{$stats['successful_reqs']}/{$stats['requests_sent']}</td>
            </tr>";
            $rank++;
        }

        $multiplierRows = '';
        foreach ($allMultipliers as $mult => $count) {
            $totalWinAmount = 0;
            foreach ($userStats as $stats) {
                if (isset($stats['multiplier_hits'][$mult])) {
                    $totalWinAmount += ($giftPrice ?? 100) * $mult * $stats['multiplier_hits'][$mult];
                }
            }
            $multiplierRows .= "<tr>
                <td>{$mult}x</td>
                <td>{$count}</td>
                <td>" . number_format($totalWinAmount) . "</td>
            </tr>";
        }

        // Build per-user multiplier distribution table (actual V7 multipliers: 5, 10, 20, 50, 70, 100, 250, 500, 1000)
        $standardMultipliers = [5, 10, 20, 50, 70, 100, 250, 500, 1000];
        $userMultiplierRows = '';
        $rank = 1;
        foreach ($userStats as $uid => $stats) {
            $row = "<tr>
                <td>{$rank}</td>
                <td>#{$uid}</td>";
            $userTotalWins = 0;
            foreach ($standardMultipliers as $mult) {
                $count = $stats['multiplier_hits'][$mult] ?? 0;
                $userTotalWins += $count;
                $highlight = $count > 0 ? " style='background:rgba(74,222,128,0.2);font-weight:bold;'" : '';
                $row .= "<td{$highlight}>{$count}</td>";
            }
            $row .= "<td class='bg-primary text-white fw-bold'>{$userTotalWins}</td>";
            
            // Add max multiplier hit
            $maxMultHit = !empty($stats['multiplier_hits']) ? max(array_keys($stats['multiplier_hits'])) : 0;
            $row .= "<td>" . ($maxMultHit > 0 ? $maxMultHit . 'x' : '-') . "</td>";
            $row .= "</tr>";
            $userMultiplierRows .= $row;
            $rank++;
        }

        $netSystem = $totalFinal - $totalInitial;
        $netSystemClass = $netSystem >= 0 ? 'profit' : 'loss';

        return "<!DOCTYPE html>
<html lang='ar' dir='rtl'>
<head>
    <meta charset='UTF-8'>
    <title>تقرير اختبار الأحمال - Lucky Gift</title>
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            color: #fff;
            padding: 20px;
            min-height: 100vh;
        }
        .container { max-width: 1400px; margin: 0 auto; }
        h1 {
            text-align: center;
            color: #ffd700;
            font-size: 2.5em;
            margin-bottom: 10px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.5);
        }
        .subtitle {
            text-align: center;
            color: #888;
            margin-bottom: 30px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: rgba(255,255,255,0.05);
            border-radius: 15px;
            padding: 20px;
            border: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }
        .stat-card h3 {
            color: #888;
            font-size: 0.9em;
            margin-bottom: 10px;
        }
        .stat-card .value {
            font-size: 2em;
            font-weight: bold;
            color: #fff;
        }
        .stat-card.profit .value { color: #4ade80; }
        .stat-card.loss .value { color: #f87171; }
        .section {
            background: rgba(255,255,255,0.05);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
            border: 1px solid rgba(255,255,255,0.1);
        }
        .section h2 {
            color: #ffd700;
            margin-bottom: 15px;
            font-size: 1.5em;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9em;
        }
        th, td {
            padding: 12px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        th {
            background: rgba(255,215,0,0.1);
            color: #ffd700;
            font-weight: 600;
        }
        tr:hover { background: rgba(255,255,255,0.03); }
        .profit { color: #4ade80; font-weight: bold; }
        .loss { color: #f87171; font-weight: bold; }
        .multiplier-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: bold;
        }
        .multiplier-0 { background: #374151; color: #9ca3af; }
        .multiplier-1 { background: #065f46; color: #6ee7b7; }
        .multiplier-2 { background: #1e40af; color: #93c5fd; }
        .multiplier-3 { background: #5b21b6; color: #c4b5fd; }
        .multiplier-5 { background: #9a3412; color: #fdba74; }
        .multiplier-10 { background: #be123c; color: #fda4af; }
        .multiplier-high { background: #ffd700; color: #1a1a2e; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🎁 تقرير اختبار الأحمال - Lucky Gift</h1>
        <p class='subtitle'>تم إنشاء التقرير: " . now()->format('Y-m-d H:i:s') . "</p>

        <div class='stats-grid'>
            <div class='stat-card'>
                <h3>إجمالي الطلبات</h3>
                <div class='value'>" . number_format($totalRequests) . "</div>
            </div>
            <div class='stat-card profit'>
                <h3>الطلبات الناجحة</h3>
                <div class='value'>" . number_format($totalSuccess) . "</div>
            </div>
            <div class='stat-card loss'>
                <h3>الطلبات الفاشلة</h3>
                <div class='value'>" . number_format($totalFailed) . "</div>
            </div>
            <div class='stat-card'>
                <h3>متوسط وقت الاستجابة</h3>
                <div class='value'>{$avgTime}ms</div>
            </div>
            <div class='stat-card'>
                <h3>الرصيد الأولي (كل المستخدمين)</h3>
                <div class='value'>" . number_format($totalInitial) . "</div>
            </div>
            <div class='stat-card'>
                <h3>الرصيد النهائي (كل المستخدمين)</h3>
                <div class='value'>" . number_format($totalFinal) . "</div>
            </div>
            <div class='stat-card profit'>
                <h3>إجمالي الأرباح</h3>
                <div class='value'>+" . number_format($totalWins) . "</div>
            </div>
            <div class='stat-card loss'>
                <h3>إجمالي الخسائر</h3>
                <div class='value'>-" . number_format($totalLosses) . "</div>
            </div>
            <div class='stat-card {$netSystemClass}'>
                <h3>صافي النظام</h3>
                <div class='value'>" . ($netSystem >= 0 ? '+' : '') . number_format($netSystem) . "</div>
            </div>
        </div>

        <!-- رصيد محفظة اللعب -->
        <div class='section'>
            <h2>💰 رصيد محفظة اللعب (Unified Vault)</h2>
            <div class='stats-grid'>
                <div class='stat-card'>
                    <h3>الرصيد قبل الاختبار</h3>
                    <div class='value'>" . number_format($initialVaultBalance) . "</div>
                </div>
                <div class='stat-card'>
                    <h3>الرصيد بعد الاختبار</h3>
                    <div class='value'>" . number_format($finalVaultBalance) . "</div>
                </div>
                <div class='stat-card {$vaultChangeClass}'>
                    <h3>التغيير</h3>
                    <div class='value'>" . ($vaultChange >= 0 ? '+' : '') . number_format($vaultChange) . "</div>
                </div>
            </div>
        </div>

        <div class='section'>
            <h2>📊 إحصائيات المضاعفات</h2>
            <table>
                <thead>
                    <tr>
                        <th>المضاعف</th>
                        <th>عدد المرات</th>
                        <th>إجمالي المكاسب</th>
                    </tr>
                </thead>
                <tbody>
                    {$multiplierRows}
                </tbody>
            </table>
        </div>

        <div class='section'>
            <h2>🎯 توزيع المضاعفات لكل مستخدم</h2>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>المستخدم</th>
                        <th>5x</th>
                        <th>10x</th>
                        <th>20x</th>
                        <th>50x</th>
                        <th>70x</th>
                        <th>100x</th>
                        <th>250x</th>
                        <th>500x</th>
                        <th>1000x</th>
                        <th class='bg-primary'>المجموع</th>
                        <th>أعلى مضاعف</th>
                    </tr>
                </thead>
                <tbody>
                    {$userMultiplierRows}
                </tbody>
            </table>
        </div>

        <div class='section'>
            <h2>🏆 ترتيب المستخدمين حسب الرصيد النهائي</h2>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>المستخدم</th>
                        <th>الرصيد الأولي</th>
                        <th>الرصيد النهائي</th>
                        <th>أعلى رصيد وصله</th>
                        <th>صافي الربح/الخسارة</th>
                        <th>إجمالي الأرباح</th>
                        <th>إجمالي الخسائر</th>
                        <th>مرات الفوز</th>
                        <th>مرات الخسارة</th>
                        <th>المضاعفات المحققة</th>
                        <th>نسبة النجاح</th>
                    </tr>
                </thead>
                <tbody>
                    {$userRows}
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>";
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TEST 2 — stop_luckyGift flag blocks all requests
    // ─────────────────────────────────────────────────────────────────────────
    public function test_stop_flag_blocks_all_requests(): void
    {
        settings()->set('stop_luckyGift', 1);

        $user = User::factory()->create(['di' => 999_999]);

        $response = $this->actingAs($user)
            ->postJson('/api/gifts/v7/send-lucky-gift-combo', [
                'id'       => self::GIFT_ID,
                'toUid'    => (string) $this->ownerId,
                'owner_id' => $this->ownerId,
                'num'      => self::NUM,
            ]);

        // Common::apiResponse returns 'success' key (not 'status')
        // When stop_luckyGift=1, it returns success=false (0)
        $successValue = $response->json('success');
        $this->assertFalse(
            (bool) $successValue,
            'Expected success=false when stop_luckyGift=1, got: ' . json_encode($successValue)
        );

        // Reset for other tests
        settings()->set('stop_luckyGift', 0);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TEST 3 — Validation rejects missing required fields
    // ─────────────────────────────────────────────────────────────────────────
    public function test_validation_rejects_missing_fields(): void
    {
        $user = User::factory()->create(['di' => 999_999]);

        // Send without 'id' and 'toUid'
        $response = $this->actingAs($user)
            ->postJson('/api/gifts/v7/send-lucky-gift-combo', [
                'num' => self::NUM,
            ]);

        // Common::apiResponse returns 'success' key (false) on validation error
        $successValue = $response->json('success');
        $this->assertFalse(
            (bool) $successValue,
            'Expected success=false for missing fields, got: ' . json_encode($successValue)
        );

        // The response data should contain validation errors
        $data = $response->json('data');
        $this->assertNotNull($data, 'Expected validation errors in response data');

        // Ensure at least one of the missing fields is reported
        $dataStr = json_encode($data);
        $this->assertTrue(
            str_contains($dataStr, 'id') || str_contains($dataStr, 'toUid'),
            "Expected validation errors for 'id' or 'toUid', got: {$dataStr}"
        );
    }

    // ─────────────────────────────────────────────────────────────────────────
    // TEST 4 — Unauthenticated request returns 401
    // ─────────────────────────────────────────────────────────────────────────
    public function test_unauthenticated_request_returns_401(): void
    {
        // Re-enable auth middleware for this test only
        $this->withMiddleware();

        $response = $this->postJson('/api/gifts/v7/send-lucky-gift-combo', [
            'id'       => self::GIFT_ID,
            'toUid'    => (string) $this->ownerId,
            'owner_id' => $this->ownerId,
            'num'      => self::NUM,
        ]);

        $response->assertStatus(401);
    }

    /**
     * Round calculated multiplier to nearest valid V7 multiplier
     * Valid multipliers: 5, 10, 20, 50, 70, 100, 250, 500, 1000
     */
    private function roundToValidMultiplier(int $calculatedMult): int
    {
        $validMultipliers = [5, 10, 20, 50, 70, 100, 250, 500, 1000];
        
        // If already valid, return as-is
        if (in_array($calculatedMult, $validMultipliers)) {
            return $calculatedMult;
        }
        
        // Find closest valid multiplier
        $closest = $validMultipliers[0];
        $minDiff = abs($calculatedMult - $closest);
        
        foreach ($validMultipliers as $valid) {
            $diff = abs($calculatedMult - $valid);
            if ($diff < $minDiff) {
                $minDiff = $diff;
                $closest = $valid;
            }
        }
        
        return $closest;
    }
}
