<?php

namespace App\Helpers;

use App\Models\Vip;
use App\Models\Gift;
use App\Models\User;
use App\Models\Ware;
use App\Models\Agency;
use App\Models\Family;
use App\Models\OfficialMessage;
use Modules\Reals\Entities\Real;
use Illuminate\Support\Facades\DB;
use Modules\Moment\Entities\Moment;
use App\Models\OfficialMessageAdmin;
use Modules\Public\Http\Services\UserCounterServices;

class CustomNotification
{
    public function appName($lang)
    {
        return $lang == 'ar' ? config('app.name') : config('app.name_en');
    }

    public function senderLevel(int $userId)
    {
        $user = User::withoutAppends()->where('id', $userId)->first();
        if (!$user) {
            return 0;
        }
        $tokens_notfacion = DB::table('users')->where('id', $userId)->value('notification_id');
        $body_ar = __('api.sender_level', ['level' => $user->total_sender_level], 'ar');
        $body_en = __('api.sender_level', ['level' => $user->total_sender_level], 'en');
        $firebaseBody = ($user->lan === 'ar') ? $body_ar : $body_en;
        $title = __('Sender level upgraded');

        $image = Vip::where('level', $user->total_sender_level)->where('type', 2)->first()?->img;
        $data = getImagePath($image);
        $icon = $data;
        Common::send_firebase_notification($tokens_notfacion, $this->appName($user->lan), $firebaseBody, icon: $icon, data: $data);
        Common::sendOfficialMessage($user->id, title: $body_en, content: $title, titleAr: $body_ar, image: $data);
        (new UserCounterServices)->eventUser($user, 'official-messages', 1);
    }
    public function receiverLevel(int $userId)
    {
        $user = User::withoutAppends()->where('id', $userId)->first();
        if (!$user) {
            return 0;
        }
        $tokens_notfacion = DB::table('users')->where('id', $userId)->value('notification_id');
        $body_ar = __('api.receiver_level', ['level' => $user->total_received_level], 'ar');
        $body_en = __('api.receiver_level', ['level' => $user->total_received_level], 'en');
        $firebaseBody = ($user->lan === 'ar') ? $body_ar : $body_en;

        $title = __('Receiver level upgraded');
        $image = Vip::where('level', $user->total_received_level)->where('type', 1)->first()?->img;
        $data = getImagePath($image);
        $icon = $data;
        Common::send_firebase_notification($tokens_notfacion, $this->appName($user->lan), $firebaseBody, icon: $icon, data: $data);
        Common::sendOfficialMessage($user->id, title: $body_en, content: $title, titleAr: $body_ar, image: $image);
        (new UserCounterServices)->eventUser($user, 'official-messages', 1);
    }

    public function BackgroudRequest(User $user, $type = 0)
    {
        $tokens_notification = $user?->notification_id;
        if ($type == 0) {
            $body_ar = __('api.background_accept', ['name' => $user->name], 'ar');
            $body_en = __('api.background_accept', ['name' => $user->name], 'en');
        } else {
            $body_ar = __('api.background_refuse', ['name' => $user->name], 'ar');
            $body_en = __('api.background_refuse', ['name' => $user->name], 'en');
        }
        $firebaseBody = ($user->lan === 'ar') ? $body_ar : $body_en;
        Common::send_firebase_notification($tokens_notification, $this->appName($user->lan), $firebaseBody, messageType: 'backgroud-request');

        Common::sendOfficialMessage($user->id, title: $body_en, titleAr: $body_ar);
        (new UserCounterServices)->eventUser($user, 'official-messages', 1);
    }

    public function target(int $userId)
    {

        $user = User::withoutAppends()->where('id', $userId)->first();
        if (!$user) {
            return 0;
        }
        User::$withoutAppends = false;
        $tokens_notfacion = DB::table('users')->where('id', $userId)->value('notification_id');
        $salary               = $user->salary;
        $agencyName               = $user->agency?->name;
        $body_ar              = __('api.target', ['salary' => $salary, 'agency' => $agencyName], 'ar');
        $body_en              = __('api.target', ['salary' => $salary, 'agency' => $agencyName], 'en');
        $firebaseBody = ($user->lan === 'ar') ? $body_ar : $body_en;
        $data['user_id'] = $user?->id;
        Common::send_firebase_notification($tokens_notfacion,$this->appName($user->lan), $firebaseBody, data: $data,  messageType: 'achieve-target-monthly');
        Common::sendOfficialMessage($user->id, $body_en, __('New target'), titleAr: $body_ar);
        (new UserCounterServices)->eventUser($user, 'official-messages', 1);
    }




