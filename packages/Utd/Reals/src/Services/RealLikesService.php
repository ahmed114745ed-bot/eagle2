<?php

namespace Utd\Reals\Services;

use App\Models\User;
use Utd\Reals\Entities\Real;
use Utd\Reals\Entities\RealUserLike;

class RealLikesService extends BaseModelService
{
    public function __construct()
    {
        parent::__construct(new RealUserLike());
    }

    public function add(Real $real, User $user)
    {
        $userId = $user->id;
        $real->likes()->create([
            'user_id' => $userId,
        ]);

        return true;
    }

    public function likeOrUnLike(Real $real, User $user)
    {
        $userId = $user->id;
        $likeData = $real->likes()->where('user_id', $userId)->first();
        if ($likeData) {
            $likeData->delete();
        } else {
            $real->likes()->create([
                'user_id' => $userId,
            ]);
            (new RealsNotification())->likeReal($real, $user);
        }

        return true;
    }

    public function delete($like_id, Real $real)
    {
        $real->likes()->where('id', $like_id)->delete();
    }

    /**
     * @return mixed
     */
    public function showLikes($real)
    {
        return $real->likes()->with([
            'user' => function ($query) {
                $query->withoutAppends()->with('profile')->select(['id', 'name']);
            },
        ])->paginate(10);
    }
}
