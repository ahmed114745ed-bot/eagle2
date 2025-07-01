<?php 
// ملف: app/Helpers/UserCoinLogHelper.php
namespace App\Helpers;

use App\Models\UserCoinLog;
use Carbon\Carbon;

class UserCoinLogHelper
{
    /**
     * Log app profit coins to user_coin_logs table.
     *
     * @param int|null $userId
     * @param string $type         (مثلاً: gift / game / lucky / vip / pack / cps ...)
     * @param string $subType      (مثلاً: gift_logs / coin_game_users ...)
     * @param int $amount
     * @param string|null $fromDate
     * @param string|null $toDate
     * @return UserCoinLog
     */
    public static function log(
        ?int $userId,
        string $type,
        string $subType,
        int $amount,
        ?string $fromDate = null,
        ?string $toDate = null
    ): UserCoinLog {
        return UserCoinLog::create([
            'user_id'    => $userId,
            'type'       => $type,
            'sub_type'   => $subType,
            'amount'     => $amount,
            'from_date'  => $fromDate ?? Carbon::now()->toDateString(),
            'to_date'    => $toDate ?? Carbon::now()->toDateString(),
        ]);
    }
}
