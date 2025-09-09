<?php

namespace Modules\RoomBoom\Services;

use App\Helpers\Common;
use App\Models\GiftLog;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Modules\RoomBoom\Entities\RoomBoom;
use Modules\RoomBoom\Entities\RoomBoomGift;
use Modules\RoomBoom\Entities\RoomBoomLevel;
use Modules\RoomBoom\Entities\TotalRoomGift;
use Modules\RoomBoom\Jobs\EndBoomPusherJob;
use Modules\RoomBoom\Jobs\EndBoomZegoJob;
use Modules\RoomBoom\Jobs\NewRoomBoomRewardJob;
use Modules\RoomBoom\Jobs\RoomBoomRewardJob;

class NewRoomBoomGiftService
{
    /**
     * @throws \Throwable
     */
    public function sendGift($room, $totalPrice, $userId): void
    {
        DB::transaction(function () use ($room, $totalPrice, $userId) {
            $roomId = $room->id;
            $roomUid = $room->uid;
            $tz = getTimezone();
            $todayStart = Carbon::now($tz)->startOfDay()->copy()->setTimezone('UTC');

            $totalRoomGift = $this->getOrCreateTotalRoomGift($roomId, $todayStart, $totalPrice);

            if ($totalRoomGift) {
                $currentTotal = $totalRoomGift->current_total;
//                $newTotal = $currentTotal + $totalPrice;
            } else {
//                $newTotal = $currentTotal = $totalPrice;
            }

            $this->oldLevels($totalPrice, $totalRoomGift->id, $userId, $currentTotal);

            $newTotal = $currentTotal;

            $this->activateLevels($totalRoomGift, $newTotal);

            $this->handleCurrentLevel($totalRoomGift, $newTotal, $roomId, $roomUid);

            $totalRoomGift->current_total = $newTotal;

            $this->checkAndEndBoom($totalRoomGift, $userId, $newTotal, $room);

            $totalRoomGift->save();
        });
    }

    private function getOrCreateTotalRoomGift($roomId, $todayStart, $totalPrice){
        $totalRoomGift = TotalRoomGift::where('room_id', $roomId)
            ->where('created_at', '>=', $todayStart)
            ->lockForUpdate()
            ->first();

        if (!$totalRoomGift) {
            $totalRoomGift = TotalRoomGift::create([
                'room_id' => $roomId,
                'current_total' => 0,
            ]);
        }

        return $totalRoomGift;
    }

    private function checkAndEndBoom($totalRoomGift, $userId, $newTotal, $room): void
    {
        $openBooms = RoomBoom::where('total_room_gift_id', $totalRoomGift->id)
            ->whereNull('ended_at')
            ->latest()
            ->get();

        foreach ($openBooms as $openBoom){
            $boomLevel = RoomBoomLevel::find($openBoom->room_boom_level_id);

            if ($boomLevel && $newTotal >= $boomLevel->target) {
                $openBoom->ended_at = Carbon::now();
                $openBoom->total_gifts_value = $newTotal;
                $openBoom->save();

                dispatch(new NewRoomBoomRewardJob($openBoom->id, $userId))->delay(now()->addSeconds(30));
                dispatch(new EndBoomPusherJob($boomLevel, $newTotal, auth()->id(), $room));
                dispatch(new EndBoomZegoJob($boomLevel, $room))->delay(now()->addSeconds(20));

                $this->mayStartNextBoom($totalRoomGift, $newTotal, $room, $boomLevel, $userId);
            }
        }
    }

    private function mayStartNextBoom($totalRoomGift, $newTotal, $room, $boomLevel, $userId): void
    {
        if ($newTotal == $boomLevel->target) {
            $nextLevel = RoomBoomLevel::where('min_target', $newTotal)->first();

            if ($nextLevel) {
                RoomBoom::firstOrCreate([
                    'total_room_gift_id' => $totalRoomGift->id,
                    'room_boom_level_id' => $nextLevel->id,
                ], [
                    'started_at'        => now(),
                    'total_gifts_value' => $newTotal,
                ]);

                $d = [
                    "messageContent" => [
                        "message" => "roomBoomStarted",
                        "roomBoomLevel" => $nextLevel->id,
                    ]
                ];

                Common::sendToZego('SendCustomCommand', $room->id, $room->uid, json_encode($d));
            }
        }
    }

