<?php

namespace Utd\Room\Repositories;

use Utd\Room\Entities\BanRoom;

class BanRoomRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(new BanRoom());
    }

    public function getByRoom($roomId)
    {
        return $this->model->where('room_id', $roomId)->get();
    }

    public function isBanned($userId, $roomId)
    {
        return $this->model->where('user_id', $userId)
            ->where('room_id', $roomId)
            ->where(function ($q) {
                $q->whereNull('expire_at')
                    ->orWhere('expire_at', '>', now());
            })
            ->exists();
    }

    public function banUser($userId, $roomId, $staffId = null, $expireAt = null)
    {
        return $this->create([
            'user_id' => $userId,
            'room_id' => $roomId,
            'staff_id' => $staffId,
            'expire_at' => $expireAt,
        ]);
    }

    public function unbanUser($userId, $roomId)
    {
        return $this->model->where('user_id', $userId)
            ->where('room_id', $roomId)
            ->delete();
    }
}
