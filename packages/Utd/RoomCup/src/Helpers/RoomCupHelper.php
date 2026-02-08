<?php

namespace Utd\RoomCup\Helpers;

use Illuminate\Support\Facades\DB;

class RoomCupHelper
{
    public static function updateRoomCupWallet(float|int $diffCoins): void
    {
        $sql = '
            UPDATE core_wallets
            SET coins = coins + :coins
            WHERE name = "room_cup_target"
        ';

        DB::update($sql, [
            'coins' => $diffCoins,
        ]);
    }
}
