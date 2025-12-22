<?php

namespace App\Traits\User;


use App\Models\User;
use Illuminate\Support\Facades\Log;
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
    Log::info("Current sender level for user {$this->id}:", ['level' => $currentLevel?->level]);

    if (!$currentLevel) {
        Log::info("No current sender level found for user {$this->id}");
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
        Log::info("No next sender level exists for user {$this->id}");
        return [
            'next_level' => null,
            'remaining_exp' => 0,
        ];
    }

    $currentExp = $this->sender_exp ?? 0;
    $remainingExp = max($nextLevel->exp - $currentExp, 0);

    Log::info("Next sender level for user {$this->id}:", [
        'next_level' => $nextLevel->level,
        'current_exp' => $currentExp,
        'next_level_exp' => $nextLevel->exp,
        'remaining_exp' => $remainingExp
    ]);

    return [
        'next_level' => $nextLevel->level,
        'remaining_exp' => $remainingExp,
    ];
}



}
