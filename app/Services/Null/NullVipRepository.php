<?php

namespace App\Services\Null;

use App\Contracts\VipRepositoryContract;

class NullVipRepository implements VipRepositoryContract
{
    public function getByType($type)
    {
        return collect();
    }

    public function getByLevels($levelsList, $type)
    {
        return collect();
    }

    public function getByLevelsV2($levelsList, $type)
    {
        return collect();
    }

    public function badgesVip($type)
    {
        return collect();
    }

    public function findByLevel($level, $type)
    {
        return null;
    }

    public function nextLevel($level, $type)
    {
        return null;
    }

    public function findByType($type)
    {
        return null;
    }

    public function getLevelGroups(): array
    {
        return [
            'sender' => [],
            'receiver' => [],
            'charge' => [],
        ];
    }

    public function getLevelsByType(int $type)
    {
        return collect();
    }

    public function getLevels(array $levels)
    {
        return collect();
    }
}
