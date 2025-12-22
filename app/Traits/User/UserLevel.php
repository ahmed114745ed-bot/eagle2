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


    public function getNextSenderLevelInfoAttribute(): array
    {
        $currentLevel = $this->senderLevel;

        if (!$currentLevel) {
            return [
                'next_level' => null,
                'remaining_exp' => null,
            ];
        }

        $nextLevel = Vip::where('type', 'sender')
            ->where('level', '>', $currentLevel->level)
            ->orderBy('level')
            ->first();

        if (!$nextLevel) {
            return [
                'next_level' => null,
                'remaining_exp' => 0,
            ];
        }

        $currentExp = $this->sender_exp ?? 0;

        return [
            'next_level' => $nextLevel->level,
            'remaining_exp' => max(
                $nextLevel->exp - $currentExp,
                0
            ),
        ];
    }


}
