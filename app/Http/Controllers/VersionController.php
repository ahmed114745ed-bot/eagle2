<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Helpers\Common;
use App\Models\Setting;
use App\Helpers\CacheHelper;
use Database\Seeders\config;
use Illuminate\Http\Request;
use App\Facades\UserHandling;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\Collection;

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
        $settings = $this->getSettingsArray();
        $appUrl = '';
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

        $links = [
            'Android' => $settings['android_link'] ?? null,
            'IOS'     => $settings['ios_link'] ?? null,
            'Huawei'  => $settings['huawei_link'] ?? null,
        ];

        $appUrl = $links[$request->OS] ?? null;

        $isBan          = $this->haveBan(@$user->uuid);
        $isGiftUpdated  = $this->isUpdated('gifts_update_at', @$request->gift_time);
        $isIntroUpdated = $this->isUpdated('intro_updated_at', @$request->intro_time);
        $isBubbleFrameUpdated = $this->isUpdated('bubble_frame_updated_at', @$request->bubbles_frame_time);
        $isFrameUpdated = $this->isUpdated('frame_updated_at', @$request->frame_time);
        $isEmojiUpdated = $this->isUpdated('emoji_updated_at', @$request->emoji_time);
        $isExtraUpdated = $this->isUpdated('extra_updated_at', @$request->extra_time);
        $agencyBadges = $this->isUpdated('badges_agency_update_at', @$request->badges_agency_time);
        $wapple = $this->isUpdated('wappel_frame_updated_at', @$request->wabbles_frame_time);
        $isColorSettingUpdated = $this->isUpdated('color_setting_updated_at', @$request->color_time);
        $ProfileFrameUpdated = $this->isUpdated('profile_frame_updated', @$request->profile_frame_updated);
        $isRoomBoomVideoUpdated = $this->isUpdated('room_boom_video_update_at', @$request->room_boom_video_update_at);


        $images = $this->isUpdated('images_updated_at', @$request->images_time);
        $ground = $this->isUpdated('ground_updated_at', @$request->ground_time);
        $colorsUpdate = $this->isUpdated('colors_updated_at', @$request->colors_updated_time);

        $default_background =  \DB::table('backgrounds')->where('enable', 1)->orderBy('id')->value('img');


        $data = [
            'is_auth'         => $isAuth && !$isBan,
            'is_last_version' => $currentVersion <= (int)$version,
            'is_force'        => $this->isForce($version, $request->OS),
            'is_show_shipping_agencies' => true,
            'images' => $images,
            'badges-agency' =>  $agencyBadges,
            'cache_update' => [
                'gifts'  => $isGiftUpdated,
                'intro' => $isIntroUpdated,
                'emoji' => $isEmojiUpdated,
                'frames' => $isFrameUpdated,
                'extras' => $isExtraUpdated,
                'profile_frame_updated' => $ProfileFrameUpdated,
                'bubble_frame' => $isBubbleFrameUpdated,
                'room_boom_videos' => $isRoomBoomVideoUpdated,
                'wapple' => $wapple ?? false,
                'colors' =>  $colorsUpdate,
                'background' => $ground,
                'host_agency' => (bool) ($settings['host_agency'] ?? true),
                'color_time'  => $isColorSettingUpdated,
                //intro - frames - extradata - emoji
            ],
            'enable_chat'  => settings()->get('chat_status') == "on",
            'reel_status'    => (bool) ($settings['reel_status'] ?? true),
            'youtube_status' => (bool) ($settings['youtube_status'] ?? true),
            'live_status'    => (bool) ($settings['live_status'] ?? true),
            'zego_feature'    => (bool) ($settings['zego_feature'] ?? true),
            'default_room_background'    => $default_background ?? '',
            'is_show_room_activity' => ($settings['room_cup'] ?? 0) == 1 || ($settings['room_cup_setting'] ?? 0) == 1,
            'is_pk_live_active' => (bool) ($settings['pk_live_action'] ?? false),
            'app_url' => @$appUrl,
            'is_new_theme_enabled' => (bool) ($settings['is_new_theme_enabled'] ?? false),
            'moment_status'  => (bool) ($settings['moment_status'] ?? true),
            'is_show_host_levels' =>
            intval($settings['host_level_action'] ?? 0) === 1
                && intval($settings['host_level_enabled'] ?? 0) === 1,
            "is_share_with_friends" => (bool)($settings['share_room_with_friends'] ?? true),
            'is_show_grid_view' => (bool) Common::getConf('show_room') ?? false,

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
        if ($time > 9999999999) {
            // Convert milliseconds to seconds
            $time = (int) ($time / 1000);
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

    /**
     * @return mixed
     */
    public function getSettingsArray()
    {
        $settings = Cache::get('all_settings');
        if (!$settings) {
            $settings = CacheHelper::cacheSettings();
        }
        
        return $settings->whereIn('key', ['reel_status', 'youtube_status', 'share_room_with_friends', 'live_status', 'host_agency', 'zego_feature', 'huawei_link', 'host_level_enabled', 'host_level_action', 'ios_link', 'android_link', 'room_cup', 'room_cup_setting', 'is_new_theme_enabled', 'pk_live_action', 'moment_status'])->pluck('value', 'key')->toArray();
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
