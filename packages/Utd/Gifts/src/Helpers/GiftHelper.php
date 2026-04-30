<?php

namespace Utd\Gifts\Helpers;

use Utd\Gifts\Entities\Gift;
use Illuminate\Support\Facades\DB;

class GiftHelper
{
    public static function UserLuckyGift($isWin, $userId, Gift $gift, $value, $number, $totalNumWin, $totalUserWin)
    {
        $data = [
            'user_id' => $userId,
            'gift_id' => @$gift->id,
            'type' => $isWin ? 1 : 0,
            'value' => $value,
            'number' => $number,
            'total_num_win' => $totalNumWin,
            'total_win' => $totalUserWin,
            'gift_price' => @$gift->price,
            'app_profit_coins' => @$gift->price,
            'created_at' => now(),
            'updated_at' => now(),
        ];

        DB::table('user_lucky_gifts')->insert($data);
    }
}
