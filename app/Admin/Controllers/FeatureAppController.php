<?php

namespace App\Admin\Controllers;

use App\Models\Setting;
use Encore\Admin\Layout\Content;
use App\Admin\Controllers\MainController;
use App\Models\Config;
use App\Models\Language;

class FeatureAppController extends MainController
{

    public $permission_name = 'app-feature';
    /**
     * Title for current resource.
     *
     * @var string
     */
    protected $title = 'App Feature';

    // public function index(Content $content)
    // {
    //     $hostAgencySetting = Setting::where('key', 'host_agency')->first();
    //     $reelSettings = Setting::where('key', 'reel_status')->first();
    //     $youtubeSettings = Setting::where('key', 'youtube_status')->first();
    //     $liveSettings = Setting::where('key', 'live_status')->first();
    //     $hostAgencyStatus = ($hostAgencySetting && $hostAgencySetting->value == 1);
    //     $reelSettings = ($reelSettings && $reelSettings->value == 1);
    //     $youtubeSettings = ($youtubeSettings && $youtubeSettings->value == 1);
    //     $liveSettings = ($liveSettings && $liveSettings->value == 1);
    //     return parent::index($content
    //         ->view('app_feature', compact(['hostAgencyStatus', 'reelSettings', 'youtubeSettings', 'liveSettings'])));
    // }


    public function index(Content $content)
    {
        $keys = ['host_agency', 'reel_status', 'youtube_status', 'live_status', 'share_room_with_friends', 'room_cup_setting', 'moment_status', 'host_level_enabled'];

        $settings = Setting::whereIn('key', $keys)
            ->pluck('value', 'key')
            ->map(fn($value) => $value == 1);

        return parent::index(
            $content->view('app_feature', [
                'hostAgencyStatus' => $settings['host_agency'] ?? false,
                'reelSettings'     => $settings['reel_status'] ?? 1,
                'youtubeSettings'  => $settings['youtube_status'] ?? 1,
                'liveSettings'     => $settings['live_status'] ?? 1,
                'roomCupSetting'     => $settings['room_cup_setting'] ?? 0,
                'momentStatus'     => $settings['moment_status'] ?? 0,
                'hostLevel'       => $settings['host_level_enabled'] ?? 1,
                'shareRoom'       => $settings['share_room_with_friends'] ?? 1,
            ])
        );
    }
}
