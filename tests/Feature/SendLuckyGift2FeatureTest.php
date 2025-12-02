<?php

namespace Tests\Feature;

use App\Helpers\LogHelper;
use Tests\TestCase;
use Mockery;
use App\Models\User;
use App\Models\Gift;
use App\Models\MonthlyDiamondReceive;
use App\Services\Gifts\LuckyGiftService;
use App\Services\Gifts\UpdateUserWhenSendGift;
use Illuminate\Support\Facades\DB;

class SendLuckyGift2FeatureTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_send_lucky_gift_monthly_report()
    {
        $this->withoutMiddleware([
            \App\Http\Middleware\CheckCpu::class,
            \App\Http\Middleware\AppFeatureEnable::class,
        ]);

        settings()->set('stop_luckyGift', 0);

        // إنشاء مستخدمين
        $sender = User::factory()->create([ 'di' => 500]);
        $receiver = User::factory()->create([ 'di' => 300]);

        // إنشاء هدية ثابتة
         $gift = Gift::where('type',6)->first();

        $senderBefore = $sender->coins;
        $receiverBefore = $receiver->coins;

        $giftCost = $gift->price;
        $receiverGain = 50;

        // Mock LuckyGiftService
        $mockGift = Mockery::mock(LuckyGiftService::class);
        $mockGift->shouldReceive('sendCombo')
                 ->andReturn([
                     'success' => true,
                     'data' => [
                         'combo' => [
                             ['data' => ['win_coins' => $receiverGain]]
                         ]
                     ]
                 ]);
        $this->app->instance(LuckyGiftService::class, $mockGift);

        // Mock UpdateUserWhenSendGift
        $mockUpdate = Mockery::mock(UpdateUserWhenSendGift::class);
        $mockUpdate->shouldReceive('update')->andReturnTrue();
        $this->app->instance(UpdateUserWhenSendGift::class, $mockUpdate);

        // إرسال الطلب داخليًا
      $response = $this->actingAs($sender, 'sanctum')
                 ->postJson('/api/gifts/send-lucky-gift-combo', [
                     'id' => $gift->id,
                     'toUid' => $receiver->id,
                     'num' => 1,
                 ]);
        LogHelper::info('Lucky Gift Monthly response', $response->json());

        $response->assertStatus(200)
                 ->assertJson(['success' => true]);

        // تحديث الرصيد بعد الهدية
        $sender->refresh();
        $receiver->refresh();

        $expectedSender = $senderBefore - $giftCost;
        $expectedReceiver = $receiverBefore + $receiverGain;

        $this->assertEquals($expectedSender, $sender->coins);

        // تسجيل / تحديث diamonds الشهرية
        $month = now()->month;
        $year  = now()->year;

        $monthly = MonthlyDiamondReceive::firstOrCreate(
            ['user_id' => $receiver->id, 'month' => $month, 'year' => $year],
            ['monthly_diamond_received' => 0, 'old_diamond' => $receiverBefore]
        );

        $monthly->monthly_diamond_received += $receiverGain;
        $monthly->save();

        $monthly->refresh();

        $this->assertEquals($expectedReceiver, $receiverBefore + $receiverGain);
        $this->assertEquals($monthly->monthly_diamond_received, $receiverGain);

        // تقرير شهري
        $report = [
            'sender_before' => $senderBefore,
            'sender_after' => $sender->coins,
            'receiver_before' => $receiverBefore,
            'receiver_after' => $receiver->coins,
            'monthly_received' => $monthly->monthly_diamond_received,
            'gift_value' => $giftCost,
            'received_value' => $receiverGain,
        ];

        \Log::info('Lucky Gift Monthly Report', $report);
    }
}
