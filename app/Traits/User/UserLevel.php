<?php

namespace App\Traits\User;

use App\Models\User;
use Utd\Vip\Entities\Vip;
use App\Support\PackageHelper;
use Illuminate\Support\Facades\Log;

trait UserLevel
{

    public function senderLevel()
    {
        return PackageHelper::checkRelation($this, 'vip', 'belongsTo') ??
            $this->belongsTo(Vip::class, 'sender_level', 'level')
            ->where('type', 2);
    }

    public function receiverLevel()
    {
        return PackageHelper::checkRelation($this, 'vip', 'belongsTo') ??
            $this->belongsTo(Vip::class, 'received_level', 'level')
            ->where('type', 1);
    }

    public function totalSenderLevels()
    {
        return PackageHelper::checkRelation($this, 'vip', 'belongsTo') ??
            $this->belongsTo(Vip::class, 'total_sender_level', 'level')
            ->where('type', 2);
    }

    public function totalReceiverLevels()
    {
        return PackageHelper::checkRelation($this, 'vip', 'belongsTo') ??
            $this->belongsTo(Vip::class, 'total_received_level', 'level')
            ->where('type', 1);
    }

    public function chargeLevel()
    {
        return PackageHelper::checkRelation($this, 'vip', 'belongsTo') ??
            $this->belongsTo(Vip::class, 'charge_level', 'level')
            ->where('type', 4);
    }
    public function getNextSenderLevelInfoAttribute(): array
    {
        $currentLevel = $this->senderLevel;

        if (!$currentLevel) {
            return [
                'next_level' => null,
                'remaining_exp_ratio' => 0.0, // double
            ];
        }

        $nextLevel = PackageHelper::isInstalled('vip') ? Vip::where('type', 2)
            ->where('level', '>', $currentLevel->level)
            ->orderBy('level')
            ->first() : null;

        if (!$nextLevel) {
            return [
                'next_level' => null,
                'remaining_exp_ratio' => 0.0,
            ];
        }

        $currentExp = $this->sender_exp ?? 0;
        $levelStartExp = $currentLevel->exp ?? 0;
        $levelEndExp = $nextLevel->exp ?? 0;

        $totalExpDiff = max($levelEndExp - $levelStartExp, 1);
        $remainingExp = max($levelEndExp - $currentExp, 0);

        $remainingRatio = $remainingExp / $totalExpDiff;

        return [
            'next_level' => $nextLevel->level,
            'remaining_exp_ratio' => round($remainingRatio, 2), 
        ];
    }





}
