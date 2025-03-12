<?php

namespace App\Admin\Controllers;

use App\Models\Setting;
use App\Models\Timezone;
use Encore\Admin\Controllers\AdminController;
use Illuminate\Http\Request;
use Encore\Admin\Layout\Content;

class SettingController extends AdminController
{
    protected $title = 'Settings';

    public function index(Content $content)
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        $timezones = Timezone::all();
        return $content
            ->header(__('Settings'))
            ->description('')

            ->body(view('admin.settings', compact('settings','timezones')));
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
