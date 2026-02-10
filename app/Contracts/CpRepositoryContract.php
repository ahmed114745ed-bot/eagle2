<?php

namespace App\Contracts;

use Illuminate\Support\Collection;

interface CpRepositoryContract
{
    /**
     * Get CP ranking without specific relation type
     * 
     * @param int $type - 1: today, 2: this week, 3: this month
     * @return Collection
     */
    public function getCpRankingWithOutRelation(int $type);

    /**
     * Get all CPs for a user
     * 
     * @param int $userId
     * @return Collection
     */
    public function getByUser(int $userId);
}
