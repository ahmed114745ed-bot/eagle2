<?php

namespace Utd\Events\Services;

use App\Contracts\LoseWinnerRewardsContract;
use App\Models\Pack;
use App\Models\Ware;
use Carbon\Carbon;

class LoseWinnerRewards implements LoseWinnerRewardsContract
{
    public function removePacksVip($reward, $vip, $user, $expire): void
    {
        $wares = Ware::query ()->where ('get_type',1)->where ('level',$vip->level)->get ();
        $targetIds = $wares->pluck('id');

        $createdAt = Carbon::parse($reward->created_at);
        $afterExpire = $createdAt->copy()->addDays($expire ?? 0)->timestamp;
        $expire = intval(($afterExpire - now()->timestamp) );
        if ($expire < 0) return;

        Pack::whereIn('target_id', $targetIds)->where('get_type', 1)->where('user_id', $user->id)->decrement('expire', $expire);

        Pack::query ()->where ('user_id',$user->id)
            ->whereIn('target_id', $targetIds)
            ->where('get_type', 1)
            ->where ('expire','<',now ()->timestamp)
            ->delete ();
    }
}
