<?php

namespace App\Traits\Gifts;

use App\Jobs\AllOpeningRoomsZegoRequest;

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

        $d     = [
            "messageContent" => [
                "msg"     => "SHBL",
                'uid' => $zigoData['user_id'],
                'uImg' => $zigoData['user_image'],
                'gImg' => $zigoData['gift_image'],
                'ownerId' => $zigoData['owner_id'],
                'uName' => $zigoData['user_name'],
                'per'   => $zigoData['percentage'],
                'isPass' => $zigoData['is_room_pass']
            ]
        ];
        $json  = json_encode($d);

        dispatchJobToQueue(new AllOpeningRoomsZegoRequest($json, $zigoData['user_id'], $zigoData['room_id'], isExceptRoom: true ), 'heavyProcessing');
    }
}
