<?php

namespace App\Console\Commands;


use Carbon\Carbon;
use App\Models\User;
use App\Models\BoxUse;
use Illuminate\Console\Command;


class NormalLuckyBoxCommand extends Command
{
    protected $signature = 'normal-lucy-box';

    protected $description = 'Command description';

    public function handle()
    {
        $cacheKey = 'timezone';
        $timezone = \Cache::rememberForever($cacheKey, function () {
            $setting = \App\Models\Setting::where('key', 'timezone')->first();
            return $setting?->value ?? 'UTC';
        });
        $timestamp = Carbon::now($timezone)->timestamp;

        $userBoxes =   BoxUse::where('end_at', '<', $timestamp)->where('type', 0)->where('is_closed', false)->get();
        if (!$userBoxes) return '';
        foreach ($userBoxes as $userBox) {
            User::where('id', $userBox->user_id)->increment('di', $userBox->unused_coins);
            $userBox->is_closed = true;
            $userBox->save();
        }
    }
}
