<?php

namespace Modules\Charizma\Http\Services;


use Modules\Charizma\Entities\ExtraDataInRoom;
use Illuminate\Http\Request;
use App\models\User;
use App\Models\Room;
use App\Helpers\Common;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Modules\Charizma\Transformers\CharismaResource;

class UserCharismaService
{
    public static function formatTotalInService(): bool
    {
        return config('charisma.format', false);
    }

    private Collection $userCharismaLevels;

    public function __construct()
    {
    }

    public function roomCharisma($room_id)
    {
        $room = Room::withoutAppends()->where('charizma_status', true)->where('id', $room_id)->first();
        if (!$room) {
            return [];
        }

        //        $microphones = $room->microphone;
        $users = $this->getUserIdWithPosition2($room);
        $user_ids = $users->pluck('user_id')->toArray();

        $charisma = ExtraDataInRoom::whereIn('user_id', $user_ids)->where('room_id', $room->id)->get()->map(function ($item) use ($users) {
            $item->position = $users->where('user_id', $item->user_id)->value('position');
            return $item;
        });

        return CharismaResource::collection($charisma);
    }



    public function RemoveUserRoomWhenLeaveMic($userId, $roomId)
    {
        $user = User::find($userId);
        $room = Room::find($roomId);

        if (!$user || !$room) {
            return Common::apiResponse(0, 'User Or Room does not exist', null, 404);
        }

        // Delete the ExtraDataInRoom record for the specified user
        $deleted = ExtraDataInRoom::where('user_id', $userId)->where('room_id', $roomId)->delete();

        if ($deleted) {
            return Common::apiResponse(1, 'User removed from the room', null, 200);

        } else {
            return Common::apiResponse(0, 'No records found for the user', null, 404);
        }
    }

    public function addTotalEarnedCoinsInUserRoom(Room $room, array $userIds, $earnedCoins = null): false|array
    {

        // info('addTotalEarnedCoinsInUserRoom');
        $roomId = $room->id;

        if (!$room) {
            return false;
        }


        //        $users = $this->getUserIdWithPosition2($room);
        $users = $room->microphones()
            ->get(['user_id', 'position'])
            ->map(fn($m) => [
                'user_id' => (int) $m->user_id,
                'position' => (int) $m->position,
            ])
            ->values();

        $allDataChanges = [];

        foreach ($userIds as $userId) {
            // Always get or create the record
            $extraDataInRoom = ExtraDataInRoom::firstOrCreate([
                'user_id' => $userId,
                'room_id' => $roomId
            ], ['total' => 0]);

            if ($earnedCoins) {
                // Update the total earned coins using atomic increment
                $extraDataInRoom->increment('total', $earnedCoins);
                // Reload the model to get fresh data
                $extraDataInRoom->refresh();
            }

            $user = $users->where('user_id', $userId)->first();
            if ($user) {
                $user['total'] = self::formatTotalInService()
                    ? numToStringNew($extraDataInRoom->total ?? 0)
                    : (int) $extraDataInRoom->total;
                Log::info('Total for user ' . $userId . ': ' . $user['total']);
                $allDataChanges[] = $user;
            }
        }


        return $allDataChanges;
    }

    public function addTotalEarnedCoinsInUserRoom2(Room $room, array $userIds, $earnedCoins = null): false|array
    {
        $roomId = $room->id;

        if (!$room) {
            return false;
        }

        $users = $this->getUserIdWithPosition2($room);

        $allDataChanges = [];

        foreach ($userIds as $userId) {
            // Always get or create the record
            $extraDataInRoom = ExtraDataInRoom::firstOrCreate([
                'user_id' => $userId,
                'room_id' => $roomId
            ], ['total' => 0]);

            if ($earnedCoins) {
                // Update the total earned coins using atomic increment
                $extraDataInRoom->increment('total', $earnedCoins);
                // Reload the model to get fresh data
                $extraDataInRoom->refresh();
            }

            $user = $users->where('user_id', $userId)->first();
            if ($user) {
                $user['total'] = self::formatTotalInService()
                    ? numToStringNew($extraDataInRoom->total ?? 0)
                    : (int) $extraDataInRoom->total;
                $allDataChanges[] = $user;
            }
        }

        return $allDataChanges;
    }

    /**
     * @param $microphones
     * @return \Illuminate\Support\Collection
     */
    public function getUserIdWithPosition($microphones): \Illuminate\Support\Collection
    {
        $arrMicrophones = explode(',', $microphones) ?: [];
        $users = [];
        foreach ($arrMicrophones as $key => $arrMicrophone) {
            if ($arrMicrophone >= 0) {
                $users[] = ['user_id' => $arrMicrophone, 'position' => $key];
            }
        }

        return collect($users);
    }

    public function getUserIdWithPosition2($room)
    {
        return $room->microphones()->get(['user_id', 'position']);
    }


    public function removeRoomCharisma(int $roomId)
    {
        ExtraDataInRoom::query()->where('room_id', $roomId)->delete();
    }

    public function resetUserCharisma(int $userId, int $roomId)
    {
        ExtraDataInRoom::query()->where('room_id', $roomId)->where('user_id', $userId)->delete();
    }

    public function getUserResetData($microphones, array $userIds)
    {
        $users = $this->getUserIdWithPosition($microphones);
        $allDataChanges = [];
        foreach ($userIds as $userId) {
            $user = $users->where('user_id', $userId)->first();
            if ($user) {
                $user['total'] = numToStringNew(0);
                $allDataChanges[] = $user;
            }
        }
        return $allDataChanges;
    }

    public function getUserResetData2($room, array $userIds)
    {
        $users = $this->getUserIdWithPosition2($room);
        $allDataChanges = [];
        foreach ($userIds as $userId) {
            $user = $users->where('user_id', $userId)->first();
            if ($user) {
                $user['total'] = numToStringNew(0);
                $allDataChanges[] = $user;
            }
        }
        return $allDataChanges;
    }
}
