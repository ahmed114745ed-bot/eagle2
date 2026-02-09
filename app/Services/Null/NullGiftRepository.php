<?php

namespace App\Services\Null;

use App\Contracts\GiftRepositoryContract;

class NullGiftRepository implements GiftRepositoryContract
{
    public function all($type = null)
    {
        return collect();
    }

    public function getByCategory($categoryId)
    {
        return collect();
    }

    public function findById($id)
    {
        return null;
    }

    public function create(array $data)
    {
        return null;
    }

    public function update($id, array $data)
    {
        return false;
    }

    public function allAchievementGift()
    {
        return collect();
    }
}
