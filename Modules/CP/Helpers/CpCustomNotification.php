<?php

namespace Modules\CP\Helpers;

use App\Helpers\Common;
use App\Models\User;
use Modules\Public\Http\Services\UserCounterServices;

class CpCustomNotification
{

    public function cancelCp(User $user)
    {
        $tokens_notification = $user?->notification_id;
        $body_ar = __('api.cancel_cp', [], 'ar');
        $body_en = __('api.cancel_cp',  [],'en');

        $firebaseBody = ($user->lan === 'ar') ? $body_ar : $body_en;
        Common::send_firebase_notification($tokens_notification, __('api.tik_chat'), $firebaseBody,messageType: 'backgroud-request' );

        Common::sendOfficialMessage($user->id, title: $body_en, titleAr: $body_ar);
    }

}
