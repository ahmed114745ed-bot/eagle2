<?php

namespace App\Admin\Controllers;

use App\Helpers\Common;
use App\Models\Setting;
use App\Models\Timezone;
use Illuminate\Http\Request;
use Encore\Admin\Layout\Content;
use App\Admin\Controllers\MainController;
use Encore\Admin\Controllers\AdminController;
class ChargesSettingController extends MainController
{
    protected $title = 'Settings';
    public $permission_name = 'charge-settings';

    public function index(Content $content)
    {
        $settings = Setting::pluck('value', 'key')->toArray();

        return $content
            ->header(__('Settings'))
            ->description('')

            ->body(view('admin.charges_settings', compact('settings')));
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
