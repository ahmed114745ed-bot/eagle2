<?php

namespace Utd\Room\Repositories;

use Utd\Room\Entities\RoomVisitor;

class RoomVisitorRepository extends AbstractRepository
{
    public function __construct()
    {
        parent::__construct(new RoomVisitor());
    }

    public function getByUser($id)
    {
        return $this->model->where('user_id', $id)->with('room')->get();
    }

    public function getByRoom($roomId)
    {
        return $this->model->where('room_id', $roomId)->with('user')->get();
    }

    public function addVisitor($userId, $roomId)
    {
        return $this->model->firstOrCreate([
            'user_id' => $userId,
            'room_id' => $roomId,
        ]);
    }

    public function removeVisitor($userId, $roomId)
    {
        return $this->model->where('user_id', $userId)
            ->where('room_id', $roomId)
            ->delete();
    }
}
