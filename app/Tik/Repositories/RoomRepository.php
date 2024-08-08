<?php

namespace App\Tik\Repositories;

use App\Models\Room;
use App\Models\RoomPrivateMessages;

class RoomRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(new Room());
    }


    // public function create($request, $userId)
    // {
    //     $room = $this->model->create(array_merge($request->all(), ['uid' => $userId]));
    //     if ($request->hasFile('room_cover')) {
    //         $room->room_cover = Common::upload('rooms', $request->file('room_cover'));
    //         $room->save();
    //     }

    //     return $room;
    // }

    public function findRoomUser($userId)
    {
        return $this->model->withoutAppends()->where('uid', $userId)->first();
    }

    public function updateRoom($room)
    {
        $room->update();
    }

    public function createPrivetMessage($fromUserId, $toUserId, $message, $price)
    {
        return  RoomPrivateMessages::query()->create([
            'from_user_id' => $fromUserId,
            'to_user_id' => $toUserId,
            'message' => $message,
            'price' => $price
        ]);
    }


    public function findRoom($roomId)
    {
        return $this->model->find($roomId);
    }


    public function getRooms($ids)
    {
        return $this->model->whereIn('uid', $ids)->where(function ($q) {
            $q->where('count_room_socket', '!=', 0);
        })->orderBy('hot', 'desc')->get();
    }

    public function updateMicRoom($room, $mic)
    {
        $room->microphone = $mic;
        $this->updateRoomUser($room);
        return true;
    }

    public function updateRoomStatus($userId,$isAvailable)
    {
        return $this->model->query()->where('uid', $userId)->update(['room_status' => $isAvailable ? 2 : 1]);
    }

    public function updateRoomUser($room)
    {
        $room->save();
        return true;
    }
}
