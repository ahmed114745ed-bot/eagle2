<?php

namespace Utd\Reals\Services;

use App\Helpers\CustomNotification;
use App\Models\User;
use Utd\Reals\Entities\Real;

class RealsNotification extends CustomNotification
{
    public function likeReal(Real $real, User $userLike): void
    {
        $reelUser = $real->user;
        $lang = $reelUser->lan ?? 'en';
        $body = __('api.like_your_real', ['name' => $userLike->name], $lang);
        $title = __('api.likeReal', [], $lang);
        $data['real_id'] = @$real->id;
        $this->sendNotificationWithImage($reelUser, $body, $body, $userLike, $title, 'like-real', $data);
    }

    public function CommentReal(Real $real, User $user): void
    {
        $reelUser = $real->user;
        $lang = $reelUser->lan ?? 'en';
        $body = __('api.comment_real', ['name' => $user->name], $lang);
        $title = __('api.realComment', [], $lang);
        $data['real_id'] = @$real->id;
        $this->sendNotificationWithImage($reelUser, $body, $body, $user, $title, 'real-comment', $data);
    }
}
