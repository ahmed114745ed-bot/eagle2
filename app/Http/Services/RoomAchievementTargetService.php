<?php

namespace App\Http\Services;

use App\Models\Room;
use App\Models\User;
use App\Models\GiftLog;
use App\Models\AppFeature;
use App\Models\RoomGiftTarget;
use App\Facades\CustomNotification;
use App\Models\RoomOwnerAchievement;
use Illuminate\Support\Facades\Log;

class RoomAchievementTargetService
{


    public function sumGiftPrice($roomId)
    {
          return GiftLog::where('room_id', $roomId)->where('room_gift_status', true)->sum('giftPrice');
    }

    public function reachTarget($roomId, $targetId)
    {
        return !RoomOwnerAchievement::where('target_id', $targetId)->where('room_id', $roomId)->exists();
    }

    public function roomTarget(Room $room)
    {
        Log::info('Starting roomTarget logic', ['room_id' => $room->id, 'room_name' => $room->room_name]);

        $appFeature = AppFeature::where('slug', 'room_gift_target')->first();
        Log::info('Fetched appFeature', ['feature' => $appFeature]);
    
        if ($appFeature && $appFeature?->status == 1) {
            $totalRoomPrice = $this->sumGiftPrice($room->id);
            Log::info('Total room gift price calculated', ['room_id' => $room->id, 'total_price' => $totalRoomPrice]);
    
            $roomTarget = RoomGiftTarget::where('target', '<=', $totalRoomPrice)->orderByDesc('target')->first();
            Log::info('Fetched roomTarget', ['target' => $roomTarget]);
    
            if (!$roomTarget) {
                Log::info('No room target reached.');
                return;
            }
    
            if ($this->reachTarget($room->id, $roomTarget->id)) {
                Log::info('Target reached', ['room_id' => $room->id, 'target_id' => $roomTarget->id]);
    
                $user = User::find($room->uid);
                if (!$user) {
                    Log::warning('Room owner user not found', ['uid' => $room->uid]);
                    return;
                }
    
                $beforeCoins = $user->di;
                $user->di += $roomTarget->coins;
                $user->save();
    
                Log::info('Updated user coins', [
                    'user_id' => $user->id,
                    'before' => $beforeCoins,
                    'added' => $roomTarget->coins,
                    'after' => $user->di
                ]);
    
                RoomOwnerAchievement::create([
                    'target_id' => $roomTarget->id,
                    'room_id' => $room->id,
                    'coins' => $roomTarget->coins,
                ]);
    
                Log::info('RoomOwnerAchievement created', [
                    'target_id' => $roomTarget->id,
                    'room_id' => $room->id,
                    'coins' => $roomTarget->coins
                ]);
    
                CustomNotification::roomAchievementTarget($user, $roomTarget->coins, $room->room_name);
                Log::info('Achievement notification sent');
            } else {
                Log::info('Target not reached based on condition');
            }
        } else {
            Log::info('AppFeature not active or not found');
        }
    }
}
