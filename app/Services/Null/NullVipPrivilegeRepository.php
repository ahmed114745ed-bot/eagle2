<?php

namespace App\Services\Null;

use App\Contracts\VipPrivilegeRepositoryContract;
use Illuminate\Support\Collection;

class NullVipPrivilegeRepository implements VipPrivilegeRepositoryContract
{
    public function all(): Collection
    {
        return collect();
    }

    public function findById(int $id): mixed
    {
        return null;
    }

    public function listVip(?string $search = null): Collection
    {
        return collect();
    }

    public function getAllPrivileges()
    {
        return collect();
    }
}
