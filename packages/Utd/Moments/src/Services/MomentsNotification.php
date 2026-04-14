<?php

namespace Utd\Moments\Services;

use App\Helpers\Common;
use App\Helpers\CustomNotification;
use Utd\Gifts\Entities\Gift;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Public\Http\Services\UserCounterServices;
use Utd\Moments\Entities\Moment;

class MomentsNotification extends CustomNotification
{
    public function momentComment(Moment $moment, User $userSender): void
    {
        $momentUser = $moment->user;
        $lang = $momentUser->lan ?? 'en';
        $body = __('api.comment_moment', ['name' => $userSender->name], $lang);
        $title = __('api.momentComment', [], $lang);
        $data['moment_id'] = @$moment->id;
        $this->sendNotificationWithImage($momentUser, $body, $body, $userSender, $title, 'moment-comment', $data);
    }

    public function likeMoment(Moment $moment, User $user): void
    {
        $momentUser = $moment->user;
        $lang = $momentUser->lan ?? 'en';
        $body = __('api.like_your_moment', ['name' => $user->name], 'ar');
        $title = __('api.likeMoment', [], $lang);
        $data['moment_id'] = @$moment->id;
        $this->sendNotificationWithImage($momentUser, $body, $body, $user, $title, 'like-moment', $data);
    }

    public function sendMomentGift(User $senderUser, Gift $gift, $receivedUser, $momentId): void
    {
        $tokens_notfacion = DB::table('users')->where('id', $receivedUser->id)->value('notification_id');
        $lang = $receivedUser->lan ?? 'en';
        $body = __('api.user_send_gift_moment', ['name' => $senderUser->name, 'gift' => $gift->name], $lang);

        $data['image'] = getImagePath($gift->img);
        $data['moment_id'] = $momentId;
        $icon = $data['image'];

        if (!$receivedUser->is_logout) Common::send_firebase_notification($tokens_notfacion, $this->appName($receivedUser->lan), $body, icon: $icon, data: $data, messageType: 'send-moment-gift');
        Common::sendOfficialMessage($receivedUser->id, image: $senderUser->profile->avatar, title: $body, content: $senderUser->name, titleAr: $body, fromUserId: $senderUser->id);
        (new UserCounterServices)->eventUser($receivedUser, 'official-messages');
    }
}