    public function momentComment(Moment $moment, User $userSender)
    {
        $momentUser                = $moment->user;
        $body_ar             = __('api.comment_moment', ['name' => $userSender->name], 'ar');
        $body_en             = __('api.comment_moment', ['name' => $userSender->name], 'en');
        $data['moment_id'] = @$moment->id;
        $this->sendNotificationWithImage($momentUser, $body_ar, $body_en, $userSender, 'moment Comment', 'moment-comment', $data);
    }

    public function likeReal(Real $real, User $userLike)
    {
        $reelUser                = $real->user;
        $body_ar             = __('api.like_your_real', ['name' => $userLike->name], 'ar');
        $body_en             = __('api.like_your_real', ['name' => $userLike->name], 'en');
        $data['real_id'] = @$real->id;
        $this->sendNotificationWithImage($reelUser, $body_ar, $body_en, $userLike, 'like real', 'like-real', $data);
    }

    public function CommentReal(Real $real, User $user)
    {
        $reelUser               = $real->user;
        $body_ar             = __('api.comment_real', ['name' => $user->name], 'ar');
        $body_en             = __('api.comment_real', ['name' => $user->name], 'en');
        $data['real_id'] = @$real->id;
        $this->sendNotificationWithImage($reelUser, $body_ar, $body_en, $user, 'real comment', 'real-comment', $data);
    }

    public function likeMoment(Moment $moment, User $user)
    {
        $momentUser               = $moment->user;
        $body_ar             = __('api.like_your_moment', ['name' => $user->name], 'ar');
        $body_en             = __('api.like_your_moment', ['name' => $user->name], 'en');
        $data['moment_id'] = @$moment->id;
        $this->sendNotificationWithImage($momentUser, $body_ar, $body_en, $user, 'like moment', 'like-moment', $data);
    }

    public function acceptAgency(Agency $agency, User $user)
    {
        $tokens_notification[] = DB::table('users')->where('id', $user->id)->value('notification_id');
        $body_ar = __('api.accept_agency', ['name' => $agency->name],  'ar');
        $body_en = __('api.accept_agency', ['name' => $agency->name],  'en');
        $firebaseBody = ($user?->lan === 'ar') ? $body_ar : $body_en;

        Common::send_firebase_notification($tokens_notification, $this->appName($user->lan), $firebaseBody);
        Common::sendOfficialMessage($user->id, title: $body_en, content: $agency->name, titleAr: $body_ar);
        (new UserCounterServices)->eventUser($user, 'official-messages');
    }

    public function visitProfile(User $user, User $visitor)
    {
        $tokens_notification = $user?->notification_id;
        $body_ar = __('api.visited_profile', ['name' => $visitor->name], 'ar');
        $body_en = __('api.visited_profile', ['name' => $visitor->name], 'en');
        $firebaseBody = ($user->lan === 'ar') ? $body_ar : $body_en;
        $data['image'] = getDriverUrl() . '/' . $visitor->profile->avatar;
        $data['user_id'] = $user?->id;
        $icon = $data['image'];
        Common::send_firebase_notification($tokens_notification,$this->appName($user->lan), $firebaseBody, icon: $icon, data: $data, messageType: 'visit-profile');

        Common::sendOfficialMessage($user->id, image: $visitor->profile->avatar, title: $body_en, content: $visitor->name, titleAr: $body_ar, fromUserId: $visitor->id);
        (new UserCounterServices)->eventUser($user, 'official-messages');
    }