    public function oldLevels($totalPrice, $totalRoomGiftId, $userId, &$currentTotal): void
    {
        $remaining = $totalPrice;
        $levels = RoomBoomLevel::orderBy('level', 'asc')->get();

        foreach ($levels as $level) {
            if ($remaining <= 0) break;

            if ($currentTotal >= $level->target) {
                continue;
            }

            $neededForLevel = $level->target - $currentTotal;
            $levelAmount  = min($remaining, $neededForLevel);
            $neededToMin = max(0, $level->min_target - $currentTotal);

            if ($currentTotal < $level->min_target) {
                if ($levelAmount  >= $neededToMin && $neededToMin > 0) {
                    RoomBoomGift::create([
                        'total_room_gift_id' => $totalRoomGiftId,
                        'user_id' => $userId,
                        'price' => $neededToMin,
                        'room_boom_level' => $level->level,
                        'start_boom_ranking' => 0,
                    ]);

                    $remainingPart = $levelAmount - $neededToMin;
                    if ($remainingPart > 0) {
                        RoomBoomGift::create([
                            'total_room_gift_id' => $totalRoomGiftId,
                            'user_id' => $userId,
                            'price' => $remainingPart,
                            'room_boom_level' => $level->level,
                            'start_boom_ranking' => 1,
                        ]);
                    }
                } else {
                    RoomBoomGift::create([
                        'total_room_gift_id' => $totalRoomGiftId,
                        'user_id' => $userId,
                        'price' => $levelAmount ,
                        'room_boom_level' => $level->level,
                        'start_boom_ranking' => 0,
                    ]);
                }
            } else {
                RoomBoomGift::create([
                    'total_room_gift_id' => $totalRoomGiftId,
                    'user_id' => $userId,
                    'price' => $levelAmount ,
                    'room_boom_level' => $level->level,
                    'start_boom_ranking' => 1,
                ]);
            }

            $currentTotal += $levelAmount ;
            $remaining -= $levelAmount ;
        }
    }

    private function activateLevels($totalRoomGift, $newTotal): void
    {
        $levelsToActivate = RoomBoomLevel::where('min_target', '<=', $newTotal)
            ->where('target', '<=', $newTotal)
            ->orderBy('level', 'asc')
            ->get();

        foreach ($levelsToActivate as $level) {
            $exists = RoomBoom::where('room_boom_level_id', $level->id)
                ->where('total_room_gift_id', $totalRoomGift->id)
                ->first();

            if (!$exists) {
                $this->safeCreateBoom($totalRoomGift->id, $level->id, $newTotal);
            }
        }
    }

    private function handleCurrentLevel($totalRoomGift, $newTotal, $roomId, $roomUid): void
    {
        $currentLevel = RoomBoomLevel::where('min_target', '<=', $newTotal)
            ->where('target', '>=', $newTotal)
            ->orderBy('level')
            ->first();

        if (!$currentLevel) return;

        $existingBoom = RoomBoom::where('room_boom_level_id', $currentLevel->id)
            ->where('total_room_gift_id', $totalRoomGift->id)
            ->lockForUpdate()
            ->first();

        if (!$existingBoom) {
            $existingBoom = $this->safeCreateBoom($totalRoomGift->id, $currentLevel->id, $newTotal);

            $d = [
                "messageContent" => [
                    "message"       => "roomBoomStarted",
                    'roomBoomLevel' => $currentLevel->id,
                ]
            ];
            Common::sendToZego('SendCustomCommand', $roomId, $roomUid, json_encode($d));
        }

        $existingBoom->total_gifts_value = $newTotal;
        $existingBoom->save();
    }

    private function safeCreateBoom($totalRoomGiftId, $levelId, $newTotal)
    {
        return RoomBoom::firstOrCreate(
            [
                'total_room_gift_id' => $totalRoomGiftId,
                'room_boom_level_id' => $levelId,
            ],
            [
                'started_at' => Carbon::now(),
                'total_gifts_value' => $newTotal,
            ]
        );
    }

}
