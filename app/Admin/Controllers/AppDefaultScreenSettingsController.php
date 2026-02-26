<?php

namespace App\Admin\Controllers;

use App\Helpers\Common;
use App\Models\Setting;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\Cache;
use App\Admin\Controllers\MainController;


class AppDefaultScreenSettingsController extends MainController
{
    public $permission_name = 'default-screen-settings';

    public function index(Content $content)
    {
        $defaultScreen = Common::getSettingValue('default_screen') ?? 'audio_room';

        // Mapping of select option => setting key to check if enabled
        $options = [
            'audio_room' => 'audio_room',
            'live'       => 'live_status',
            'moment'     => 'moment_status',
            'game'       => 'game_status',
            'reels'      => 'reel_status',
            'chat'       => 'chat_status',
        ];

        // Filter options: only include if the setting key is 1
        $enabledOptions = [];
        foreach ($options as $key => $settingKey) {
            $value = Common::getSettingValue($settingKey) ?? 1;
            if ($value == 1) {
                $enabledOptions[$key] = $key; // key is also the value in <option>
            }
        }


        // dd($enabledOptions);
        return parent::index(
            $content
                ->title(__('Default App Screen Settings'))
                ->body(view('defaultScreen', [
                    'defaultScreen'   => $defaultScreen,
                    'enabledOptions'  => $enabledOptions,
                    'saveUrl'         => $this->saveUrl(),
                ]))
        );
    }

    private function saveUrl()
    {
        return admin_url('default-app-screen');
    }


    public function store()
    {
        $type = request('type');

        if (!in_array($type, ['audio_room', 'live', 'moment', 'game', 'reels', 'chat'])) {
            return response()->json(['status' => 'error', 'message' => 'Invalid type'], 400);
        }

        Setting::updateOrCreate(
            ['key' => 'default_screen'],
            ['value' => $type]
        );
        Cache::put('default_screen', $type);
        admin_success(__('done successfully ✅'));
        return redirect()->back();
    }
}