    public function follow(User $receiver, User $user)
    {
        $tokens_notfacion = DB::table('users')->where('id', $receiver->id)->value('notification_id');
        $body_ar = __('api.followed_you', ['name' => $user->name], 'ar');
        $body_en = __('api.followed_you', ['name' => $user->name], 'en');
        $firebaseBody = ($receiver->lan === 'ar') ? $body_ar : $body_en;

        $data['image'] = getImagePath($user->profile->avatar);
        $icon = $data['image'];
        Common::send_firebase_notification($tokens_notfacion,$this->appName($user->lan), $firebaseBody, icon: $icon, data: $data, messageType: 'follow');
        Common::sendOfficialMessage($receiver->id, image: $user->profile->avatar, title: $body_en, content: $user->name, titleAr: $body_ar, fromUserId: $user->id);
        (new UserCounterServices)->eventUser($receiver, 'official-messages');
    }

    public function followBack(User $receiver, User $user)
    {
        $tokens_notfacion = DB::table('users')->where('id', $receiver->id)->value('notification_id');
        $body_ar = __('api.follow_back', ['name' =>  $user->name], 'ar');
        $body_en = __('api.follow_back', ['name' =>  $user->name], 'en');
        $firebaseBody = ($receiver->lan === 'ar') ? $body_ar : $body_en;
        $data['image'] = getImagePath($user->profile->avatar);
        $icon = $data['image'];

        Common::send_firebase_notification($tokens_notfacion, $this->appName($user->lan), $firebaseBody, icon: $icon, data: $data, messageType: 'followBack');
        Common::sendOfficialMessage($receiver->id, image: $user->profile->avatar, title: $body_en, content: $user->name, titleAr: $body_ar, fromUserId: $user->id);
        (new UserCounterServices)->eventUser($receiver, 'official-messages');
    }

    public function removeFamilyUser(Family $family, User $user)
    {
        $tokens_notification = $user?->notification_id;
        $body_ar = __('api.remove_from_family', ['name' => $family->name],  'ar');
        $body_en = __('api.remove_from_family', ['name' => $family->name],  'en');
        $firebaseBody = ($user?->lan === 'ar') ? $body_ar : $body_en;
        $icon = $family->img;

        Common::send_firebase_notification($tokens_notification,$this->appName($user->lan), $firebaseBody, $icon);
        Common::sendOfficialMessage(user_id: $user->id, content: $body_en, title: $family->name, titleAr: $body_ar, image: $family->img);
        (new UserCounterServices)->eventUser($user, 'official-messages');
    }

    public function adminFamily(Family $family, User $user)
    {
        $tokens_notification = $user?->notification_id;
        $body_ar = __('api.admin_family', ['name' => $family->name], 'ar');
        $body_en = __('api.admin_family', ['name' => $family->name], 'en');
        $firebaseBody = ($user?->lan === 'ar') ? $body_ar : $body_en;
        $icon = $family->img;
        Common::send_firebase_notification($tokens_notification, $this->appName($user->lan), $firebaseBody, $icon);
        Common::sendOfficialMessage(user_id: $user->id, content: $body_en, title: $family->name, titleAr: $body_ar, image: $family->img);
        (new UserCounterServices)->eventUser($user, 'official-messages');
    }

    public function officialMsg(OfficialMessageAdmin $msg)
    {
        $user_id = $msg->user_id;
        if ($user_id == 0) {
            $usersChunk = User::where('notification_id', '!=', NULL)->select(['id', 'notification_id'])->get()->unique('notification_id')->chunk(200);
            $body             = $msg->content;
            $title = $msg->title;
            $data['image'] = null;
            $icon = null;
            if ($msg->img) {
                $data['image'] = getImagePath($msg->img);
                $icon = $data['image'];
            }


            foreach ($usersChunk as $user) {
                $user = $user->pluck('notification_id')->toArray();
                Common::send_firebase_notification($user, $title, $body, icon: $icon, data: $data, messageType: 'system-msg');
            }
            (new UserCounterServices)->eventUsers('system-messages');
            // $users->chunk(200, function ($chunkedUsers) use ($usersTokenAr, $body, $title) {
            //     foreach ($chunkedUsers as $user) {
            //         Common::send_firebase_notification($usersTokenAr, $title, $body);
            //     }
            // });
        }

        if ($user_id != 0) {
            // $user = $msg->user;
            $tokens_notfacion = User::where('id', $user_id)->value('notification_id');
            $body             = $msg->content;
            $title = $msg->title;
            $data['image'] = null;
            $icon = null;
            if ($msg->img) {
                $data['image'] = getImagePath($msg->img);
                $icon = $data['image'];
            }

            Common::send_firebase_notification($tokens_notfacion, $title, $body, icon: $icon, data: $data, messageType: 'system-msg');
        }
    }


