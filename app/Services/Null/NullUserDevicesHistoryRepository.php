<?php

namespace App\Services\Null;

use App\Contracts\UserDevicesHistoryRepositoryContract;

class NullUserDevicesHistoryRepository implements UserDevicesHistoryRepositoryContract
{
    public function all($perPage, $Page, $deviceToken, $request)
    {
        return new \Illuminate\Pagination\LengthAwarePaginator([], 0, $perPage ?: 15);
    }

    public function findOrFail(int $id, array $relations = [])
    {
        throw new \Illuminate\Database\Eloquent\ModelNotFoundException('SwitchAccount package is not installed.');
    }
}
