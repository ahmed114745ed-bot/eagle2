<?php

namespace App\Admin\Controllers;

use App\Helpers\Common;
use App\Models\Setting;
use App\Models\Timezone;
use Illuminate\Http\Request;
use Encore\Admin\Layout\Content;
use App\Admin\Controllers\MainController;
use Encore\Admin\Controllers\AdminController;

class SettingController extends MainController
{
    protected $title = 'Settings';
    public $permission_name = 'settings';

    public function index(Content $content)
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        $timezones = Timezone::all();
        $agora_app_id = Common::getConfig('app_id');
        $zego_server_secret = Common::getConfig('zego_server_secret');
        $zego_app_id = Common::getConfig('zego_app_id');
        $app_sign = Common::getConfig('app_sign');
        $library = Common::getConfig('library');
        return $content
            ->header(__('Settings'))
            ->description('')

            ->body(view('admin.settings', compact('settings','timezones','agora_app_id','zego_server_secret','zego_app_id','app_sign','library')));
    }

    public function saveSettings(Request $request)
    {
        $data = $request->except('_token');

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
        }

        return back()->with('success', 'تم تحديث الإعدادات بنجاح!');
    }
}