    public function acceptRequestAgency(User $user)
    {
        $tokens_notification = $user?->notification_id;
        $body_ar             = 'لقد تم قبول وكالتك';
        $body_en             = 'Your  agency has been accepted';
        $firebaseBody        = ($user?->lan === 'ar') ? $body_ar : $body_en;

        Common::send_firebase_notification($tokens_notification, $this->appName($user->lan), $firebaseBody, messageType: 'accept-agency');
        Common::sendOfficialMessage($user->id, $body_ar, '', titleAr: $body_ar);
        (new UserCounterServices)->eventUser($user, 'official-messages');
    }

    public function refuseRequestAgency(User $user)
    {
        $tokens_notification = $user?->notification_id;
        $body_ar             = 'لقد تم رفض وكالتك';
        $body_en             = 'Your agency has been refused';
        $firebaseBody        = ($user?->lan === 'ar') ? $body_ar : $body_en;
        Common::send_firebase_notification($tokens_notification, $this->appName($user->lan), $firebaseBody, messageType: 'accept-agency');
        Common::sendOfficialMessage($user->id, $body_ar, '', titleAr: $body_ar);
        (new UserCounterServices)->eventUser($user, 'official-messages');
    }

    public function sendMomentGift(User $senderUser, Gift $gift, $receivedUser, $momentId)
    {
        $tokens_notfacion = DB::table('users')->where('id', $receivedUser->id)->value('notification_id');
        $body_ar = __('api.user_send_gift_moment', ['name' =>  $senderUser->name, 'gift' => $gift->name], 'ar');
        $body_en = __('api.user_send_gift_moment', ['name' =>  $senderUser->name, 'gift' => $gift->name], 'en');
        $firebaseBody = ($receivedUser->lan === 'ar') ? $body_ar : $body_en;
        $data['image'] = getImagePath($gift->img);
        $data['moment_id'] = $momentId;
        $icon = $data['image'];

        Common::send_firebase_notification($tokens_notfacion, $this->appName($receivedUser->lan), $firebaseBody, icon: $icon, data: $data, messageType: 'send-moment-gift');
        Common::sendOfficialMessage($receivedUser->id, image: $senderUser->profile->avatar, title: $body_en, content: $senderUser->name, titleAr: $body_ar, fromUserId: $senderUser->id);
        (new UserCounterServices)->eventUser($receivedUser, 'official-messages');
    }

    public function mallSend($user, $toUser, $type, $bubbleImage)
    {
        $tokens_notification = $toUser?->notification_id;
        if ($type == 4) {
            $body_ar             = __('api.gift_aristocracy', ['name' => $user->name, 'ware' => '(بابل)'], 'ar');
            $body_en             = __('api.gift_aristocracy', ['name' => $user->name, 'ware' => '(Bubble)'], 'en');
        } else if ($type == 5) {
            $body_ar             = __('api.gift_aristocracy', ['name' => $user->name, 'ware' => '(ايطار)'], 'ar');
            $body_en             = __('api.gift_aristocracy', ['name' => $user->name, 'ware' => '(frame)'], 'en');
        } else if ($type == 11) {
            $body_ar             = __('api.gift_aristocracy', ['name' => $user->name, 'ware' => '(تاثير دخول)'], 'ar');
            $body_en             = __('api.gift_aristocracy', ['name' => $user->name, 'ware' => '(entering effect)'], 'en');
        } else {
            $body_ar             = __('api.gift_aristocracy', ['name' => $user->name, 'ware' => '(بابل, ايطار او تأثير دخول)'], 'ar');
            $body_en             = __('api.gift_aristocracy', ['name' => $user->name, 'ware' => '(Bubble ,frame or entering effect)'], 'en');
        }
        $firebaseBody        = ($toUser?->lan === 'ar') ? $body_ar : $body_en;
        $data['image'] = getImagePath($bubbleImage);
        $icon = $data['image'];

        Common::send_firebase_notification($tokens_notification,$this->appName($user->lan), $firebaseBody, icon: $icon, data: $data, messageType: 'mall-send');
        Common::sendOfficialMessage($toUser->id, $body_en, '', titleAr: $body_ar, fromUserId: $user->id);
        (new UserCounterServices)->eventUser($toUser, 'official-messages');
        (new UserCounterServices)->eventUser($toUser, 'mall');
    }

