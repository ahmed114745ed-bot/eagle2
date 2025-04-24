<?php

namespace App\Admin\Controllers;

use App\Helpers\Common;
use App\Models\PaymentCoin;
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
        $app_sign = Common::getConfig('app_sign');
        $library = Common::getConfig('library');
        $brand_images = BrandImage::all();
        $paymentCoins = PaymentCoin::all();

        $pusher_app_id = Common::getConf('pusher_app_id');
        $pusher_app_key = Common::getConf('pusher_app_key');
        $pusher_app_secret = Common::getConf('pusher_app_secret');
        $firebase_api_key = Common::getConf('firebase_api_key');
        $firebase_auth_domain = Common::getConf('firebase_auth_domain');
        $firebase_database_url = Common::getConf('firebase_database_url');
        $supabase_url = Common::getConf('supabase_url');
        $supabase_key = Common::getConf('supabase_key');
        $supabase_service_role_key = Common::getConf('supabase_service_role_key');
        return $content
            ->header(__('Settings'))
            ->description('')

            ->body(view('admin.settings_new', compact('pusher_app_secret','pusher_app_key','pusher_app_id','settings','timezones','agora_app_id','zego_server_secret','zego_app_id','app_sign','library', 'brand_images', 'paymentCoins','firebase_api_key','firebase_auth_domain','firebase_database_url','supabase_url','supabase_key','supabase_service_role_key')));
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
