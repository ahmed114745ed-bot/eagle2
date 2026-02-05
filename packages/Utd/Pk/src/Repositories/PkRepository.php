<?php

namespace Utd\Pk\Repositories;

use App\Contracts\PkRepositoryContract;
use Utd\Pk\Entities\Pk;

class PkRepository extends AbstractRepository implements PkRepositoryContract
{
    public function __construct()
    {
        parent::__construct(new Pk());
    }

    public function getActiveByRoom($roomId)
    {
        return $this->model->where('room_id', $roomId)
            ->where('status', 1)
            ->where('end_at', '>=', now())
            ->orderByDesc('id')
            ->first();
    }

    public function getByRoom($roomId)
    {
        return $this->model->where('room_id', $roomId)
            ->orderByDesc('id')
            ->get();
    }

    public function endPk($pkId)
    {
        return $this->update(['status' => 0], $pkId);
    }

    public function getPk($roomId)
    {
        return $this->model->query()->where('room_id', $roomId)->where('status', 1)->first();
    }

    public function findById($id)
    {
        return $this->model->query()->where('id', $id)->where('status', 1)->orderByDesc('id')->first();
    }

    public function roomPks($userId, $perPage, $page)
    {
        return $this->model->whereHas('room', function ($q) use ($userId) {
            $q->where('uid', $userId);
        })->orderByDesc('id')->paginate($perPage, ['*'], 'page', $page);
    }
}
