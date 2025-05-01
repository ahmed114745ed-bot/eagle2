<?php

namespace App\Http\Controllers;

use App\Models\Config;
use App\Models\Target;
use App\Models\User;
use App\Models\UserSallary;
use Cache;
use App\Helpers\Common;
use App\Models\BrandImage;
use App\Models\Setting;
use App\Models\Timezone;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\NotificationTranslation;
use App\Models\PaymentCoin;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Encore\Admin\Auth\Permission;
use Encore\Admin\Facades\Admin;

class SettingsController extends Controller
{
    public $permission_name = 'settings';
    public function downloadApp(){

        $url = env('DOWNLOAD_URL');
        return view('downloadApp', compact('url'));
    }
    public function index()
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        $timezones = Timezone::all();
        $agora_app_id = Common::getConfig('app_id');
        $zego_server_secret = Common::getConfig('zego_server_secret');
        $zego_app_id = Common::getConfig('zego_app_id');
        $tencent_server_secret = Common::getConfig('tencent_server_secret');
        $tencent_app_id = Common::getConfig('tencent_app_id');
        $app_sign = Common::getConfig('app_sign');
        $library = Common::getConfig('library');
        $soundLibrary = Common::getConfig('sound_library');
        $videoLibrary = Common::getConfig('video_library');
        $gamesLibrary = Common::getConfig('games_library');
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
        return view('admin.settings', compact('pusher_app_secret',
                'pusher_app_key',
                'pusher_app_id',
                'settings',
                'timezones',
                'agora_app_id',
                'zego_server_secret',
                'zego_app_id',
                'app_sign',
                'library',
                'brand_images',
                'paymentCoins',
                'firebase_api_key',
                'firebase_auth_domain',
                'firebase_database_url',
                'supabase_url',
                'supabase_key',
                'supabase_service_role_key',
                'tencent_app_id',
                'tencent_server_secret',
                'soundLibrary',
                'videoLibrary',
                'gamesLibrary'));
    }


    public function update(Request $request)
    {

        if (!Admin::user()->can('*')){
            Permission::check('edit-'.$this->permission_name);
        }
        $data = $request->except('_token','zones_coins','super_admin_coins');



        if (
            ($request->has('shipping_coins') && !is_null($request->shipping_coins) && $request->shipping_coins != cache()->get('shipping_coins')) ||
            ($request->has('super_admin_coins') && !is_null($request->super_admin_coins) && $request->super_admin_coins != cache()->get('super_admin_coins')) ||
            ($request->has('zones_coins') && !is_null($request->zones_coins) && $request->zones_coins != cache()->get('zones_coins')) ||
            ($request->has('user_coins') && !is_null($request->user_coins) && $request->user_coins != cache()->get('user_coins'))
        ) {

            $zoneSetting = Setting::where('key', 'zones_coins')->first();
            $superAdminSetting = Setting::where('key', 'super_admin_coins')->first();
            $shippingSetting = Setting::where('key', 'shipping_coins')->first();
            if ($zoneSetting && $superAdminSetting && $shippingSetting ) {

                $userSalary = UserSallary::select('sallary', 'cut_amount')->first();

                        // if ($userSalary) {
                        //     $calculatedValue = $userSalary->sallary - $userSalary->cut_amount;

                        //     if ($calculatedValue > 0) {

                        //             admin_toastr(__('We can`t update the target system right now because some users still have active targets.'), 'error');
                        //             return back();

                        //     }
                        // }

                // if ($request->zones_coins < $request->super_admin_coins) {
                //     admin_toastr(__('Zones coins must be greater than  super admin coins'), 'error');
                //     return back();
                // }

                // if ($request->super_admin_coins < $request->shipping_coins) {
                //     admin_toastr(__('super admin coins must be greater than  agancy coins'), 'error');
                //     return back();
                // }

                if ($request->shipping_coins < $request->user_coins) {
                    admin_toastr(__('agancy coins must be greater than  user coins'), 'error');
                    return back();
                }





            }



        }

        if ($request->background_type === 'color') {
            $data['app_background'] = $request->background_color;
            $data['background_color'] = $request->background_color;
        } elseif ($request->background_type === 'image' && $request->hasFile('app_background_image')) {
            $data['app_background'] = Common::upload('images', $request->file('app_background_image'));
        } else if($request->background_type == 'gradient'){
            $data['gradient_1'] = $request->gradient_1;
            $data['gradient_2'] = $request->gradient_2;
            $data['gradient_3'] = $request->gradient_3;
        }
        elseif ($request->brand_background_type === 'image') {
            if(!empty($request->brand_image)){
                $data['brand_background'] = $request->brand_image;
                $data['brand_background_image'] = $request->brand_image;
            }
            else if($request->hasFile('brand_background_image')){
                $data['brand_background'] = Common::upload('images', $request->file('brand_background_image'));
                $data['brand_background_image'] = $data['brand_background'];
            }
        }

        if ($request->has('brand_background_image_reset') && $request->brand_background_image_reset == '1') {
            $data['brand_background_image'] = null;
        }

        if ($request->brand_background_type == 'color'){
            $data['brand_background_image'] = null;
        }

        if ($request->app_title_en || $request->app_title_ar){
            Cache::forget('app_title');
        }

        unset($data['app_background_image'], $data['brand_background_image_reset']);

        // Process and save settings
        foreach ($data as $key => $value) {
            if ($value instanceof \Illuminate\Http\UploadedFile) {
                $value = Common::upload('images', $value);
            }

            if (!is_null($value)) {
                    Setting::updateOrCreate(['key' => $key], ['value' => $value]);
                    if (!$value instanceof \Illuminate\Http\UploadedFile) {
                        Cache::put($key, $value);
                     }
            }
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
            Cache::put($key, $value);

          //  $key = str_contains($key, 'color') ? 'colors_updated_at' : $key.'_updated_at';
            $key = Str::contains($key, ['color', 'app_background','image1','image2','image3']) ? 'colors_updated_at' : $key.'_updated_at';
            settings()->set($key, true);

        }


        if( $request->has('user_coins')){
            Config::query()->where('name', '=','one_usd_value_in_coins')->update(['value' => $request->user_coins]);

       }

        admin_toastr('تم تحديث الإعدادات بنجاح!', 'success');

        return back();
    }

    public function store_notification_templates(Request $request)
    {

        $validated = $request->validate([
            'key' => 'required|unique:notifications,key',
        ]);

        $template = Notification::create(['key' => $validated['key']]);

        $languages = ['ar', 'en', 'tr', 'hi'];

        foreach ($languages as $code) {
            if ($request->has("title_{$code}") && $request->has("message_{$code}")) {
                NotificationTranslation::updateOrCreate(

                    [
                        'notification_id' => $template->id,
                        'language' => $code
                    ],
                    [
                        'title' => $request->input("title_{$code}"),
                        'message' => $request->input("message_{$code}"),
                    ]

                );
            }
        }



        admin_toastr('تم تحديث الإعدادات بنجاح!', 'success');

        return back();
    }

    public function edit_notification_templates(Request $request)
    {

        $template = Notification::findOrFail($request->id);

        $languages = ['ar', 'en', 'tr', 'hi'];

        foreach ($languages as $code) {
            if ($request->filled("title_{$code}") && $request->filled("message_{$code}")) {
                NotificationTranslation::updateOrCreate(
                    [
                        'notification_id' => $template->id,
                        'language' => $code
                    ],
                    [
                        'title'   => $request->input("title_{$code}"),
                        'message' => $request->input("message_{$code}"),
                    ]
                );
            }
        }

        admin_toastr('تم تحديث الإعدادات بنجاح!', 'success');

        return back();
    }

    public function checkActiveTargets(Request $request)
    {
        $target = Target::first();
        $hasActiveTargets = false;

        if ($target) {
            $users = User::where('monthly_diamond_received', '>=', $target->diamonds)->get();
            $hasActiveTargets = $users->count() > 0;
        }

        return response()->json(['hasActiveTargets' => $hasActiveTargets]);
    }
}