    public function acceptUserFamily(Family $family, ?User $user)
    {
        if (!$user) return;
        $tokens_notification = DB::table('users')->where('id', $user->id)->value('notification_id');
        $body_ar = __('api.accept_family', ['name' => $family->name], 'ar');
        $body_en = __('api.accept_family', ['name' => $family->name], 'en');
        $firebaseBody = ($user?->lan === 'ar') ? $body_ar : $body_en;
        $icon = $family->img;
        $data['family_id'] = $family->id;
        Common::send_firebase_notification($tokens_notification, $this->appName($user->lan), $firebaseBody, $icon, data: $data, messageType: 'accept-user-family');
        // Common::sendOfficialMessage($user->id, type: 1,$body_ar, $family->img, $body_en, $family->name);

        Common::sendOfficialMessage($user->id, type: 1, content: $body_ar, image: $family->img, title: $family->name);
        (new UserCounterServices)->eventUser($user, 'official-messages');
    }


    public function requestJoinFamily(Family $family, User $user)
    {
        $tokens_notification = DB::table('users')->where('id',  $family->owner?->id)->value('notification_id');
        $body_ar = __('api.send_Family', ['name' => $user->name], 'ar');
        $body_en = __('api.send_Family', ['name' => $user->name], 'en');
        $firebaseBody = ($family->owner?->lan === 'ar') ? $body_ar : $body_en;
        $icon = $user->profile->avatar;
        $data['family_id'] = $family->id;
        Common::send_firebase_notification($tokens_notification, $this->appName($user->lan), $firebaseBody, $icon, data: $data, messageType: 'request-join-family');
        Common::sendOfficialMessage($family->owner?->id, image: $user->profile->avatar, title: $body_en, content: $user->name, titleAr: $body_ar, fromUserId: $user->id);
        (new UserCounterServices)->eventUser($family->owner, 'official-messages');
    }

    public function family(Family $family, User $user)
    {
        $tokens_notification = $user?->notification_id;
        $body_ar = __('api.accept_family', ['name' => $family->name],  'ar');
        $body_en = __('api.accept_family', ['name' => $family->name],  'en');
        $firebaseBody = ($user?->lan === 'ar') ? $body_ar : $body_en;
        $icon = $family->img;
        $data['family_id'] = $family->id;
        Common::send_firebase_notification($tokens_notification, $this->appName($user->lan), $firebaseBody, $icon, data: $data, messageType: 'family');
        Common::sendOfficialMessage($user->id, content: $family->name, title: $body_en, titleAr: $body_ar, image: $family->img);
        (new UserCounterServices)->eventUser($family->owner, 'official-messages');
    }

    public function acceptAgencyApp(Agency $agencyName, User $user)
    {
        $tokens_notification[] = DB::table('users')->where('id', $user->id)->value('notification_id');
        $body_ar = __('api.accept_agency', ['name' => $agencyName->name],  'ar');
        $body_en = __('api.accept_agency', ['name' => $agencyName->name],  'en');
        $firebaseBody = ($user?->lan === 'ar') ? $body_ar : $body_en;
        $data['image'] = $agencyName->img;
        $data['agency_id'] = $agencyName->id;
        $icon = $agencyName->img;
        Common::send_firebase_notification($tokens_notification, $this->appName($user->lan), $firebaseBody, $icon, $data, messageType: 'accept-agency-app');
        Common::sendOfficialMessage($user->id, image: $agencyName->img, title: $body_en, content: $agencyName->name, titleAr: $body_ar);
        (new UserCounterServices)->eventUser($user, 'official-messages');
    }

