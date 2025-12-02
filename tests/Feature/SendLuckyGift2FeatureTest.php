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
use Illuminate\Support\Facades\Log;

class SendLuckyGift2FeatureTest extends TestCase
{
    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_send_lucky_gift_monthly_report()
    {
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
        $sender = User::factory()->create([ 'di' => 500]);
        $receiver = User::factory()->create([ 'di' => 300]);

        // إنشاء هدية ثابتة
         $gift = Gift::where('type',6)->first();

        $senderBefore = $sender->coins;
        $receiverBefore = $receiver->coins;

        $giftCost = $gift->price;
        $receiverGain = 50;

        // Mock LuckyGiftService
        $mockGift = Mockery::mock(LuckyGiftService::class)->makePartial();
        $mockGift->shouldReceive('sendCombo')
                 ->once()
                 ->with(Mockery::any(), Mockery::any(), Mockery::any())
                 ->andReturn([
                     'success' => true,
                     'message' => 'Gift sent successfully',
                     'data' => [
                         'combo' => [
                             ['data' => ['win_coins' => $receiverGain]]
                         ]
                     ]
                 ]);
        $this->app->instance(LuckyGiftService::class, $mockGift);

        // Mock UpdateUserWhenSendGift
        $mockUpdate = Mockery::mock(UpdateUserWhenSendGift::class);
        $mockUpdate->shouldReceive('update')
                   ->once()
                   ->andReturnTrue();
        $this->app->instance(UpdateUserWhenSendGift::class, $mockUpdate);

        // إرسال الطلب مع التحقق من التوثيق
        $token = $sender->createToken('test-token')->plainTextToken;
        
        Log::info('Sender ID:', ['id' => $sender->id]);
        Log::info('Receiver ID:', ['id' => $receiver->id]);
        Log::info('Gift ID:', ['id' => $gift->id]);

        try {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $token,
                'Accept' => 'application/json',
            ])->postJson('/api/gifts/send-lucky-gift-combo', [
                'id' => (int) $gift->id,
                'toUid' => (int) $receiver->id,
                'room_id' => 1000000,
                'num' => 1,
            ]);

            // عرض الاستعلامات التي تم تنفيذها
            Log::info('Database Queries:', DB::getQueryLog());
            
            // عرض تفاصيل الرد
            Log::info('Response Status:', ['status' => $response->status()]);
            Log::info('Response Headers:', $response->headers->all());
            
            if ($response->status() !== 200) {
                Log::error('Response Content:', ['content' => $response->getContent()]);
                Log::error('Response JSON:', ['json' => $response->json() ?? 'No JSON']);
            }

            $responseData = $response->json();
            LogHelper::info('Lucky Gift Monthly response', $responseData ?? []);
            // التحقق من الرد
            $response->assertStatus(200)
                     ->assertJson([
                         'success' => true,
                         'message' => 'Gift sent successfully'
                     ]);

        } catch (\Exception $e) {
            Log::error('Test Exception:', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }

        // تحديث الرصيد بعد الهدية
        $sender->refresh();
        $receiver->refresh();

        $expectedSender = $senderBefore - $giftCost;
        $expectedReceiver = $receiverBefore + $receiverGain;

        $this->assertEquals($expectedSender, $sender->coins, 
            "Sender coins mismatch. Expected: {$expectedSender}, Actual: {$sender->coins}");

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

        $this->assertEquals($receiverBefore + $receiverGain, $receiver->coins,
            "Receiver coins mismatch. Expected: " . ($receiverBefore + $receiverGain) . ", Actual: {$receiver->coins}");
        
        $this->assertEquals($receiverGain, $monthly->monthly_diamond_received,
            "Monthly diamond received mismatch. Expected: {$receiverGain}, Actual: {$monthly->monthly_diamond_received}");

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

        Log::info('Lucky Gift Monthly Report', $report);
    }
}