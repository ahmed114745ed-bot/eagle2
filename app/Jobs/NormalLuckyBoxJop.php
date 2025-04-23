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
        $cacheKey = 'timezone';
        $timestamp = Carbon::now($cacheKey)->timestamp;

        $userBoxes =   BoxUse::where('end_at', '<', $timestamp)->where('type', 0)->where('is_closed', false)->get();
        foreach ($userBoxes as $userBox) {
            User::where('id', $userBox->user_id)->increment('di', $userBox->unused_coins);
            $userBox->is_closed = true;
            $userBox->save();
        }
    }
}