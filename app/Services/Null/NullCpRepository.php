<?php

namespace App\Services\Null;

use App\Contracts\CpRepositoryContract;
use Illuminate\Support\Collection;

class NullCpRepository implements CpRepositoryContract
{
    /**
     * Get CP ranking without relation - returns empty collection when package not installed
     */
    public function getCpRankingWithOutRelation(int $type)
    {
        return collect();
    }

    /**
     * Get all CPs for a user - returns empty collection when package not installed
     */
    public function getByUser(int $userId)
    {
        return collect();
    }
}