    public function chargeAction(User $user, $request)
    {
        $tokens_notification[] = DB::table('users')->where('id', $user->id)->value('notification_id');
        $body_ar = __('api.got_coin', ['coins' => $request->amount, 'name' => $user->name],  'ar');
        $body_en = __('api.got_coin', ['coins' => $request->amount, 'name' => $user->name],  'en');
        $firebaseBody = ($user?->lan === 'ar') ? $body_ar : $body_en;
        $data['coins'] = $request->amount;
        Common::send_firebase_notification($tokens_notification, $this->appName($user->lan), $firebaseBody, data: $data, messageType: 'charge-action-notifaction');
        Common::sendOfficialMessage($user->id, $body_en, '', titleAr: $body_ar);
        (new UserCounterServices)->eventUser($user, 'official-messages');
    }

    public function banUser(User $user, $duration)
    {
        $tokens_notification = $user?->notification_id;
        $body_ar             = __('api.ban_user_id', ['duration' => $duration], 'ar');
        $body_en             = __('api.ban_user_id', ['duration' => $duration], 'en');
        $firebaseBody        = ($user?->lan === 'ar') ? $body_ar : $body_en;
        $data['user_id'] = $user?->id;
        Common::send_firebase_notification($tokens_notification, $this->appName($user->lan), $firebaseBody, data: $data, messageType: 'ban-user');
        Common::sendOfficialMessage($user->id, $body_en, '', titleAr: $body_ar);
        (new UserCounterServices)->eventUser($user, 'official-messages');
    }

    public function removeBanUser(User $user)
    {
        $tokens_notification = $user?->notification_id;

        $body_ar             = __('api.removeBan_user_id', locale: 'ar');
        $body_en             = __('api.removeBan_user_id', locale: 'en');

        $firebaseBody        = ($user?->lan === 'ar') ? $body_ar : $body_en;
        $data['user_id'] = $user?->id;
        Common::send_firebase_notification($tokens_notification, $this->appName($user->lan), $firebaseBody, data: $data, messageType: 'remove-ban-user');
        Common::sendOfficialMessage($user->id, $body_en, '', titleAr: $body_ar);
        (new UserCounterServices)->eventUser($user, 'official-messages');
    }

    public function vips(User $user, $duration, $img)
    {
        $tokens_notification = $user?->notification_id;
        $body_ar             = __('api.userVips', ['duration' => $duration, 'vip' => 'الاستقراطيه'], 'ar');
        $body_en             = __('api.userVips', ['duration' => $duration, 'vip' => 'VIP'], 'en');
        $firebaseBody        = ($user?->lan === 'ar') ? $body_ar : $body_en;
        $data['image'] = getImagePath($img);
        $icon = $data['image'];
        Common::send_firebase_notification($tokens_notification,$this->appName($user->lan), $firebaseBody, icon: $icon, data: $data, messageType: 'vips');
        Common::sendOfficialMessage($user->id, $body_en, '', titleAr: $body_ar);
        (new UserCounterServices)->eventUser($user, 'official-messages');
    }

    public function familyLevelUpgrade(int $familyId)
    {
        $family = Family::query()->with(['users' => fn($query) => $query->withoutAppends()->select(['users.id', 'notification_id', 'lan'])])->where('families.id', $familyId)->first();
        if (!$family) {
            return 0;
        }
        $users = $family->users;
        $usersTokenEn = $users->where('lan', '!=', 'ar')->pluck('notification_id');
        $usersTokenAr = $users->where('lan', 'ar')->pluck('notification_id');

        $body_ar              = __('api.family_level_up', ['name' => $family->name, 'level' => $family->currentLevel?->level ?? 0], 'ar');
        $body_en              = __('api.family_level_up', ['name' => $family->name, 'level' => $family->currentLevel?->level ?? 0], 'en');
        Common::sendOfficialMessage($family->id, $body_en, __('Family level up'), titleAr: $body_ar);
        $data['image'] = getImagePath($family->image);
        $data['family_id'] = $familyId;
        $icon = $data['image'];


        Common::send_firebase_notification($usersTokenEn, config('app.name_en'), $body_en, icon: $icon, data: $data, messageType: 'family-level-upgrade');
        Common::send_firebase_notification($usersTokenAr, config('app.name'), $body_ar, icon: $icon, data: $data, messageType: 'family-level-upgrade');
    }

