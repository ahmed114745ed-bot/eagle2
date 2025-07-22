<?php

namespace Modules\LuckyBox\Traits;

trait RoomBoxes {

    public function scopeWithLuckyBoxFlag($query, $userId)
    {
        $query->withExists(['boxUses as is_lucky_box' => function ($q) use ($userId) {
            $q->where('not_used_num', '>', 0)
                // ->where('unused_coins', '>', 0)
                ->where('end_at', '>=', now()->timestamp)
                ->whereDoesntHave('picks', function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                });
        }]);
    }
}
