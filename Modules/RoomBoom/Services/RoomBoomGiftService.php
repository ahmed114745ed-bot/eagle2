<?php

namespace Modules\RoomBoom\Services;

use App\Helpers\Common;
use App\Models\GiftLog;
use Carbon\Carbon;
use Modules\RoomBoom\Entities\RoomBoom;
use Modules\RoomBoom\Entities\RoomBoomLevel;
use Modules\RoomBoom\Entities\TotalRoomGift;
use Modules\RoomBoom\Jobs\EndBoomPusherJob;
use Modules\RoomBoom\Jobs\EndBoomZegoJob;
use Modules\RoomBoom\Jobs\RoomBoomRewardJob;

class RoomBoomGiftService
{
    public function sendGift($room, $totalPrice, $roomBoomUuid): void
    {
        $roomId = $room->id;
        $roomUid = $room->uid;
        $tz = $this->getTimezone();
        $todayStart = Carbon::now($tz)->startOfDay();

        $totalRoomGift = $this->getOrCreateTotalRoomGift($roomId, $todayStart, $totalPrice);

        if ($totalRoomGift) {
            $currentTotal = $totalRoomGift->current_total;
            $newTotal = $currentTotal + $totalPrice;
        } else {
            $newTotal = $currentTotal = $totalPrice;
        }

        $currentLevel = RoomBoomLevel::where('min_target', '<=', $newTotal)
            ->where('target', '>=', $newTotal)
            ->orderBy('level')
            ->first();

        if ($currentLevel) {
            $existingBoom = RoomBoom::where('room_boom_level_id', $currentLevel->id)
                ->where('total_room_gift_id', $totalRoomGift->id)
                ->first();

            if (!$existingBoom) {
                $giftLogId = GiftLog::where('room_boom_uuid', $roomBoomUuid)->orderByDesc('id')->value('id');

                $existingBoom = RoomBoom::create([
                    'total_room_gift_id' => $totalRoomGift->id,
                    'room_boom_level_id' => $currentLevel->id,
                    'started_at' => Carbon::now(),
                    'total_gifts_value' => $newTotal,
                    'trigger_gift_id' => $giftLogId
                ]);

                $d = [
                    "messageContent" => [
                        "message" => "roomBoomStarted",
                        'roomBoomLevel' => $currentLevel->id,
                    ]
                ];
                $json = json_encode($d);

                Common::sendToZego('SendCustomCommand', $roomId, $roomUid, $json);
            }

            if ($newTotal >= $currentLevel->min_target) {
                $startBoomRanking = 1;
            } else {
                $startBoomRanking = 0;
            }

            GiftLog::where('room_boom_uuid', $roomBoomUuid)->update([
                'room_boom_level' => $currentLevel->level,
                'start_boom_ranking' => $startBoomRanking
            ]);
            $existingBoom->total_gifts_value = $newTotal;
            $existingBoom->save();
        } else {
            $nextLevel = RoomBoomLevel::where('min_target', '>', $newTotal)
                ->orderBy('min_target', 'asc')
                ->first();

            if ($nextLevel) {
                GiftLog::where('room_boom_uuid', $roomBoomUuid)->update([
                    'room_boom_level' => $nextLevel->level,
                    'start_boom_ranking' => 0
                ]);
            }
        }

        $totalRoomGift->current_total = $newTotal;

//        $this->checkAndEndBoom($totalRoomGift, $roomBoomUuid, $newTotal, $currentLevel, $room);

        $totalRoomGift->save();
    }

    private function getTimezone(): string
    {
        $tz = request()->header('tz', Common::timeZone());
        return in_array($tz, timezone_identifiers_list()) ? $tz : 'UTC';
    }

    private function getOrCreateTotalRoomGift($roomId, $todayStart, $totalPrice){
        $totalRoomGift = TotalRoomGift::where('room_id', $roomId)
            ->where('created_at', '>=', $todayStart)
            ->first();

        if (!$totalRoomGift) {
            $totalRoomGift = TotalRoomGift::create([
                'room_id' => $roomId,
                'current_total' => 0,
            ]);
        }

        return $totalRoomGift;
    }

    private function checkAndEndBoom($totalRoomGift, $roomBoomUuid, $newTotal, $currentLevel, $room): void
    {
        $openBoom = RoomBoom::where('total_room_gift_id', $totalRoomGift->id)
            ->whereNull('ended_at')
            ->latest()
            ->first();

        if ($openBoom){
            $boomLevel = RoomBoomLevel::find($openBoom->room_boom_level_id);
            $giftLog = GiftLog::where('room_boom_uuid', $roomBoomUuid)->orderByDesc('id')->first(['id', 'sender_id']);

            if ($boomLevel && $newTotal >= $boomLevel->target) {
                $openBoom->ended_at = Carbon::now();
                $openBoom->total_gifts_value = $newTotal;
                $openBoom->final_gift_id = $giftLog->id;
                $openBoom->save();

                dispatch(new RoomBoomRewardJob($openBoom->id))->delay(now()->addSeconds(30));
                dispatch(new EndBoomPusherJob($currentLevel, $newTotal, auth()->id(), $room));
                dispatch(new EndBoomZegoJob($currentLevel, $room))->delay(now()->addSeconds(20));
            }
        }
    }

}
