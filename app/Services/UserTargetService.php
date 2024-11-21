<?php

namespace App\Services;

use App\Models\User;
use App\Helpers\Common;
use App\Models\UserTarget;
use Modules\Public\Http\Services\UserCounterServices;

class UserTargetService
{
    //UserTargetAchieveJob
    public function sendNotification(UserTarget $userTarget)
    {
        // select user target
        $user         = $userTarget->user;
        $targetSalary = $userTarget->target_usd;
        $body_en = __('api.achieve_target', ['salary' => $targetSalary], 'en');
        $body_ar = __('api.achieve_target', ['salary' => $targetSalary], 'ar');
        $notificationIds[] = $user->notification_id;
        Common::sendOfficialMessage($user->id, $body_en, $user->name, titleAr: $body_ar);
        (new UserCounterServices)->eventUser($user, 'official-messages');
        $title = ($user->lan == 'ar') ?  config('app.name_ar') : config('app.name_en');
        $data["target_user"] = $userTarget->id;
        Common::send_firebase_notification($notificationIds, $title, $user->lan == 'ar' ? $body_ar : $body_en, data: $data, messageType: 'achieve-target');
    }
}
