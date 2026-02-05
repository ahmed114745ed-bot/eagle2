<?php

namespace App\Services\Null;

use App\Contracts\PkRepositoryContract;

class NullPkRepository implements PkRepositoryContract
{
    public function getActiveByRoom($roomId)
    {
        return null;
    }

    public function getByRoom($roomId)
    {
        return collect();
    }

    public function endPk($pkId)
    {
        return false;
    }

    public function getPk($roomId)
    {
        return null;
    }

    public function findById($id)
    {
        return null;
    }

    public function roomPks($userId, $perPage, $page)
    {
        return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage, $page);
    }

    public function create(array $data)
    {
        return null;
    }

    public function update(array $data, $id)
    {
        return false;
    }
}
