<?php

namespace Utd\Moments\Services;

use App\Models\User;
use Utd\Moments\Entities\Moment;

class MomentLikesService extends MomentBaseModelService
{
    public function __construct(Moment $model)
    {
        parent::__construct($model);
    }

    public function add(Moment $moment, User $user)
    {
        $userId = $user->id;
        $moment->likes()->create([
            'user_id' => $userId,
        ]);

        return true;
    }

    public function likeOrUnLike(Moment $moment, User $user)
    {
        $userId = $user->id;
        $likeData = $moment->likes()->where('user_id', $userId)->first();
        if ($likeData) {
            $likeData->delete();

            return 'un Like';
        }
        $moment->likes()->create([
            'user_id' => $userId,
        ]);

        return 'Like';

        return true;
    }

    public function delete($like_id, Moment $moment)
    {
        $moment->likes()->where('id', $like_id)->delete();
    }

    /**
     * @return mixed
     */
    public function showLikes($moment)
    {
        return $moment->likes()->with([
            'user' => function ($query) {
                $query->withoutAppends()->with('profile')->select(['id', 'name', 'uuid']);
            },
        ])->orderByDesc('id')->paginate(10);
    }
}
