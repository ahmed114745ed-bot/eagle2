<?php

namespace Tests\Feature;

use Tests\TestCase;
use Mockery;
use App\Models\User;
use App\Services\Gifts\LuckyGiftService;
use App\Services\Gifts\UpdateUserWhenSendGift;

class SendLuckyGift2FeatureTest extends TestCase
{

    public function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

public function test_send_lucky_gift_feature_test()
{
    $this->withoutMiddleware([
        \App\Http\Middleware\CheckCpu::class,
        \App\Http\Middleware\AppFeatureEnable::class,
    ]);

    // Disable stop lucky
    settings()->set('stop_luckyGift', 0);

    // Create users
    $sender = User::factory()->create(['coins' => 500]);
    $receiver = User::factory()->create(['coins' => 200]);

    $senderBefore = $sender->coins;
    $receiverBefore = $receiver->coins;

    $giftCost = 100;
    $receiverGain = 50;

    // Mock LuckyGiftService behavior
    $mockGift = Mockery::mock(LuckyGiftService::class);
    $mockGift->shouldReceive('sendLuckyGift2')
        ->once()
        ->andReturn([
            'sender_new_balance' => $senderBefore - $giftCost,
            'receiver_new_balance' => $receiverBefore + $receiverGain,
        ]);

    $this->app->instance(LuckyGiftService::class, $mockGift);

    // Mock UpdateUserWhenSendGift
    $mockUpdate = Mockery::mock(UpdateUserWhenSendGift::class);
    $this->app->instance(UpdateUserWhenSendGift::class, $mockUpdate);

    // Send request to the real route
    $response = $this->actingAs($sender, 'sanctum')
                     ->postJson('/api/gifts/send-lucky-gift-combo', [
                         'id' => 1,
                         'toUid' => $receiver->id,
                         'num' => 1,
                     ]);

    $response->assertStatus(200)
             ->assertJson([
                 'status' => 1,
                 'data' => [
                     'sender_new_balance' => $senderBefore - $giftCost,
                     'receiver_new_balance' => $receiverBefore + $receiverGain,
                 ]
             ]);
}

}
