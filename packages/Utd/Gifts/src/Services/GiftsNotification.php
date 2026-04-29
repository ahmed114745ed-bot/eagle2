<?php

namespace Utd\Gifts\Services;

use App\Helpers\Common;
use App\Helpers\CustomNotification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Modules\Public\Http\Services\UserCounterServices;
use Utd\Gifts\Entities\Gift;

class GiftsNotification extends CustomNotification
{
    public function sendMomentGift(User $senderUser, Gift $gift, $receivedUser, $momentId)
    {
        $tokens_notfacion = DB::table('users')->where('id', $receivedUser->id)->value('notification_id');
        $lang = $receivedUser->lan ?? 'en';
        $body = __('api.user_send_gift_moment', ['name' => $senderUser->name, 'gift' => $gift->name], $lang);

        $data['image'] = getImagePath($gift->img);
        $data['moment_id'] = $momentId;
        $icon = $data['image'];

        if (! $receivedUser->is_logout) {
            Common::send_firebase_notification($tokens_notfacion, $this->appName($receivedUser->lan), $body, icon: $icon, data: $data, messageType: 'send-moment-gift');
        }
        Common::sendOfficialMessage($receivedUser->id, image: $senderUser->profile->avatar, title: $body, content: $senderUser->name, titleAr: $body, fromUserId: $senderUser->id);
        (new UserCounterServices())->eventUser($receivedUser, 'official-messages');
    }
}
