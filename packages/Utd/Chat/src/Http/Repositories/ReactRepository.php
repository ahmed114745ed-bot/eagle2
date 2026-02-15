<?php

namespace Utd\Chat\Http\Repositories;

use Utd\Chat\Entities\React;

class ReactRepository
{
    public function findExistingReact($chatRoomId, $messageId, $userId)
    {
        return React::findReact($chatRoomId, $messageId, $userId)
            ->first();
    }

    public function createReact($data)
    {
        return React::create($data);
    }

    public function deleteReact(React $react)
    {
        $react->delete();
    }
}
