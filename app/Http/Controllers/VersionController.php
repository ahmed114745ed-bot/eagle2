<?php

namespace App\Http\Controllers;

use App\Helpers\Common;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Http\Request;
use App\Facades\UserHandling;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;


class VersionController extends Controller
{
    public function versionAndCache(Request $request)
    {
        $version = $request->version;
        $currentVersion  = settings()->get($request->OS == 'Huawei' ? 'huawei_current_version' : ($request->OS == 'IOS' ? 'ios_current_version' : 'android_current_version'));

        /*if ($version > $currentVersion){
            return Common::apiResponse(false, __('api_responses.disabled_version'));
        }*/
        $authorizationHeader = $request->header('Authorization');
        $token               = $this->getTokenFromHeader($authorizationHeader);
        [$isAuth, $user] = $this->isAuth($token);
        if ($user) {
            try {
                if ($request->OS == 'Android' && $user->android_version != $version) {
                    DB::table('users')->where('id', $user->id)->update(['android_version' => intval($version)]);
                } else if ($request->OS == 'IOS' && $user->ios_version != $version) {
                    DB::table('users')->where('id', $user->id)->update(['ios_version' => intval($version)]);
                } else if ($request->OS == 'Huawei' && $user->huawei_version != $version) {
                    DB::table('users')->where('id', $user->id)->update(['huawei_version' => intval($version)]);
                }
            } catch (\Exception $e) {
            }
            //            $user->update(['android_version' => $version]);
        }
        $isBan          = $this->haveBan(@$user->uuid);
        $isGiftUpdated  = $this->isUpdated('gifts_update_at', @$request->gift_time);
        $isIntroUpdated = $this->isUpdated('intro_updated_at', @$request->intro_time);
        $isBubbleFrameUpdated = $this->isUpdated('bubble_frame_updated_at', @$request->bubbles_frame_time);
        $isFrameUpdated = $this->isUpdated('frame_updated_at', @$request->frame_time);
        $isEmojiUpdated = $this->isUpdated('emoji_updated_at', @$request->emoji_time);
        $isExtraUpdated = $this->isUpdated('extra_updated_at', @$request->extra_time);
        $agencyBadges =$this->isUpdated('badges_agency_update_at', @$request->badges_agency_time);
        $wapple = $this->isUpdated('wappel_frame_updated_at', @$request->wabbles_frame_time);
        $isColorUpdated = $this->isUpdated('colors_updated_at', @$request->color_time);
        $ProfileFrameUpdated = $this->isUpdated('profile_frame_updated', @$request->profile_frame_updated);
        $reelSettings = Setting::where('key', 'reel_status')->first();

        $data = [
            'is_auth'         => $isAuth && !$isBan,
            'is_last_version' => $currentVersion <= (int)$version ,
            'is_force'        => $this->isForce($version, $request->OS),
            'is_show_shipping_agencies' => true,
            'badges-agency' =>  $agencyBadges,
            'cache_update' => [
                'gifts'  => $isGiftUpdated,
                'intro' => $isIntroUpdated,
                'emoji' => $isEmojiUpdated,
                'frames' => $isFrameUpdated,
                'extras' => $isExtraUpdated,
                'profile_frame_updated' => $ProfileFrameUpdated,
                'bubble_frame' => $isBubbleFrameUpdated,
                'wapple' => $wapple,
                'colors' => settings()->get('colors_updated_at') ?? false,
                'background' => settings()->get('ground_updated_at') ?? false,
                'host_agency' => (bool)\Cache::get('host_agency'),
                //intro - frames - extradata - emoji
            ],
            'enable_chat'  => settings()->get('chat_status') == "on",
            'reel_status' => (bool)$reelSettings?->value ?? false,
        ];

        //update current version for user

        return response()->json($data);
    }

    private function getTokenFromHeader(?string $authorizationHeader)
    {
        //remove bearer word from header auth
        $token = substr($authorizationHeader, 7);
        //split from id|token to $token
        $tokens = explode('|', $token);

        if (count($tokens) == 2) $token = $tokens[1];
        elseif (count($tokens) == 1) $token = $tokens[0];
        else $token = null;
        return $token;
    }

    private function isAuth($token): array
    {

        $user = Auth::guard('sanctum')->user();
        return [$user != null, $user];
    }

    // private function isForce($version)
    // {
    //     $isForce = false;

    //     if ($version < settings()->get('android_min_version')) $isForce = true;

    //     if (settings()->get('android_update_required') == 1 && $version != settings()->get('android_current_version') ) $isForce = true;

    //     return $isForce;
    // }

    private function haveBan(?string $uuid): bool
    {
        if ($uuid == null) return false;

        return UserHandling::haveBan($uuid, \request());
    }

    /**
     * @param $key
     * @param $time
     * @return bool
     */
    public function isUpdated($key, $time): bool
    {
        if ($time) {
            $time /= 1000;
        }
        $settingGiftUpdate = settings()->get($key);
        $isGiftUpdated     = true;
        // if ($settingGiftUpdate == null && $time != null) {
        //     $isGiftUpdated = false;

        // } elseif ($time) {
        //     $isGiftUpdated = $settingGiftUpdate > $time;
        // }

        if ($settingGiftUpdate === null && $time !== null) {
            $isGiftUpdated = false;
        }

        if ($time !== null) {
            $isGiftUpdated = $settingGiftUpdate > $time;
        }
        return $isGiftUpdated;
    }

    private function isForce($version, $OS)
    {
        $isForce     = false;
        $minKey      =
            $OS == 'Huawei' ? 'huawei_min_version' : ($OS == 'IOS' ? 'ios_min_version' : 'android_min_version');
        $requiredKey =
            $OS == 'Huawei' ? 'huawei_update_required' : ($OS == 'IOS' ? 'ios_update_required' : 'android_update_required');
        $currentKey  =
            $OS == 'Huawei' ? 'huawei_current_version' : ($OS == 'IOS' ? 'ios_current_version' : 'android_current_version');

        if ($version < settings()->get($minKey)) $isForce = true;
        if (settings()->get($requiredKey) == 1 && $version < settings()->get($currentKey)) $isForce = true;

        return $isForce;
    }

    private function updateUserCurrentVersion(?User $user, $version): bool
    {
        if (!$user) return false;
        if ($version == null || $user->current_app_version == $version) return false;

        $user->current_app_version = $version;
        $user->save();
        return true;
    }

    public function settings()
    {
        $config = getPusherConfig();
        $data = [
            'pusher' => $config,
        ];
        return Common::apiResponse(true, '', $data);
    }
}