    public function wareVip(User $user, $duration, $name, $image)
    {
        $tokens_notification = $user?->notification_id;
        $body_ar             = __('api.wareVips', ['duration' => $duration, 'name' => $name], 'ar');
        $body_en             = __('api.wareVips', ['duration' => $duration, 'name' => $name], 'en');
        $firebaseBody        = ($user?->lan === 'ar') ? $body_ar : $body_en;
        $data['image'] = getImagePath($image);
        $icon = $data['image'];
        Common::send_firebase_notification($tokens_notification,$this->appName($user->lan), $firebaseBody, icon: $icon, data: $data, messageType: 'ware-vip');
        Common::sendOfficialMessage($user->id, $body_en, '', titleAr: $body_ar);
        (new UserCounterServices)->eventUser($user, 'official-messages');
    }

    /**
     * @param mixed $momentUser
     * @param \Illuminate\Foundation\Application|array|string|\Illuminate\Contracts\Translation\Translator|\Illuminate\Contracts\Foundation\Application|null $body_ar
     * @param \Illuminate\Foundation\Application|array|string|\Illuminate\Contracts\Translation\Translator|\Illuminate\Contracts\Foundation\Application|null $body_en
     * @param User $userSender
     * @param $tokens_notification
     * @return void
     */
    public function sendNotificationWithImage(mixed $momentUser, string $body_ar, string $body_en, User $userSender, string $title, string $messageType, $data): void
    {
        $firebaseBody  = ($momentUser?->lan === 'ar') ? $body_ar : $body_en;
        $data['image'] = getDriverUrl() . '/' . $userSender->profile->avatar;
        $icon          = $userSender->profile?->avatar;
        $tokens_notification = $momentUser?->notification_id;

        Common::send_firebase_notification($tokens_notification, $this->appName($momentUser?->lan), $firebaseBody, $icon, $data, messageType: $messageType);
        Common::sendOfficialMessage($momentUser?->id, title: $body_en, content: $title, titleAr: $body_ar, fromUserId: $userSender->id);
    }

    public function acceptRequestToGetMony(User $user, $type = 1, $reason = null, $value = 0)
    {
        $tokens_notification = $user?->notification_id;
        if ($type == 1) {
            $body_ar             = __('api.accept_request_sallary', ["value" => $value], 'ar');
            $body_en             = __('api.accept_request_sallary', ["value" => $value], 'en');
        } else {
            $body_ar             = __('api.denied_request_sallary', ["value" => $value, 'reason' => $reason], 'ar');
            $body_en             = __('api.denied_request_sallary', ["value" => $value, 'reason' => $reason], 'ar');
        }
        $firebaseBody        = ($user?->lan === 'ar') ? $body_ar : $body_en;
        Common::send_firebase_notification($tokens_notification, $this->appName($user->lan), $firebaseBody, icon: '', data: [], messageType: 'request-message');
        Common::sendOfficialMessage($user->id, $body_ar, '', titleAr: $body_ar);
        (new UserCounterServices)->eventUser($user, 'official-messages');
    }

    public function roomAchievementTarget(User $user, $coins, $roomName)
    {
        $tokens_notification = $user?->notification_id;

        $body_ar              = __('api.roomTarget', ['coins' => $coins, 'room' => $roomName], 'ar');
        $body_en              = __('api.roomTarget', ['coins' => $coins, 'room' => $roomName], 'en');

        $firebaseBody        = ($user?->lan === 'ar') ? $body_ar : $body_en;
        $data['user_id'] = $user?->id;
        Common::send_firebase_notification($tokens_notification, $this->appName($user->lan), $firebaseBody, data: $data, messageType: 'room-achievement-target');
        Common::sendOfficialMessage($user->id, $body_en, '', titleAr: $body_ar);
        (new UserCounterServices)->eventUser($user, 'official-messages');
    }
}
