<?php

namespace Tests\Feature;

use App\Models\Room;
use Tests\TestCase;
use Mockery;
use App\Models\User;
use App\Models\Gift;
use App\Models\MonthlyDiamondReceive;
use App\Services\Gifts\LuckyGiftService;
use App\Classes\Gifts\UpdateUserWhenSendGift;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class SendLuckyGift2FeatureTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_send_lucky_gift_monthly_report()
    {

                // Log::info(' start test_send_lucky_gift_monthly_report', []);

        // 1. تفعيل عرض الأخطاء
        $this->withoutExceptionHandling();

        // 2. تفعيل query log لرؤية الاستعلامات
        DB::enableQueryLog();

        // 3. إيقاف middleware مؤقتاً
        $this->withoutMiddleware([
            \App\Http\Middleware\CheckCpu::class,
            \App\Http\Middleware\AppFeatureEnable::class,
            \App\Http\Middleware\EncryptCookies::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
        ]);

        settings()->set('stop_luckyGift', 0);

        // إنشاء مستخدمين
        $user = User::factory()->create(['di' => 2000]);
        $receiver = User::factory()->create(['di' => 300]);

        // إنشاء هدية ثابتة لضمان توفر البيانات المطلوبة للاختبار
        $gift = Gift::create([
            'name' => 'Lucky Test Gift',
            'e_name' => 'Lucky Test Gift',
            'type' => 6,
            'vip_level' => 0,
            'is_play' => 0,
            'price' => 100,
            'img' => 'gift.png',
            'show_img' => 'gift_show.png',
            'show_img2' => 'gift_show_alt.png',
            'enable' => 1,
        ]);

        $room = Room::where('type', 'audio')->first();
        if (! $room) {
            $roomData = [
                'numid' => Str::random(6),
                'uid' => $receiver->id,
                'room_status' => '1',
                'room_name' => 'Integration Room',
                'room_cover' => null,
                'room_intro' => 'Integration room intro',
                'room_pass' => null,
                'room_class' => '5',
                'room_type' => '16',
                'room_welcome' => 'Welcome',
                'room_admin' => null,
                'room_visitor' => '',
                'room_speak' => '',
                'room_sound' => '',
                'room_black' => '',
                'week_star' => 2,
                'ranking' => 1,
                'is_popular' => 2,
                'secret_chat' => 2,
                'is_top' => 2,
                'sort' => 1,
                'room_background' => null,
                'microphone' => '0,0,0,0,0,0,0,0,0,0',
                'super_uid' => 2,
                'is_afk' => 0,
                'hot' => 0,
                'room_judge' => null,
                'is_prohibit_sound' => '0,0,0,0,0,0,0,0,0',
                'openid' => null,
                'commission_proportion' => null,
                'fresh_time' => null,
                'start_hour' => 0,
                'end_hour' => 0,
                'is_recommended' => 2,
                'play_num' => 0,
                'free_mic' => 0,
            ];

            if (Schema::hasColumn('rooms', 'type')) {
                $roomData['type'] = 'audio';
            }

            if (Schema::hasColumn('rooms', 'session')) {
                $roomData['session'] = 0;
            }

            if (Schema::hasColumn('rooms', 'total_diamond')) {
                $roomData['total_diamond'] = 0;
            }

            if (Schema::hasColumn('rooms', 'level')) {
                $roomData['level'] = 1;
            }

            if (Schema::hasColumn('rooms', 'level_id')) {
                $roomData['level_id'] = 1;
            }

            if (Schema::hasColumn('rooms', 'charizma_status')) {
                $roomData['charizma_status'] = 0;
            }

            $room = Room::create($roomData);
        }
        $senderBefore = $user->di;
        $receiverBefore = $receiver->di;

        $giftCost = $gift->price;
        $receiverGain = 50;

        // Mock LuckyGiftService
        $mockGift = Mockery::mock(LuckyGiftService::class);
        $mockGift->shouldReceive('sendLuckyGift2')
                 ->once()
                 ->withArgs(function (array $payload, User $authUser, UpdateUserWhenSendGift $updateUserWhenSendGift) use ($gift, $receiver, $user) {
                     return (int) $payload['id'] === $gift->id
                         && (int) $payload['toUid'] === $receiver->id
                         && $authUser->is($user)
                         && $updateUserWhenSendGift instanceof UpdateUserWhenSendGift;
                 })
                 ->andReturnUsing(function (array $payload, User $authUser, UpdateUserWhenSendGift $updateUserWhenSendGift) use ($giftCost, $receiverGain, $receiver) {
                     $authUser->di -= $giftCost;
                     $authUser->save();

                     $receiver->increment('di', $receiverGain);

                     $updateUserWhenSendGift->updateUsers($receiverGain, [$receiver->id]);

                     return [
                         'combo' => [
                             ['data' => ['win_coins' => $receiverGain]]
                         ]
                     ];
                 });
        $this->app->instance(LuckyGiftService::class, $mockGift);

        // Mock UpdateUserWhenSendGift
        $mockUpdate = Mockery::mock(UpdateUserWhenSendGift::class);
        $mockUpdate->shouldReceive('updateUsers')
                   ->once()
                   ->with($receiverGain, [$receiver->id])
                   ->andReturnTrue();
        $this->app->instance(UpdateUserWhenSendGift::class, $mockUpdate);

        // إرسال الطلب مع التحقق من التوثيق
        $token = $user->createToken('test-token')->plainTextToken;

        // Log::info('Sender ID:', ['id' => $user->id]);
        // Log::info('Receiver ID:', ['id' => $user->id]);
        // Log::info('Gift ID:', ['id' => $gift->id]);

        try {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ])->postJson('/api/gifts/send-lucky-gift-combo', [
                'id' => (int) $gift->id,
                'toUid' => (int) $receiver->id,
                'room_id' => $room->id,
                'num' => 1,
            ]);

            // Log::info('Database Queries:', DB::getQueryLog());

            // Log::info('Response Status:', ['status' => $response->status()]);
            // Log::info('Response Headers:', $response->headers->all());

            if ($response->status() !== 200) {
                Log::error('Response Content:', ['content' => $response->getContent()]);
                Log::error('Response JSON:', ['json' => $response->json() ?? 'No JSON']);
            }

            $responseData = $response->json();
            // LogHelper::info('Lucky Gift Monthly response', $responseData ?? []);
            // التحقق من الرد
            $response->assertStatus(200)
                     ->assertJson([
                         'success' => true,
                         'message' => __('api_responses.success')
                     ]);

        } catch (\Exception $e) {
            // Log::error('Test Exception:', [
            //     'message' => $e->getMessage(),
            //     'file' => $e->getFile(),
            //     'line' => $e->getLine(),
            //     'trace' => $e->getTraceAsString()
            // ]);
            throw $e;
        }

        // تحديث الرصيد بعد الهدية
        $user->refresh();
        $receiver->refresh();

        $expectedSender = $senderBefore - $giftCost;
        $expectedReceiver = $receiverBefore + $receiverGain;

        $this->assertEquals($expectedSender, $user->di,
            "Sender coins mismatch. Expected: {$expectedSender}, Actual: {$user->di}");

        // تسجيل / تحديث diamonds الشهرية
        $month = now()->month;
        $year  = now()->year;

        $monthly = MonthlyDiamondReceive::firstOrCreate(
            [
                'user_id' => $receiver->id,
                'month' => $month,
                'year' => $year
            ],
            [
                'monthly_diamond_received' => 0,
                'old_diamond' => $receiverBefore
            ]
        );

        $monthly->monthly_diamond_received += $receiverGain;
        $monthly->save();
        $monthly->refresh();

        $this->assertEquals($receiverBefore + $receiverGain, $receiver->di,
            "Receiver coins mismatch. Expected: " . ($receiverBefore + $receiverGain) . ", Actual: {$receiver->di}");

        $this->assertEquals($receiverGain, $monthly->monthly_diamond_received,
            "Monthly diamond received mismatch. Expected: {$receiverGain}, Actual: {$monthly->monthly_diamond_received}");

        $report = [
            'sender_before' => $senderBefore,
            'sender_after' => $user->di,
            'receiver_before' => $receiverBefore,
            'receiver_after' => $receiver->di,
            'monthly_received' => $monthly->monthly_diamond_received,
            'gift_value' => $giftCost,
            'received_value' => $receiverGain,
        ];

    }
}
