<?php

namespace App\Contracts;

interface VipRepositoryContract
{
    public function getByType($type);

    public function getByLevels($levelsList, $type);

    public function getByLevelsV2($levelsList, $type);

    public function badgesVip($type);

    public function findByLevel($level, $type);

    public function nextLevel($level, $type);

    public function findByType($type);

    public function getLevelGroups(): array;

    public function getLevelsByType(int $type);
}
