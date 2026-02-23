<?php

namespace App\Services\Null;

use App\Contracts\OvipRepositoryContract;
use Illuminate\Support\Collection;

class NullOvipRepository implements OvipRepositoryContract
{
    public function getOvip(): Collection
    {
        return collect();
    }

    public function getOvipByLevel(): Collection
    {
        return collect();
    }

    public function getBySortLevel(): Collection
    {
        return collect();
    }

    public function findById(int $id)
    {
        return null;
    }

    public function getAllWithPrivileges()
    {
        return collect();
    }

    public function findVipById($vipId)
    {
        return null;
    }

    public function create(array $data): mixed
    {
        return null;
    }

    public function update(array $data, $id): mixed
    {
        return false;
    }
}
