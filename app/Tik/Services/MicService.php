<?php

namespace App\Tik\Services;

use Exception;

use Carbon\Carbon;
use App\Helpers\Common;
use App\Facades\RoomHelper;
use App\Models\Cp;
use App\Tik\Repositories\PkRepository;
use App\Tik\Repositories\RoomRepository;
use App\Tik\Repositories\UserRepository;
use App\Tik\Repositories\TimeLogRepository;
use App\Tik\Repositories\LiveTimeRepository;
use Illuminate\Support\Facades\Log;
use Modules\CP\Entities\CpRoomHistory;
use Modules\CP\Enums\CpStatus;

class MicService
{
    public function __construct(

        private readonly RoomRepository $roomRepository,
        private readonly LiveTimeRepository $liveTimeRepository,
        private readonly UserRepository $userRepository,
        private readonly PkRepository $pkRepository,
        private readonly TimeLogRepository $timeLogRepository,

    ) {
    }


    public function createLiveTime($userId, $seconde)
    {
        $hours = $seconde / 3600;
        $intValue = (int)$hours;
        $minutes = round(($hours - $intValue) * 60);
        $totalTime = sprintf('%02d:%02d', $intValue, $minutes);
        $hoursFormat          = number_format($hours, 5, '.', '');
        $data = [
            'start_time' => Carbon::now()->subSecond($seconde)->timestamp,
            'end_time'   => Carbon::now()->timestamp,
            'hours'    => $hoursFormat,
            'uid'     => $userId,
        ];

        $this->liveTimeRepository->create($data);

        $user_hours =  $this->liveTimeRepository->totalHoursUser($userId);

        $hours = (int)$user_hours;

        return [$hours, $totalTime];
    }

    public function upMic($data)
    {
        $user = $this->userRepository->findById($data['user_id']);

        if (!$user) throw new Exception(__('api_responses.this_user_not_found'));
        $room = $this->roomRepository->findRoomUser($data['owner_id'], false);
        if (!$room)  throw new Exception(__('room does not exist'));

        $position = $data['position']; //mic sequence 0-8
        $mic_arr = explode(',', $room->microphone);
        $main_mic = explode(',', $room->main_microphone);
        $base_mic = explode(',', $room->getOriginal('microphone'));
        $oldValue = $main_mic[$position];

        //If it is on the mic, skip to the top mic, and the original mic is empty
        if (in_array($user->id, $mic_arr)) {

            CpRoomHistory::where("user_one_id", $user->id)
            ->orWhere("user_two_id", $user->id)->delete();

            $key = array_search($user->id, $mic_arr);
            $old = $main_mic[$key];
            $base_mic[$key] = $old;
        }
        if (@$mic_arr[$position] != -1 || RoomHelper::checkUserIsAdminOrOwner($room->room_admin ?? '', $data['owner_id'])) {

            $base_mic[$position] = $user->id . '#' . $oldValue ?: 0;
        }
        $mic = implode(',', $base_mic);
        $this->updateMicAndPK($room, $mic);
        //Remove mic sequence
        Common::delMicHand($user->id);

        $t = $this->liveTimeRepository->getActiveByUserId($user->id);
        if (!$t) {

            $data = [
                'uid' => $user->id,
                'start_time' => time()
            ];
            $this->liveTimeRepository->create($data);
        }
        $this->handleCpLovely($user, $room, $position);

        return [$user, $room];
    }

    public function handleCpLovely($user, $room, $position)
    {
        $existingCps = Cp::where(function ($query) use ($user) {
            $query->where("user_one_id", $user->id)
                  ->orWhere("user_two_id", $user->id);
        })->whereIn("status", [
            CpStatus::ACTIVE,
            CpStatus::RESTORED
        ])->get();

        if ($existingCps->isEmpty()) {
            return false;
        }

        if ($room->mode == 0 && $position == 0) {
            return true;
        }

        if ($room->mode == 0){
            $userSeats = $this->getUserNearby($position - 1, mode: $room->mode);
            $userSeats = array_map(fn($item) => $item + 1 , $userSeats);
        }else{

            $userSeats = $this->getUserNearby($position, mode: $room->mode);
        }

        foreach ($userSeats as $antherUserPosition) {
            $newMic = explode(',', $room->microphone);
            $userOtherId = $newMic[$antherUserPosition];

            $existingCp = $this->checkExistingCpLovly($user->id, $userOtherId);
            Log::info('cp id '.$existingCp?->id);
            if ($existingCp) {
                $this->handleCpRoomHistory($user, $room, $position, $antherUserPosition, $userOtherId);
//                $this->sendCpLovelyMessage($room, $user);
            }
        }
        $this->sendCpLovelyMessage($room, $user);

        return true;
    }

