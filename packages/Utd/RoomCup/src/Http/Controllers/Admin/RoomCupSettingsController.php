<?php

namespace Utd\RoomCup\Http\Controllers\Admin;

use App\Models\Setting;
use Encore\Admin\Controllers\AdminController;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\Cache;

class RoomCupSettingsController extends AdminController
{
    protected $title = '';

    public function index(Content $content)
    {
        $settings = $this->getSettings();

        return $content
            ->title(__('Room Cup Settings'))
            ->body(view('roomcup::room_cup_settings', [
                'settings' => $settings,
                'saveUrl' => $this->saveUrl(),
            ]));
    }

    private function saveUrl()
    {
        return admin_url('room-cup-settings/save');
    }

    private function getSettings()
    {
        $default = [
            'enabled' => true,
            'type' => 'daily',
            'time' => '00:00',
            'day' => 0,
            'interval' => 1,
        ];

        $settings = [];

        foreach ($default as $key => $defaultValue) {
            $cacheKey = 'roomcup_' . $key;
            $value = Cache::get($cacheKey);

            if ($value === null) {
                $setting = Setting::where('key', $cacheKey)->first();
                $value = $setting ? $setting->value : $defaultValue;
                Cache::put($cacheKey, $value, now()->addDays(30));
            }

            if ($key === 'enabled') {
                $value = (bool) $value;
            } elseif (in_array($key, ['day', 'interval'])) {
                $value = (int) $value;
            }

            $settings[$key] = $value;
        }

        return $settings;
    }

    public function save()
    {
        $data = [
            'enabled' => request()->has('enabled'),
            'type' => request('type', 'daily'),
            'time' => request('time', '00:00'),
            'day' => (int) request('day', 0),
            'interval' => (int) request('interval', 1),
        ];

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(
                ['key' => 'roomcup_' . $key],
                ['value' => $value]
            );

            Cache::put('roomcup_' . $key, $value, now()->addDays(30));
        }

        admin_success('Saved successfully ✅');
        return redirect()->back();
    }
}
