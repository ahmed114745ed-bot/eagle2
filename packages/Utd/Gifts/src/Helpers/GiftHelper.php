<?php

namespace Utd\Gifts\Helpers;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Utd\Gifts\Entities\Gift;

class GiftHelper
{
    public static function UserLuckyGift($isWin, $userId, Gift $gift, $value, $number, $totalNumWin, $totalUserWin)
    {
        if (! Schema::hasTable('user_lucky_gifts')) {
            return;
        }

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
