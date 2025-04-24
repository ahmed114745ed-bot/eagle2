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

class SuperLuckyBoxJob implements ShouldQueue
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
        \Log::error('super box ' );
        $userBoxes =   BoxUse::where('end_at', '<', $timestamp)->where('type', 1)->where('is_closed', false)->get();
        if (!$userBoxes)  return;
        foreach ($userBoxes as $userBox) {
            $keyBoxUse  = 'BoxUse_' . $userBox->bid;
            $pickerBoxIds =   PickBoxList::where('box_user_id', $userBox->id)->pluck('user_id')->toArray();
            if (!$pickerBoxIds)  return;
            $users =  User::whereIn('id', $pickerBoxIds)->inRandomOrder()->get();
            foreach ($users as $user) {

                $userInRoom =     RoomVisitor::where('user_id', $user->id)->exists();
                if (!$userInRoom)  return;

                if ($userBox->users_num != $userBox->used_num) {

                    if (($userBox->not_used_num == 1)) {
                        $userBox->not_used_num = 0;
                        $fin = 1;
                    } else {
                        $fin = 0;
                        $userBox->not_used_num -= 1;
                    }

                    $giftService = new LuckyBoxServices();
                    $coins = $giftService->getCoins($fin, $userBox->unused_coins, $userBox->not_used_num);

                    //            put user in redis
                    $data = [
                        'box_uses_id' => $userBox->id,
                        'user_id' => $user->id,
                        'coins' => $coins,
                        'room_uid' => $userBox->room_uid,
                        'room_id' => $userBox->room_id,
                        'type' => $userBox->type,
                        'box_uses_owner_id' => $userBox->user_id,
                        'image' => $userBox->image,
                        'label' => $userBox->label
                    ];

                    if (UserBoxGift::where(['user_id' => $user->id, 'box_uses_id' => true])->exists()) {
                        return;
                    }

                    UserBoxGift::query()->create($data);

                    $userBox['used_coins'] += $coins;
                    $userBox['used_num'] += 1;
                    $userBox['unused_coins'] -= $coins;
                    //update box use in redis
                    RedisService::updateUnSerialize($keyBoxUse, $userBox);
                    dispatch(new OpenBoxJob($userBox->bid, $user->id, $user->name))->onQueue('luckyBox');

                    $user->increment('di', $coins);
                }
                $user = User::where('id', $userBox->user_id)->first();
                $user->increment('di', $userBox->unused_coins);
                $userBox->is_closed = true;
                $userBox->save();
            }
        }
    }
}
