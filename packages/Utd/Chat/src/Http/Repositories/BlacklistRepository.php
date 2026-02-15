<?php

namespace Utd\Chat\Http\Repositories;

use App\Models\BlackList;

class BlacklistRepository
{
    public function isUserBlocked($userId, $fromUserId)
    {
        return BlackList::BetweenUsers($userId, $fromUserId)->exists();
    }
}
