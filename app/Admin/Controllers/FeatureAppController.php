<?php

namespace App\Admin\Controllers;

use App\Models\Setting;
use Encore\Admin\Layout\Content;
use App\Admin\Controllers\MainController;

class FeatureAppController extends MainController
{

    public $permission_name = 'app-feature';
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'App Feature';


    public function index(Content $content)
    {
        $keys = ['host_agency', 'reel_status', 'youtube_status', 'live_status','audio_room', 'share_room_with_friends', 'enable_room_boom','room_cup_setting', 'moment_status', 'host_level_enabled'];

        $settings = Setting::whereIn('key', $keys)
            ->pluck('value', 'key')
            ->map(fn($value) => $value == 1);

        return parent::index(
            $content->view('app_feature', [
                'hostAgencyStatus' => $settings['host_agency'] ?? false,
                'reelSettings'     => $settings['reel_status'] ?? 1,
                'youtubeSettings'  => $settings['youtube_status'] ?? 1,
                'liveSettings'     => $settings['live_status'] ?? 1,
                'audioRoom'        => $settings['audio_room'] ?? 1,
                'roomCupSetting'     => $settings['room_cup_setting'] ?? 0,
                'momentStatus'     => $settings['moment_status'] ?? 1,
                'hostLevel'       => $settings['host_level_enabled'] ?? 1,
                'shareRoom'       => $settings['share_room_with_friends'] ?? 1,
                'enableRoomBoom'  => $settings['enable_room_boom'] ?? 1,
            ])
        );
    }
}
