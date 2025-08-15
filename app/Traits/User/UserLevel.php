<?php

namespace App\Traits\User;

use App\Models\Vip;

trait UserLevel
{

    public function senderLevel()
    {
        return $this->belongsTo(Vip::class, 'sender_level', 'level')
            ->where('type', 2);
    }

    public function receiverLevel()
    {
        return $this->belongsTo(Vip::class, 'receiver_level', 'level')
            ->where('type', 1);
    }

}