    public function sendCpLovelyMessage($room, $user)
    {
        $cpRoomHistories = CpRoomHistory::where("room_id",$room->id)->get(['index1', 'index2']);
        $indices = $cpRoomHistories->map(function ($history) {
            return [$history->index1, $history->index2];
        })->toArray();

        $json = $this->cpMapJson($indices);

        Common::sendToZego('SendCustomCommand', $room->id, $user->id, $json);
    }
    public function cpMapJson($indices): string|false
    {
        $ms = [
            'messageContent' => [
                "message" => "cpLovelyZego",
                "data" => $indices,
            ]
        ];
        $json = json_encode($ms);
        return $json;
    }
    public function handleCpRoomHistory($user, $room, $index1, $index2, $userOtherId)
    {
        $cpRoomHistory = CpRoomHistory::where("user_one_id", $user->id)
            ->where("user_two_id", $userOtherId)
            ->orWhere(function ($query) use ($user, $userOtherId) {
                $query->where("user_two_id", $user->id)
                      ->where("user_one_id", $userOtherId);
            })
            ->where('room_id', $room->id)
            ->first();

        if ($cpRoomHistory) {
            $cpRoomHistory->index1 = $index1;
            $cpRoomHistory->index2 = $index2;
            $cpRoomHistory->save();
        } else {
            CpRoomHistory::create([
                'room_id' => $room->id,
                'user_one_id' => $user->id,
                'user_two_id' => $userOtherId,
                'index1' => $index1,
                'index2' => $index2,
            ]);
        }
    }
    public function checkExistingCpLovly($userId, $otherUserId)
    {
        return Cp::where(function ($query) use ($userId, $otherUserId) {
            $query->where("user_one_id", $userId)
                ->where("user_two_id", $otherUserId)
                ->orWhere(function ($query) use ($userId, $otherUserId) {
                    $query->where("user_two_id", $userId)
                        ->where("user_one_id", $otherUserId);
                });
        })->relation()
            /// TODO convert these status to enum
            ->whereIn("status", [CpStatus::PENDING->value, CpStatus::ACTIVE->value, CpStatus::RESTORED->value])
            // ->where("cp_relation_id",5)
            ->first();
    }
    public function getUserNearby($index, $mode,$rowSize = 4)
    {

        if ($index % $rowSize == 0) {
            return [$index + 1];
        } elseif (($index + 1) % $rowSize == 0) {
            return [$index - 1];
        } else {
            /*if ($mode == 0 && ($index + 1) % $rowSize == 0) {
                return [$index + 1];
            }*/
            return [$index - 1, $index + 1];
        }
    }
    public function goMic($data)
    {
        $user = $this->userRepository->findById($data->user_id);

        if (!$user) throw new Exception(__('api_responses.this_user_not_found'));


        $room = $this->roomRepository->findRoomUser($data['owner_id'], false);
       if(!$room) throw new Exception(__('api_responses.room_not_found'));

         $this->goMicrophoneHand($user, $room);
        return $room;
    }

    //Down the wheat - execute the operation
    public  function goMicrophoneHand($user, $room)
    {
        $microphone = $room->microphone;
        $mainMicrophone = $room->main_microphone;

        $baseMic = $room->getOriginal('microphone');
        $microphone = explode(',', $microphone);
        $mainMicrophone = explode(',', $mainMicrophone);
        $baseMic = explode(',', $baseMic);
        if (!$microphone || !in_array($user->id, $microphone)) {
            return 0;
        }
        $position = 0;
        for ($i = 0; $i < count($microphone); $i++) {
            if ($microphone[$i] == $user->id) {
                $position = $i;
                break;
            }
        }
        if ($microphone[$position] > 0) {
            $baseMic[$position] = $mainMicrophone[$position];
        }

        $microphone = implode(',', $baseMic);
        $this->updateMicAndPK($room, $microphone);
        //clear timer
        $this->timeLogRepository->delete($room->uid, $user->id);

        $this->handleLeaveCp($user, $room);

        return true;
    }

    public function handleLeaveCp($user,$room)
    {
        $userId = $user->id;
        $this->removeUserCpInRoom($userId);
        return $this->sendCpLovelyMessage($room, $user);
    }
    public function removeUserCpInRoom(mixed $userId): void
    {
        CpRoomHistory::where("user_one_id", $userId)
            ->orWhere("user_two_id", $userId)->delete();
    }
    public function mic($data, $type)
    {
        $position = $data['position'];
        $room = $this->roomRepository->findRoomUser($data['owner_id']);

        if ($room['mode'] == 0) {
            if ($position < 0 || $position > 9) throw new Exception(__('api_responses.position_error'));
        } else {
            if ($position < 0 || $position > 17) throw new Exception(__('api_responses.position_error'));
        }
        $admins = $room->room_admin;
        $admins = explode(',', $admins);

        if ($data->user()->id != $data['owner_id'] && !in_array($data->user()->id, $admins)) {
            return Common::apiResponse(0, __('api_responses.you_dont_have_permission'), null, 408);
        }

        $microphone = $room->microphone;

        $microphone = $this->micType($type, $microphone, $position);
        $this->updateMic($room, $microphone);
        return $room;
    }





    public function micType(string $type, $microphone, $position)
    {
        $microphone = explode(',', $microphone);
        if ($type == 'mute') {
            if (@$microphone[$position] != -1) {
                $microphone[$position] = -2;
            }
        } elseif ($type == 'unmute' || $type == 'open') {
            if (@$microphone[$position]) {
                $microphone[$position] = 0;
            }
        } elseif ($type == 'shut') {
            if (@$microphone[$position] == false) {
                $microphone[$position] = -1;
            }
        }
        return $microphone = implode(',', $microphone);
    }



    public function updateMicAndPK($room, $mic)
    {
        $this->updateMic($room, $mic);
        $pk = $this->pkRepository->getPk($room->id);
        if ($pk) {
            $pk->mics = $room->microphone;
            $pk->save();
        }
    }

    public function updateMic($room, $mic)
    {
        $this->roomRepository->updateMicRoom($room, $mic);
    }
}
