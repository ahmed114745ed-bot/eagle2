<?php

namespace App\Traits\User;


use App\Models\User;
use Modules\Vip\Entities\Vip;

trait UserLevel
{

    public function senderLevel()
    {
        return $this->belongsTo(Vip::class, 'sender_level', 'level')
            ->where('type', 2);
    }

    public function receiverLevel()
    {
        return $this->belongsTo(Vip::class, 'received_level', 'level')
            ->where('type', 1);
    }

    public function chargeLevel()
    {
        return $this->belongsTo(Vip::class, 'charge_level', 'level')
            ->where('type', 4);
    }

}
