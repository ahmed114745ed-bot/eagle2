<?php

namespace App\Tik\Repositories;

use App\Models\TimeEnterRoom;

class TimeEnterRoomRepository extends AbstractRepository
{

    public function __construct()
    {
        parent::__construct(new TimeEnterRoom());
    }

    public function getActiveByUserId($userId, $roomId)
    {
        return $this->model->query()->where('user_id', $userId)->where('room_id', $roomId)
            ->where('end_time', null)
            ->whereDate('created_at', today())
            ->orderByDesc('id')
            ->first();
    }
}
