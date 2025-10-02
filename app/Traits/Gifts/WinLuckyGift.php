<?php

namespace App\Traits\Gifts;

use App\Jobs\AllOpeningRoomsZegoRequest;
use Illuminate\Support\Facades\Log;

trait WinLuckyGift
{
    public function is_winner($gift): bool
    {
        if (!$gift) abort(404);
        $win_probability = ((int)$gift->luckyGift?->win_probability ?? 50) / 100;
        $randomValue     = mt_rand(0, 100) / 100;
        return $randomValue <= $win_probability;
    }

    public function sendToZegoLuckyGift($zigoData)
    {
        Log::info($zigoData);
        Log::info('shami-logs');
        $d     = [
            "messageContent" => [
                "msg"     => "SHBL",
                'event' => 'win-lucky-gift-event',
                'uid' => $zigoData['user_id'],
                'uImg' => $zigoData['user_image'],
                'gImg' => $zigoData['gift_image'],
                'ownerId' => $zigoData['owner_id'],
                'uName' => $zigoData['user_name'],
                'per'   => $zigoData['cache_value'],
                'isPass' => $zigoData['is_room_pass'],
                'gNum' => $zigoData['percentage']/*$zigoData['gift_price']*/,

                'gift_price'   => @$zigoData['gift_price'],
                'room_id'   => @$zigoData['room_id'],
                'room_name'   => @$zigoData['room_name'],
                'room_cover'   =>  @$zigoData['room_cover'],
                'room_background'   => @$zigoData['room_background'],
                'room_mode'   =>  @$zigoData['room_mode'],
                'room_uuid'   =>  @$zigoData['room_uuid'],
                'room_owner_id'   =>  @$zigoData['room_owner_id'],
                'is_password'   =>   @$zigoData['is_password'],
                'room_type'   =>   @$zigoData['room_type'],
            ]
        ];
        $json  = json_encode($d);
        AllOpeningRoomsZegoRequest::dispatch($json, $zigoData['user_id'], $zigoData['room_id'], isExceptRoom: true )
        ->onQueue('zegoRequests');
        // dispatchJobToQueue(new AllOpeningRoomsZegoRequest($json, $zigoData['user_id'], $zigoData['room_id'], isExceptRoom: true ), 'heavyProcessing');
    }
}
