<?php

namespace Utd\Gifts\Traits;

use App\Support\PackageHelper;
use Utd\Room\Jobs\AllOpeningRoomsZegoRequest;

trait WinLuckyGift
{
    public function is_winner($gift): bool
    {
        if (! $gift) {
            abort(404);
        }
        $win_probability = ((int) $gift->luckyGift?->win_probability ?? 50) / 100;
        $randomValue = mt_rand(0, 100) / 100;

        return $randomValue <= $win_probability;
    }

    public function sendToZegoLuckyGift($zigoData)
    {
        $d = [
            'messageContent' => [
                'msg' => 'SHBL',
                'event' => 'win.lucky.gift.event',
                'uid' => $zigoData['user_id'],
                'uImg' => $zigoData['user_image'],
                'gImg' => $zigoData['gift_image'],
                'ownerId' => $zigoData['owner_id'],
                'uName' => $zigoData['user_name'],
                'per' => $zigoData['cache_value'],
                'isPass' => $zigoData['is_room_pass'],
                'gNum' => $zigoData['percentage'],

                'gift_price' => @$zigoData['gift_price'],
                'room_id' => @$zigoData['room_id'],
                'room_name' => @$zigoData['room_name'],
                'room_cover' => @$zigoData['room_cover'],
                'room_background' => @$zigoData['room_background'],
                'room_mode' => @$zigoData['room_mode'],
                'room_uuid' => @$zigoData['room_uuid'],
                'room_owner_id' => @$zigoData['room_owner_id'],
                'is_password' => @$zigoData['is_password'],
                'room_type' => @$zigoData['room_type'],
            ],
        ];
        $json = json_encode($d);
        if (PackageHelper::isInstalled('room')) {
            dispatchJobToQueue(new AllOpeningRoomsZegoRequest($json, $zigoData['user_id'], $zigoData['room_id']), 'heavyProcessing');
        }
    }

    public function sendToZegoLuckyGiftV2($zigoData)
    {
        $d = [
            'messageContent' => [
                'msg' => 'SHBL',
                'event' => 'win.lucky.gift.event',
                'uid' => $zigoData['user_id'],
                'uImg' => $zigoData['user_image'],
                'gImg' => $zigoData['gift_image'],
                'ownerId' => $zigoData['owner_id'],
                'uName' => $zigoData['user_name'],
                'per' => $zigoData['cache_value'],
                'isPass' => $zigoData['is_room_pass'],
                'gNum' => $zigoData['percentage'],

                'gift_price' => @$zigoData['gift_price'],
                'room_id' => @$zigoData['room_id'],
                'room_name' => @$zigoData['room_name'],
                'room_cover' => @$zigoData['room_cover'],
                'room_background' => @$zigoData['room_background'],
                'room_mode' => @$zigoData['room_mode'],
                'room_uuid' => @$zigoData['room_uuid'],
                'room_owner_id' => @$zigoData['room_owner_id'],
                'is_password' => @$zigoData['is_password'],
                'room_type' => @$zigoData['room_type'],
            ],
        ];
        $json = json_encode($d);
        if ($zigoData['percentage'] >= 250 && $zigoData['percentage'] <= 1000 && PackageHelper::isInstalled('room')) {
            dispatchJobToQueue(new AllOpeningRoomsZegoRequest($json, $zigoData['user_id'], $zigoData['room_id'], isExceptRoom: true), 'heavyProcessing');
        }
    }
}
