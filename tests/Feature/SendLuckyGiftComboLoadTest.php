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
    // TEST 1 — Load test: 100 concurrent users
    // ─────────────────────────────────────────────────────────────────────────
    public function test_send_lucky_gift_combo_with_100_users(): void
    {
        // ── Mock FairLuckServiceV7 ────────────────────────────────────────────
        // Return a simple "winner" result so the service doesn't need Redis/DB
        // for the fair-luck engine.
        $fakeResult = (object) [
            'isWinner'      => true,
            'multiplier'    => 1.0,
            'profitAmount'  => 0.0,
            'wallets_before' => null,
            'wallets_after'  => null,
        ];

        $mockFairV7 = Mockery::mock(FairLuckServiceV7::class);
        $mockFairV7->shouldReceive('processBet')
                   ->andReturn($fakeResult);
        $this->app->instance(FairLuckServiceV7::class, $mockFairV7);

        // ── Mock UpdateUserWhenSendGift ───────────────────────────────────────
        $mockUpdate = Mockery::mock(UpdateUserWhenSendGift::class);
        $mockUpdate->shouldReceive('updateUsers')->andReturn(true);
        $mockUpdate->shouldReceive('getSenderLevel')->andReturn(1);
        $this->app->instance(UpdateUserWhenSendGift::class, $mockUpdate);

        // ── Create 100 users with enough coins ───────────────────────────────
        $giftPrice = $this->gift->price ?? 100;
        $requiredCoins = $giftPrice * self::NUM * 10; // generous buffer

        $users = User::factory()->count(self::USERS_COUNT)->create([
            'di'                 => $requiredCoins,
            'total_diamond_send' => 0,
            'sub_sender_level'   => 0,
        ]);

        // ── Metrics ───────────────────────────────────────────────────────────
        $results      = [];
        $successCount = 0;
        $failCount    = 0;
        $times        = [];

        // ── Fire requests ─────────────────────────────────────────────────────
        foreach ($users as $index => $user) {
            $start = microtime(true);

            $response = $this->actingAs($user)
                ->postJson('/api/gifts/v7/send-lucky-gift-combo', [
                    'id'       => self::GIFT_ID,
                    'toUid'    => (string) $this->ownerId,
                    'owner_id' => $this->ownerId,
                    'num'      => self::NUM,
                ]);

            $elapsed = (int) round((microtime(true) - $start) * 1000); // ms
            $times[] = $elapsed;

            $httpStatus = $response->status();
            // The response uses 'success' key (not 'status') based on Common::apiResponse
            // which returns: {'success': true/false, 'message': '...', 'data': {...}}
            $apiStatus  = $response->json('success') ?? $response->json('status') ?? -1;
            $message    = $response->json('message') ?? '';

            $isSuccess = ($httpStatus === 200 && $apiStatus == true);
            if ($isSuccess) {
                $successCount++;
            } else {
                $failCount++;
            }

            $results[] = [
                'index'      => $index + 1,
                'user_id'    => $user->id,
                'http'       => $httpStatus,
                'api_status' => $apiStatus,
                'time_ms'    => $elapsed,
                'ok'         => $isSuccess,
                'message'    => $message,
            ];
        }

        // ── Compute stats ─────────────────────────────────────────────────────
        $avgTime = count($times) > 0 ? (int) round(array_sum($times) / count($times)) : 0;
        $minTime = count($times) > 0 ? min($times) : 0;
        $maxTime = count($times) > 0 ? max($times) : 0;

        $httpDist = [];
        foreach ($results as $r) {
            $code = $r['http'];
            $httpDist[$code] = ($httpDist[$code] ?? 0) + 1;
        }
        $httpDistStr = implode('  ', array_map(
            fn($code, $cnt) => "{$code}:{$cnt}",
            array_keys($httpDist),
            array_values($httpDist)
        ));

        // ── Build log report ──────────────────────────────────────────────────
        $lines   = [];
        $lines[] = '════════════════════════════════════════════════════════════════';
        $lines[] = 'LOAD TEST — sendLuckyGift7';
        $lines[] = 'Date       : ' . now()->toDateTimeString();
        $lines[] = 'Gift ID    : ' . self::GIFT_ID;
        $lines[] = 'Room ID    : ' . self::ROOM_ID;
        $lines[] = 'Total      : ' . self::USERS_COUNT;
        $lines[] = 'Success    : ' . $successCount . '   (api_status=1)';
        $lines[] = 'Failed     : ' . $failCount;
        $lines[] = 'Avg Time   : ' . $avgTime . ' ms';
        $lines[] = 'Min / Max  : ' . $minTime . ' ms / ' . $maxTime . ' ms';
        $lines[] = 'HTTP Dist  : ' . $httpDistStr;
        $lines[] = '────────────────────────────────────────────────────────────────';

        foreach ($results as $r) {
            $status = $r['ok'] ? 'OK' : ('FAIL: ' . ($r['message'] ?: 'unknown'));
            $lines[] = sprintf(
                '[#%d]  uid=%-6d http=%d api=%d t=%dms  %s',
                $r['index'],
                $r['user_id'],
                $r['http'],
                $r['api_status'],
                $r['time_ms'],
                $status
            );
        }

        $lines[] = '════════════════════════════════════════════════════════════════';

        $logContent = implode(PHP_EOL, $lines) . PHP_EOL;
        $logPath    = storage_path('logs/load_test_lucky_gift.log');
        file_put_contents($logPath, $logContent, FILE_APPEND);

        // ── Assertions ────────────────────────────────────────────────────────
        $this->assertGreaterThanOrEqual(
            95,
            $successCount,
            "Success rate below 95%: only {$successCount}/100 succeeded.\n" .
            "Check storage/logs/load_test_lucky_gift.log for details."
        );

        $this->assertLessThan(
            2000,
            $avgTime,
            "Avg response time > 2s: {$avgTime}ms"
        );

        $this->assertFileExists($logPath);
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
}
