<?php

namespace Utd\Room\Repositories;

use Utd\Room\Entities\EnteredRoom;

class EnteredRoomRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(new EnteredRoom());
    }

    public function getByUser($userId)
    {
        return $this->model->where('uid', $userId)
            ->orderByDesc('entered_at')
            ->get();
    }

    public function getRecentByUser($userId, $limit = 10)
    {
        return $this->model->where('uid', $userId)
            ->orderByDesc('entered_at')
            ->limit($limit)
            ->get();
    }

    public function recordEntry($userId, $roomId)
    {
        return $this->updateOrCreate(
            ['uid' => $userId, 'rid' => $roomId],
            ['entered_at' => now()]
        );
    }
}
