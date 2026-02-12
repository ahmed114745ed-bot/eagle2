<?php

namespace Utd\Room\Repositories;

use DB;
use Utd\Room\Entities\RoomMicrophone;

class RoomMicrophoneRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(new RoomMicrophone());
    }

    public function getByRoom($roomId)
    {
        return $this->model->where('room_id', $roomId)
            ->orderBy('position')
            ->get();
    }

    public function getByPosition($roomId, $position)
    {
        return $this->model->where('room_id', $roomId)
            ->where('position', $position)
            ->first();
    }

    public function assignUser($roomId, $position, $userId)
    {
        return $this->updateOrCreate(
            ['room_id' => $roomId, 'position' => $position],
            ['user_id' => $userId, 'status' => 1]
        );
    }

    public function removeUser($roomId, $position)
    {
        return $this->model->where('room_id', $roomId)
            ->where('position', $position)
            ->update(['user_id' => null, 'status' => 0]);
    }

    public function clearRoom($roomId)
    {
        return $this->model->where('room_id', $roomId)->delete();
    }

    public function updateStatus($roomId, $position, $status)
    {
        return $this->model->where('room_id', $roomId)
            ->where('position', $position)
            ->update(['status' => $status]);
    }

    public function getByRoomIds(array $roomIds, array $columns = ['room_id', 'user_id', 'position'])
    {
        return DB::table('room_microphones')
            ->whereIn('room_id', $roomIds)
            ->orderBy('position')
            ->get($columns);
    }
}
