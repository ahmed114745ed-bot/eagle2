<?php

namespace App\Helpers;

use App\Models\NotificationTemplate;
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
    {          ////  G : 235
        return $lang == 'ar' ? config('app.name_ar') : config('app.name_en');
    }

    public function senderLevel(int $userId)
    {

        $user = User::withoutAppends()->where('id', $userId)->first();
        if (!$user) {
            return 0;
        }
    
        $tokens_notfacion = DB::table('users')->where('id', $userId)->value('notification_id');
    
        $notification = Common::getNotificationContent(
            'sender_level', 
            $user->lan ?? 'en', 
            ['level' => $user->total_sender_level]
        );
    
        $image = Vip::where('level', $user->total_sender_level)->where('type', 2)->first()?->img;
        $data = getImagePath($image);
        $icon = $data;
    
        Common::send_firebase_notification($tokens_notfacion, $notification['title'], $notification['body'], icon: $icon, data: $data);
        Common::sendOfficialMessage($user->id, title: $notification['title'], content: $notification['body'], titleAr: $notification['body'], image: $data);
    
        (new UserCounterServices)->eventUser($user, 'official-messages', 1);
        
        // $user = User::withoutAppends()->where('id', $userId)->first();
        // if (!$user) {
        //     return 0;
        // }
        // $tokens_notfacion = DB::table('users')->where('id', $userId)->value('notification_id');
        // $body_ar = __('api.sender_level', ['level' => $user->total_sender_level], 'ar');
        // $body_en = __('api.sender_level', ['level' => $user->total_sender_level], 'en');
        // $firebaseBody = ($user->lan === 'ar') ? $body_ar : $body_en;
        // $title = __('Sender level upgraded');

        // $image = Vip::where('level', $user->total_sender_level)->where('type', 2)->first()?->img;
        // $data = getImagePath($image);
        // $icon = $data;
        // Common::send_firebase_notification($tokens_notfacion, $this->appName($user->lan), $notification['body'], icon: $icon, data: $data);
        // Common::sendOfficialMessage($user->id, title: $notification['body'], content: $title, titleAr: $notification['body'], image: $data);
        // (new UserCounterServices)->eventUser($user, 'official-messages', 1);
    }
    public function receiverLevel(int $userId)
    {
        $user = User::withoutAppends()->where('id', $userId)->first();
        if (!$user) {
            return 0;
        }
        $tokens_notfacion = DB::table('users')->where('id', $userId)->value('notification_id');
       
        // $body_ar = __('api.receiver_level', ['level' => $user->total_received_level], 'ar');
        // $body_en = __('api.receiver_level', ['level' => $user->total_received_level], 'en');
        // $firebaseBody = ($user->lan === 'ar') ? $body_ar : $body_en;
        // $title = __('Receiver level upgraded');
        $notification = Common::getNotificationContent(
            'receiver_level', 
            $user->lan ?? 'en', 
            ['level' => $user->total_received_level]
        );
        $image = Vip::where('level', $user->total_received_level)->where('type', 1)->first()?->img;
        $data = getImagePath($image);
        $icon = $data;

        // Common::send_firebase_notification($tokens_notfacion, $this->appName($user->lan), $notification['body'], icon: $icon, data: $data);
        // Common::sendOfficialMessage($user->id, title: $notification['body'], content: $title, titleAr: $notification['body'], image: $image);
      
        Common::send_firebase_notification($tokens_notfacion, $notification['title'], $notification['body'], icon: $icon, data: $data);
        Common::sendOfficialMessage($user->id, title: $notification['title'], content: $notification['body'], titleAr: $notification['body'], image: $data);
    
        (new UserCounterServices)->eventUser($user, 'official-messages', 1);
    }

    public function BackgroudRequest(User $user, $type = 0)
    {
        $tokens_notification = $user?->notification_id;
        if ($type == 0) {
            // $body_ar = __('api.background_accept', ['name' => $user->name], 'ar');
            // $body_en = __('api.background_accept', ['name' => $user->name], 'en');
            $notification = Common::getNotificationContent(
                'background_accept', 
                $user->lan ?? 'en', 
                ['user_name' => $user->name]
            );
        } else {
            // $body_ar = __('api.background_refuse', ['name' => $user->name], 'ar');
            // $body_en = __('api.background_refuse', ['name' => $user->name], 'en');
            $notification = Common::getNotificationContent(
                'background_refuse', 
                $user->lan ?? 'en', 
                ['user_name' => $user->name]
            );
        }
        // $firebaseBody = ($user->lan === 'ar') ? $body_ar : $body_en;
        // Common::send_firebase_notification($tokens_notification, $this->appName($user->lan), $notification['body'], messageType: 'backgroud-request');
        // Common::sendOfficialMessage($user->id, title: $notification['body'], titleAr: $body_ar);

        Common::send_firebase_notification($tokens_notification, $notification['title'], $notification['body'], messageType: 'backgroud-request');
        Common::sendOfficialMessage($user->id, title: $notification['title'], content: $notification['body'], titleAr: $notification['body']);
    
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
        // $body_ar              = __('api.target', ['salary' => $salary, 'agency' => $agencyName], 'ar');
        // $body_en              = __('api.target', ['salary' => $salary, 'agency' => $agencyName], 'en');
        // $firebaseBody = ($user->lan === 'ar') ? $body_ar : $body_en;
        $notification = Common::getNotificationContent(
            'target', 
            $user->lan ?? 'en', 
            [
                'salary' => $salary,
                'agency' => $agencyName
                ]
        );
        $data['user_id'] = $user?->id;
        // Common::send_firebase_notification($tokens_notfacion, $this->appName($user->lan), $notification['body'], data: $data,  messageType: 'achieve-target-monthly');
        // Common::sendOfficialMessage($user->id, $notification['body'], __('New target'), titleAr: $body_ar);
        Common::send_firebase_notification($tokens_notfacion, $notification['title'], $notification['body'],  data: $data, messageType: 'achieve-target-monthly');
        Common::sendOfficialMessage($user->id, title: $notification['title'], content: $notification['body'], titleAr: $notification['body']);
    
        (new UserCounterServices)->eventUser($user, 'official-messages', 1);
    }




    public function momentComment(Moment $moment, User $userSender)
    {
        $momentUser                = $moment->user;
        // $body_ar             = __('api.comment_moment', ['name' => $userSender->name], 'ar');
        // $body_en             = __('api.comment_moment', ['name' => $userSender->name], 'en');
        $notification = Common::getNotificationContent(
            'comment_moment', 
            $momentUser->lan ?? 'en', 
            ['user_name' => $userSender->name]
        );
        $data['moment_id'] = @$moment->id;
        $this->sendNotificationWithImage($momentUser, $notification['body'], $userSender, $notification['title'], 'moment-comment', $data);
    }

    public function likeReal(Real $real, User $userLike)
    {
        $reelUser                = $real->user;
        // $body_ar             = __('api.like_your_real', ['name' => $userLike->name], 'ar');
        // $body_en             = __('api.like_your_real', ['name' => $userLike->name], 'en');
        $notification = Common::getNotificationContent(
            'like_real', 
            $reelUser->lan ?? 'en', 
            ['user_name' => $userLike->name]
        );
        
        $data['real_id'] = @$real->id;
        $this->sendNotificationWithImage($reelUser, $notification['body'], $userLike, $notification['title'], 'like-real', $data);
    }

    public function CommentReal(Real $real, User $user)
    {
        
        $reelUser               = $real->user;
        // $body_ar             = __('api.comment_real', ['name' => $user->name], 'ar');
        // $body_en             = __('api.comment_real', ['name' => $user->name], 'en');
        $notification = Common::getNotificationContent(
            'comment_real', 
            $reelUser->lan ?? 'en', 
            ['user_name' => $user->name]
        );
        $data['real_id'] = @$real->id;
        $this->sendNotificationWithImage($reelUser, $notification['body'], $user,  $notification['title'], 'real-comment', $data);
    }

    public function likeMoment(Moment $moment, User $user)
    {
        $momentUser               = $moment->user;
        // $body_ar             = __('api.like_your_moment', ['name' => $user->name], 'ar');
        // $body_en             = __('api.like_your_moment', ['name' => $user->name], 'en');
        $notification = Common::getNotificationContent(
            'like_moment', 
            $momentUser->lan ?? 'en', 
            ['user_name' => $user->name]
        );
        $data['moment_id'] = @$moment->id;
        $this->sendNotificationWithImage($momentUser, $notification['body'], $user, $notification['title'], 'like-moment', $data);
    }

    public function acceptAgency(Agency $agency, User $user)
    {
        $tokens_notification[] = DB::table('users')->where('id', $user->id)->value('notification_id');
        // $body_ar = __('api.accept_agency', ['name' => $agency->name],  'ar');
        // $body_en = __('api.accept_agency', ['name' => $agency->name],  'en');
        // $firebaseBody = ($user?->lan === 'ar') ? $body_ar : $body_en;
        $notification = Common::getNotificationContent(
            'accept_agency', 
            $user->lan ?? 'en', 
            ['agency_name' => $agency->name]
        );

        // Common::send_firebase_notification($tokens_notification, $this->appName($user->lan), $firebaseBody);
        // Common::sendOfficialMessage($user->id, title: $notification['body'], content: $agency->name, titleAr: $body_ar);
        Common::send_firebase_notification($tokens_notification, $notification['title'], $notification['body']);
        Common::sendOfficialMessage($user->id, title: $notification['body'], content: $agency->name, titleAr: $notification['body']);
        (new UserCounterServices)->eventUser($user, 'official-messages');
    }

    public function visitProfile(User $user, User $visitor)
    {
        $tokens_notification = $user?->notification_id;
        // $body_ar = __('api.visited_profile', ['name' => $visitor->name], 'ar');
        // $body_en = __('api.visited_profile', ['name' => $visitor->name], 'en');
        // $firebaseBody = ($user->lan === 'ar') ? $body_ar : $body_en;
        $notification = Common::getNotificationContent(
            'visited_profile', 
            $user->lan ?? 'en', 
            ['user_name' => $visitor->name]
        );
        $data['image'] = getDriverUrl() . '/' . $visitor->profile->avatar;
        $data['user_id'] = $user?->id;
        $icon = $data['image'];

        Common::send_firebase_notification($tokens_notification, $notification['title'], $notification['body'], icon: $icon, data: $data, messageType: 'visit-profile');
        Common::sendOfficialMessage($user->id, image: $visitor->profile->avatar, title: $notification['body'], content: $visitor->name, titleAr: $notification['body'], fromUserId: $visitor->id);
        (new UserCounterServices)->eventUser($user, 'official-messages');
    }

    public function follow(User $receiver, User $user)
    {
        $tokens_notfacion = DB::table('users')->where('id', $receiver->id)->value('notification_id');
        // $body_ar = __('api.followed_you', ['name' => $user->name], 'ar');
        // $body_en = __('api.followed_you', ['name' => $user->name], 'en');
        // $firebaseBody = ($receiver->lan === 'ar') ? $body_ar : $body_en;
        $notification = Common::getNotificationContent(
            'follow', 
            $receiver->lan ?? 'en', 
            ['user_name' => $user->name]
        );

        $data['image'] = getImagePath($user->profile->avatar);
        $icon = $data['image'];
        Common::send_firebase_notification($tokens_notfacion, $notification['title'], $notification['body'], icon: $icon, data: $data, messageType: 'follow');
        Common::sendOfficialMessage($receiver->id, image: $user->profile->avatar, title: $notification['body'], content: $user->name, titleAr: $notification['body'], fromUserId: $user->id);
        (new UserCounterServices)->eventUser($receiver, 'official-messages');
    }

    public function followBack(User $receiver, User $user)
    {
        $tokens_notfacion = DB::table('users')->where('id', $receiver->id)->value('notification_id');
        // $body_ar = __('api.follow_back', ['name' =>  $user->name], 'ar');
        // $body_en = __('api.follow_back', ['name' =>  $user->name], 'en');
        // $firebaseBody = ($receiver->lan === 'ar') ? $body_ar : $body_en;
        $notification = Common::getNotificationContent(
            'follow_back', 
            $receiver->lan ?? 'en', 
            ['user_name' => $user->name]
        );
        $data['image'] = getImagePath($user->profile->avatar);
        $icon = $data['image'];

        Common::send_firebase_notification($tokens_notfacion, $notification['title'], $notification['body'], icon: $icon, data: $data, messageType: 'followBack');
        Common::sendOfficialMessage($receiver->id, image: $user->profile->avatar, title: $notification['body'], content: $user->name, titleAr: $notification['body'], fromUserId: $user->id);
        (new UserCounterServices)->eventUser($receiver, 'official-messages');
    }

    public function removeFamilyUser(Family $family, User $user)
    {
        $tokens_notification = $user?->notification_id;
        // $body_ar = __('api.remove_from_family', ['name' => $family->name],  'ar');
        // $body_en = __('api.remove_from_family', ['name' => $family->name],  'en');
        // $firebaseBody = ($user?->lan === 'ar') ? $body_ar : $body_en;
        $notification = Common::getNotificationContent(
            'remove_from_family', 
            $user->lan ?? 'en', 
            ['family_name' => $family->name]
        );
        $icon = $family->img;

        Common::send_firebase_notification($tokens_notification, $notification['title'], $notification['body'], $icon);
        Common::sendOfficialMessage(user_id: $user->id, content: $notification['body'], title: $family->name, titleAr: $notification['body'], image: $family->img);
        (new UserCounterServices)->eventUser($user, 'official-messages');
    }

    public function adminFamily(Family $family, User $user)
    {
        $tokens_notification = $user?->notification_id;
        // $body_ar = __('api.admin_family', ['name' => $family->name], 'ar');
        // $body_en = __('api.admin_family', ['name' => $family->name], 'en');
        // $firebaseBody = ($user?->lan === 'ar') ? $body_ar : $body_en;
        $notification = Common::getNotificationContent(
            'admin_family', 
            $user->lan ?? 'en', 
            ['family_name' => $family->name]
        );
        $icon = $family->img;
        Common::send_firebase_notification($tokens_notification, $notification['title'], $notification['body'], $icon);
        Common::sendOfficialMessage(user_id: $user->id, content: $notification['body'], title: $family->name, titleAr: $notification['body'], image: $family->img);
        (new UserCounterServices)->eventUser($user, 'official-messages');
    }

    public  function officialMsg(OfficialMessageAdmin $msg)
    {
        $user_id = $msg->user_id;
        if ($user_id == 0) {
            $usersChunk = User::where('notification_id', '!=', NULL)->select(['id', 'notification_id'])->get()->unique('notification_id')->chunk(100);
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

              Common::send_firebase_notification($user, title: $title, body: $body, icon: $icon, data: $data, messageType: 'system-msg');
            // dd($data);
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
        // $body_ar             = 'لقد تم قبول وكالتك';
        // $body_en             = 'Your  agency has been accepted';
        // $firebaseBody        = ($user?->lan === 'ar') ? $body_ar : $body_en;
        $notification = Common::getNotificationContent(
            'accept_request_agency', 
            $user->lan ?? 'en'
            
        );

        Common::send_firebase_notification($tokens_notification, $notification['title'], $notification['body'], messageType: 'accept-agency');
        Common::sendOfficialMessage($user->id, $notification['body'], '', titleAr: $notification['body']);
        (new UserCounterServices)->eventUser($user, 'official-messages');
    }

    public function refuseRequestAgency(User $user)
    {
        $tokens_notification = $user?->notification_id;
        // $body_ar             = 'لقد تم رفض وكالتك';
        // $body_en             = 'Your agency has been refused';
        // $firebaseBody        = ($user?->lan === 'ar') ? $body_ar : $body_en;
        $notification = Common::getNotificationContent(
            'refuse_request_agency', 
            $user->lan ?? 'en'
            
        );
        Common::send_firebase_notification($tokens_notification, $notification['title'], $notification['body'], messageType: 'accept-agency');
        Common::sendOfficialMessage($user->id, $notification['body'], '', titleAr: $notification['body']);
        (new UserCounterServices)->eventUser($user, 'official-messages');
    }

    public function sendMomentGift(User $senderUser, Gift $gift, $receivedUser, $momentId)
    {
        $tokens_notfacion = DB::table('users')->where('id', $receivedUser->id)->value('notification_id');
        // $body_ar = __('api.user_send_gift_moment', ['name' =>  $senderUser->name, 'gift' => $gift->name], 'ar');
        // $body_en = __('api.user_send_gift_moment', ['name' =>  $senderUser->name, 'gift' => $gift->name], 'en');
        // $firebaseBody = ($receivedUser->lan === 'ar') ? $body_ar : $body_en;
        $notification = Common::getNotificationContent(
            'user_send_gift_moment', 
            $receivedUser->lan ?? 'en', 
            [
                'user_name' => $senderUser->name,
                'gift' => $gift->name
                ]
        );
        $data['image'] = getImagePath($gift->img);
        $data['moment_id'] = $momentId;
        $icon = $data['image'];

        Common::send_firebase_notification($tokens_notfacion, $notification['title'], $notification['body'], icon: $icon, data: $data, messageType: 'send-moment-gift');
        Common::sendOfficialMessage($receivedUser->id, image: $senderUser->profile->avatar, title: $notification['body'], content: $senderUser->name, titleAr: $notification['body'], fromUserId: $senderUser->id);
        (new UserCounterServices)->eventUser($receivedUser, 'official-messages');
    }

    public function mallSend($user, $toUser, $type, $bubbleImage)
    {
        $tokens_notification = $toUser?->notification_id;
        if ($type == 4) {
            // $body_ar             = __('api.gift_aristocracy', ['name' => $user->name, 'ware' => '(بابل)'], 'ar');
            // $body_en             = __('api.gift_aristocracy', ['name' => $user->name, 'ware' => '(Bubble)'], 'en');
           
            $ware = ($receivedUser->lan ?? 'en') === 'ar' ? '(بابل)' : '(Bubble)';
            $notification = Common::getNotificationContent(
                'gift_aristocracy', 
                $receivedUser->lan ?? 'en', 
                [
                    'user_name' => $toUser->name,
                    'ware_vip' => $ware
                ]
            );
        } else if ($type == 5) {
            // $body_ar             = __('api.gift_aristocracy', ['name' => $user->name, 'ware' => '(ايطار)'], 'ar');
            // $body_en             = __('api.gift_aristocracy', ['name' => $user->name, 'ware' => '(frame)'], 'en');
           
            $ware = ($receivedUser->lan ?? 'en') === 'ar' ? '(ايطار)' : '(frame)';
            $notification = Common::getNotificationContent(
                'gift_aristocracy', 
                $receivedUser->lan ?? 'en', 
                [
                    'user_name' => $toUser->name,
                    'ware_vip' => $ware
                ]            );
        } else if ($type == 11) {
            // $body_ar             = __('api.gift_aristocracy', ['name' => $user->name, 'ware' => '(تاثير دخول)'], 'ar');
            // $body_en             = __('api.gift_aristocracy', ['name' => $user->name, 'ware' => '(entering effect)'], 'en');
           
            $ware = ($receivedUser->lan ?? 'en') === 'ar' ? '(تاثير دخول)' : '(entering effect)';
            $notification = Common::getNotificationContent(
                'gift_aristocracy', 
                $receivedUser->lan ?? 'en', 
                [
                    'user_name' => $toUser->name,
                    'ware_vip' => $ware
                ]            );
        } else {
            // $body_ar             = __('api.gift_aristocracy', ['name' => $user->name, 'ware' => '(بابل, ايطار او تأثير دخول)'], 'ar');
            // $body_en             = __('api.gift_aristocracy', ['name' => $user->name, 'ware' => '(Bubble ,frame or entering effect)'], 'en');
          
            $ware = ($receivedUser->lan ?? 'en') === 'ar' ? '(بابل, ايطار او تأثير دخول)' : '(Bubble ,frame or entering effect)';
            $notification = Common::getNotificationContent(
                'gift_aristocracy', 
                $receivedUser->lan ?? 'en', 
                [
                    'user_name' => $toUser->name,
                    'ware_vip' => $ware
                ]);
        }

        // $firebaseBody        = ($toUser?->lan === 'ar') ? $body_ar : $body_en;
        $data['image'] = getImagePath($bubbleImage);
        $icon = $data['image'];

        Common::send_firebase_notification($tokens_notification, $notification['title'], $notification['body'], icon: $icon, data: $data, messageType: 'mall-send');
        Common::sendOfficialMessage($toUser->id, $notification['body'], '', titleAr: $notification['body'], fromUserId: $user->id);
        (new UserCounterServices)->eventUser($toUser, 'official-messages');
        (new UserCounterServices)->eventUser($toUser, 'mall');
    }

    public function acceptUserFamily(Family $family, ?User $user)
    {
        if (!$user) return;
        $tokens_notification = DB::table('users')->where('id', $user->id)->value('notification_id');
        // $body_ar = __('api.accept_family', ['name' => $family->name], 'ar');
        // $body_en = __('api.accept_family', ['name' => $family->name], 'en');
        // $firebaseBody = ($user?->lan === 'ar') ? $body_ar : $body_en;

        $notification = Common::getNotificationContent(
            'accept_family', 
            $user->lan ?? 'en', 
            [
                'family_name' => $family->name
             ]);

        $icon = $family->img;
        $data['family_id'] = $family->id;
        Common::send_firebase_notification($tokens_notification, $notification['title'], $notification['body'], $icon, data: $data, messageType: 'accept-user-family');
        // Common::sendOfficialMessage($user->id, type: 1,$notification['body'], $family->img, $notification['body'], $family->name);

        Common::sendOfficialMessage($user->id, type: 1, content: $notification['body'], image: $family->img, title: $family->name);
        (new UserCounterServices)->eventUser($user, 'official-messages');
    }


    public function requestJoinFamily(Family $family, User $user)
    {
        $tokens_notification = DB::table('users')->where('id',  $family->owner?->id)->value('notification_id');
        // $body_ar = __('api.send_Family', ['name' => $user->name], 'ar');
        // $body_en = __('api.send_Family', ['name' => $user->name], 'en');
        // $firebaseBody = ($family->owner?->lan === 'ar') ? $body_ar : $body_en;
        $notification = Common::getNotificationContent(
            'send_Family', 
            $family->owner->lan ?? 'en', 
            [
                'user_name' => $user->name
             ]);
        $icon = $user->profile->avatar;
        $data['family_id'] = $family->id;
        
        Common::send_firebase_notification($tokens_notification, $notification['title'], $notification['body'], $icon, data: $data, messageType: 'request-join-family');
        Common::sendOfficialMessage($family->owner?->id, image: $user->profile->avatar, title: $notification['body'], content: $user->name, titleAr: $notification['body'], fromUserId: $user->id);
        (new UserCounterServices)->eventUser($family->owner, 'official-messages');
    }

    public function family(Family $family, User $user)
    {
        $tokens_notification = $user?->notification_id;
        // $body_ar = __('api.accept_family', ['name' => $family->name],  'ar');
        // $body_en = __('api.accept_family', ['name' => $family->name],  'en');
        // $firebaseBody = ($user?->lan === 'ar') ? $body_ar : $body_en;

        $notification = Common::getNotificationContent(
            'accept_family', 
            $user->lan ?? 'en', 
            [
                'family_name' => $family->name
             ]);

        $icon = $family->img;
        $data['family_id'] = $family->id;
        Common::send_firebase_notification($tokens_notification, $notification['title'], $notification['body'], $icon, data: $data, messageType: 'family');
        Common::sendOfficialMessage($user->id, content: $family->name, title: $notification['body'], titleAr: $notification['body'], image: $family->img);
        (new UserCounterServices)->eventUser($family->owner, 'official-messages');
    }

    public function acceptAgencyApp(Agency $agencyName, User $user)
    {
        $tokens_notification[] = DB::table('users')->where('id', $user->id)->value('notification_id');
        // $body_ar = __('api.accept_agency', ['name' => $agencyName->name],  'ar');
        // $body_en = __('api.accept_agency', ['name' => $agencyName->name],  'en');
        // $firebaseBody = ($user?->lan === 'ar') ? $body_ar : $body_en;
        $notification = Common::getNotificationContent(
            'accept_agency', 
            $user->lan ?? 'en', 
            [
                'agency_name' => $agencyName->name
             ]);
        $data['image'] = $agencyName->img;
        $data['agency_id'] = $agencyName->id;
        $icon = $agencyName->img;
        Common::send_firebase_notification($tokens_notification, $notification['title'], $notification['body'], $icon, $data, messageType: 'accept-agency-app');
        Common::sendOfficialMessage($user->id, image: $agencyName->img, title: $notification['body'], content: $agencyName->name, titleAr: $notification['body']);
        (new UserCounterServices)->eventUser($user, 'official-messages');
    }

    public function chargeAction(User $user, $request)
    {
        $tokens_notification[] = DB::table('users')->where('id', $user->id)->value('notification_id');
        // $body_ar = __('api.got_coin', ['coins' => $request->amount, 'name' => $user->name],  'ar');
        // $body_en = __('api.got_coin', ['coins' => $request->amount, 'name' => $user->name],  'en');
        // $firebaseBody = ($user?->lan === 'ar') ? $body_ar : $body_en;
        $notification = Common::getNotificationContent(
            'got_coin', 
            $user->lan ?? 'en', 
            [
                'coins' => $request->amount,
                'user_name' => $user->name
             ]);
        $data['coins'] = $request->amount;
        Common::send_firebase_notification($tokens_notification, $notification['title'], $notification['body'], data: $data, messageType: 'charge-action-notifaction');
        Common::sendOfficialMessage($user->id, $notification['body'], '', titleAr: $notification['body']);
        (new UserCounterServices)->eventUser($user, 'official-messages');
    }

    public function banUser(User $user, $duration)
    {
        $tokens_notification = $user?->notification_id;
        // $body_ar             = __('api.ban_user_id', ['duration' => $duration], 'ar');
        // $body_en             = __('api.ban_user_id', ['duration' => $duration], 'en');
        // $firebaseBody        = ($user?->lan === 'ar') ? $body_ar : $body_en;
        $notification = Common::getNotificationContent(
            'ban_user_id', 
            $user->lan ?? 'en', 
            [
                'duration' => $duration,
             ]);
        $data['user_id'] = $user?->id;
        Common::send_firebase_notification($tokens_notification, $notification['title'], $notification['body'], data: $data, messageType: 'ban-user');
        Common::sendOfficialMessage($user->id, $notification['body'], '', titleAr: $notification['body']);
        (new UserCounterServices)->eventUser($user, 'official-messages');
    }

    public function removeBanUser(User $user)
    {
        $tokens_notification = $user?->notification_id;

        // $body_ar             = __('api.removeBan_user_id', locale: 'ar');
        // $body_en             = __('api.removeBan_user_id', locale: 'en');

        // $firebaseBody        = ($user?->lan === 'ar') ? $body_ar : $body_en;
        $notification = Common::getNotificationContent(
            'removeBan_user_id', 
            $user->lan ?? 'en', 
            );
        $data['user_id'] = $user?->id;
        Common::send_firebase_notification($tokens_notification, $notification['title'], $notification['body'], data: $data, messageType: 'remove-ban-user');
        Common::sendOfficialMessage($user->id, $notification['body'], '', titleAr: $notification['body']);
        (new UserCounterServices)->eventUser($user, 'official-messages');
    }

    public function vips(User $user, $duration, $img)
    {
        $tokens_notification = $user?->notification_id;
        // $body_ar             = __('api.userVips', ['duration' => $duration, 'vip' => 'الاستقراطيه'], 'ar');
        // $body_en             = __('api.userVips', ['duration' => $duration, 'vip' => 'VIP'], 'en');
        // $firebaseBody        = ($user?->lan === 'ar') ? $body_ar : $body_en;
        $vip = ($receivedUser->lan ?? 'en') === 'ar' ? '(الاستقراطيه)' : '(VIP)';

        $notification = Common::getNotificationContent(
            'userVips', 
            $user->lan ?? 'en', 
            [
                'duration' => $duration,
                'vip' => $vip,
             ]);
        $data['image'] = getImagePath($img);
        $icon = $data['image'];
        Common::send_firebase_notification($tokens_notification, $notification['title'], $notification['body'], icon: $icon, data: $data, messageType: 'vips');
        Common::sendOfficialMessage($user->id, $notification['body'], '', titleAr: $notification['body']);
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

        // $body_ar              = __('api.family_level_up', ['name' => $family->name, 'level' => $family->currentLevel?->level ?? 0], 'ar');
        // $body_en              = __('api.family_level_up', ['name' => $family->name, 'level' => $family->currentLevel?->level ?? 0], 'en');

        $notification = Common::getNotificationContent(
            'family_level_up', 
            $user->lan ?? 'en', 
            [
                'family_name' => $family->name,
                'family_level' => $family->currentLevel?->level ?? 0,
             ]);
       
        Common::sendOfficialMessage($family->id, $notification['body'], __('Family level up'), titleAr: $notification['body']);
        $data['image'] = getImagePath($family->image);
        $data['family_id'] = $familyId;
        $icon = $data['image'];


        Common::send_firebase_notification($usersTokenEn, config('app.name_en'), $notification['body'], icon: $icon, data: $data, messageType: 'family-level-upgrade');
        Common::send_firebase_notification($usersTokenAr, config('app.name_ar'), $notification['body'], icon: $icon, data: $data, messageType: 'family-level-upgrade');
    }

    public function wareVip(User $user, $duration, $name, $image)
    {
        $tokens_notification = $user?->notification_id;
        // $body_ar             = __('api.wareVips', ['duration' => $duration, 'name' => $name], 'ar');
        // $body_en             = __('api.wareVips', ['duration' => $duration, 'name' => $name], 'en');
        // $firebaseBody        = ($user?->lan === 'ar') ? $body_ar : $body_en;
        $notification = Common::getNotificationContent(
            'wareVips', 
            $user->lan ?? 'en', 
            [
                'duration' => $duration,
                'ware_vip' => $name ?? 0,
             ]);
        $data['image'] = getImagePath($image);
        $icon = $data['image'];
        Common::send_firebase_notification($tokens_notification, $notification['title'], $notification['body'], icon: $icon, data: $data, messageType: 'ware-vip');
        Common::sendOfficialMessage($user->id, $notification['body'], '', titleAr: $notification['body']);
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
    public function sendNotificationWithImage(mixed $momentUser, string $body, User $userSender, string $title, string $messageType, $data): void
    {
        // $firebaseBody  = ($momentUser?->lan === 'ar') ? $body_ar : $body_en;
        $data['image'] = getDriverUrl() . '/' . $userSender->profile->avatar;
        $icon          = $userSender->profile?->avatar;
        $tokens_notification = $momentUser?->notification_id;

        Common::send_firebase_notification($tokens_notification, $title, $body, $icon, $data, messageType: $messageType);
        Common::sendOfficialMessage(user_id: $momentUser?->id, title: $body, content: $title, titleAr: $body, fromUserId: $userSender->id);
    }

    public function acceptRequestToGetMony(User $user, $type = 1, $reason = null, $value = 0)
    {
        $tokens_notification = $user?->notification_id;
        if ($type == 1) {
            // $body_ar             = __('api.accept_request_sallary', ["value" => $value], 'ar');
            // $body_en             = __('api.accept_request_sallary', ["value" => $value], 'en');

            $notification = Common::getNotificationContent(
                'accept_request_sallary', 
                $user->lan ?? 'en', 
                [
                    'value' => $value ,
                 ]);

        } else {
            // $body_ar             = __('api.denied_request_sallary', ["value" => $value, 'reason' => $reason], 'ar');
            // $body_en             = __('api.denied_request_sallary', ["value" => $value, 'reason' => $reason], 'ar');
            $notification = Common::getNotificationContent(
                'denied_request_sallary', 
                $user->lan ?? 'en', 
                [
                    'value' => $value,
                    'reason' => $reason ,
                 ]);
        }
        // $firebaseBody        = ($user?->lan === 'ar') ? $body_ar : $body_en;
        Common::send_firebase_notification($tokens_notification, $notification['title'], $notification['body'], icon: '', data: [], messageType: 'request-message');
        Common::sendOfficialMessage($user->id, $notification['body'], '', titleAr: $notification['body']);
        (new UserCounterServices)->eventUser($user, 'official-messages');
    }

    public function roomAchievementTarget(User $user, $coins, $roomName)
    {
        $tokens_notification = $user?->notification_id;

        // $body_ar              = __('api.roomTarget', ['coins' => $coins, 'room' => $roomName], 'ar');
        // $body_en              = __('api.roomTarget', ['coins' => $coins, 'room' => $roomName], 'en');

        // $firebaseBody        = ($user?->lan === 'ar') ? $body_ar : $body_en;
        $notification = Common::getNotificationContent(
            'roomTarget', 
            $user->lan ?? 'en', 
            [
                'coins' => $coins ,
                'room' => $roomName 
             ]);
        $data['user_id'] = $user?->id;
        Common::send_firebase_notification($tokens_notification, $notification['title'], $notification['body'], data: $data, messageType: 'room-achievement-target');
        Common::sendOfficialMessage($user->id, $notification['body'], '', titleAr: $notification['body']);
        (new UserCounterServices)->eventUser($user, 'official-messages');
    }
}
