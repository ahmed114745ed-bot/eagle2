<?php

namespace App\Admin\Controllers;

use App\Helpers\Common;
use App\Models\Setting;
use App\Models\Timezone;
use Illuminate\Http\Request;
use Encore\Admin\Layout\Content;
use App\Admin\Controllers\MainController;
use App\Models\BrandImage;
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

        $stripe_server_secret = Common::getConfig('stripe_server_secret');
        $stripe_app_id = Common::getConfig('stripe_app_id');
        $opay_server_secret = Common::getConfig('opay_server_secret');
        $opay_app_id = Common::getConfig('opay_app_id');
        $app_sign = Common::getConfig('app_sign');
        $library = Common::getConfig('library');
        $brand_images = BrandImage::all();
        return $content
            ->header(__('Settings'))
            ->description('')

            ->body(view('admin.settings_new', compact([
                'settings',
                'timezones',
                'agora_app_id',
                'zego_server_secret',
                'zego_app_id',
                'app_sign',
                'library',
                'brand_images',

                'stripe_server_secret',
                'stripe_app_id',
                'opay_server_secret',
                'opay_app_id',
                ])));
    }

    public function save_image(Request $request){
        $name = Common::upload('images', $request->image);
        BrandImage::create([
            'name' => $name
        ]);
        return Common::apiResponse(true,'Success');
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
