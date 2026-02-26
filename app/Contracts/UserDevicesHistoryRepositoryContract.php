<?php

namespace App\Contracts;

interface UserDevicesHistoryRepositoryContract
{
    public function all($perPage, $Page, $deviceToken, $request);

    public function findOrFail(int $id, array $relations = []);
}
