<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Models\Room;
use App\Models\User;
use App\Models\BoxUse;
use App\Models\Follow;
use App\Helpers\Common;
use App\Models\PickBoxList;
use App\Models\RoomVisitor;
use App\Models\UserBoxGift;
use App\Facades\RedisService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use App\Http\Services\LuckyBoxServices;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;

class NormalLuckyBoxJop implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $timezone = Common::timeZone();
        $timestamp = Carbon::now($timezone)->timestamp;
        \Log::error('normal box ' );
        $userBoxes =   BoxUse::where('end_at', '<', $timestamp)->where('type', 0)->where('is_closed', false)->get();
        if (!$userBoxes)  return;
       
        foreach ($userBoxes as $userBox) {
           $user = User::where('id', $userBox->user_id)->first();
           $user-> increment('di', $userBox->unused_coins);
            $userBox->is_closed = true;
            $userBox->save();
        }
    }
}