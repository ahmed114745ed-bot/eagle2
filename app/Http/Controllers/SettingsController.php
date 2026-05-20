<?php

namespace App\Http\Controllers;

use App\Events\ZegoFeatureEvent;
use App\helper\TimeHelper;
use Log;
use Cache;
use Carbon\Carbon;
use App\Models\Room;
use App\Models\User;
use App\Models\Config;
use App\Models\Target;
use App\Helpers\Common;
use App\Models\Setting;
use App\Models\Timezone;
use App\Models\BrandImage;
use App\Models\PaymentCoin;
use App\Models\UserSallary;
use Illuminate\Support\Str;
use App\Models\Notification;
use Illuminate\Http\Request;
use Encore\Admin\Facades\Admin;
use App\Jobs\ChangeCinemaModeJob;
use App\Models\Language;
use Encore\Admin\Auth\Permission;
use Illuminate\Support\Facades\File;
use App\Models\MonthlyDiamondReceive;
use App\Models\NotificationTranslation;

class SettingsController extends Controller
{
    public $permission_name = 'settings';
    public function downloadApp($id)
    {

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

        // UTD-STREAM Credentials
        $utd_stream_app_id = Common::getConfig('utd_stream_app_id');
        $utd_stream_server_secret = Common::getConfig('utd_stream_server_secret');
        $utd_stream_callback_secret = Common::getConfig('utd_stream_callback_secret');
        $utd_webhook_url = url('/api/utd-stream-webhook');

        return view('admin.settings', compact(
            'pusher_app_secret',
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
            'gamesLibrary',
            'utd_stream_app_id',
            'utd_stream_server_secret',
            'utd_stream_callback_secret',
            'utd_webhook_url'
        ));
    }


    public function update(Request $request)
    {
        // if (!Admin::user()->can('*')) {
        //     Permission::check('edit-' . $this->permission_name);
        // }
        $data = $request->except(['_token', 'current_tab', 'inner_tab_type']);

        if (
            ($request->has('shipping_coins') && !is_null($request->shipping_coins) && $request->shipping_coins != cache()->get('shipping_coins')) ||
            ($request->has('super_admin_coins') && !is_null($request->super_admin_coins) && $request->super_admin_coins != cache()->get('super_admin_coins')) ||
            ($request->has('zones_coins') && !is_null($request->zones_coins) && $request->zones_coins != cache()->get('zones_coins')) ||
            ($request->has('user_coins') && !is_null($request->user_coins) && $request->user_coins != cache()->get('user_coins'))
        ) {

            $zoneSetting = Setting::where('key', 'zones_coins')->first();
            $superAdminSetting = Setting::where('key', 'super_admin_coins')->first();
            $shippingSetting = Setting::where('key', 'shipping_coins')->first();
            if ($zoneSetting && $superAdminSetting && $shippingSetting) {

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
            $data['images_background'] = Common::upload('images', $request->file('app_background_image'));
        } else if ($request->background_type == 'gradient') {
            $data['gradient_1'] = $request->gradient_1;
            $data['gradient_2'] = $request->gradient_2;
            $data['gradient_3'] = $request->gradient_3;
        } elseif ($request->brand_background_type === 'image') {
            if (!empty($request->brand_image)) {
                $data['brand_background'] = $request->brand_image;
                $data['brand_background_image'] = $request->brand_image;
            } else if ($request->hasFile('brand_background_image')) {
                $data['brand_background'] = Common::upload('images', $request->file('brand_background_image'));
                $data['brand_background_image'] = $data['brand_background'];
            }
        }

        if ($request->has('brand_background_image_reset') && $request->brand_background_image_reset == '1') {
            $data['brand_background_image'] = null;
        }

        if ($request->has('zego_feature')) {
            if ($request->zego_feature == 0) {
                $zegoFeature = [
                    'zego_feature' => (bool)0,
                ];
                event(new ZegoFeatureEvent($zegoFeature));
            }
        }

        if ($request->brand_background_type == 'color') {
            $data['brand_background_image'] = null;
        }

        if ($request->app_title_en || $request->app_title_ar) {
            Cache::forget('app_title');
        }

        if ($request->hasFile('apple_service_file')) {
            $file_path = Common::upload('images', $request->apple_service_file);
            $data['apple_service_file'] = $file_path;
        }
        unset($data['app_background_image'], $data['brand_background_image_reset']);

        if ($request->has('timezone') || $request->has('week_start') || $request->has('week_end')) {
            TimeHelper::clearCache();
        }

        // Process and save settings
        foreach ($data as $key => $value) {

            if (Str::contains($key, ['color']) && (common::getSettingValue('app_primary_color') != $request->app_primary_color || common::getSettingValue('app_second_color') != $request->app_second_color || common::getSettingValue('app_white_color') != $request->app_white_color || common::getSettingValue('app_black_color') != $request->app_black_color || common::getSettingValue('app_grey_color') != $request->app_grey_color || common::getSettingValue('app_yellow_color') != $request->app_yellow_color)) {
                $cacheKey = 'colors_updated_at';
                settings()->set($cacheKey, true);
            } elseif ((common::getSettingValue('background_type') !== $request->background_type || $request->hasFile('app_background_image') || common::getSettingValue('background_color') !== $request->background_color || common::getSettingValue('gradient_2') !== $request->gradient_2 || common::getSettingValue('gradient_3') !== $request->gradient_3 || common::getSettingValue('gradient_1') !== $request->gradient_1)) {
                $cacheKey = 'ground_updated_at';
                settings()->set($cacheKey, true);
            } else {
                $cacheKey = $key . '_updated_at';
                settings()->set($cacheKey, true);
            }
            if ($value instanceof \Illuminate\Http\UploadedFile) {
                $value = Common::upload('images', $value);
            }

            if (!is_null($value)) {
                Setting::updateOrCreate(['key' => $key], ['value' => $value]);
                if (!$value instanceof \Illuminate\Http\UploadedFile) {
                    Cache::put($key, $value);
                }
            }

            if ($request->youtube_status == 0) {
                dispatch(new ChangeCinemaModeJob());

                // Room::where('mode', 5)->update(['mode' => 1]);
            }

            if ($key === 'app_fav_icon') {
                Cache::forget('favicon');
            }



            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
            // Clear old cache first
            Cache::forget($key);
            Cache::put($key, $value);

            // //  $key = str_contains($key, 'color') ? 'colors_updated_at' : $key.'_updated_at';
            // if (Str::contains($key, ['color'])) {
            //     $key = 'colors_updated_at';
            // } elseif (Str::contains($key, ['app_background', 'image1', 'image2', 'image3'])) {
            //     $key = 'app_background_updated_at';
            // } else {
            //     $key . '_updated_at';
            // }

        }

        if ($request->payment_getaway_id) {
            $paymentGetaway = PaymentCoin::find($request->payment_getaway_id);
            $key = "is_{$paymentGetaway->type}_active";

            $paymentGetaway->status = $request->$key;
            $paymentGetaway->save();
        }


        if ($request->has('user_coins')) {
            Config::query()->where('name', '=', 'one_usd_value_in_coins')->update(['value' => $request->user_coins]);
        }
        if ($request->has('default_language')) {
            Language::query()->update(['is_default' => 0]);

            Language::where('code', $request->default_language)->update(['is_default' => 1]);
        }

        // Clear all cache including rememberForever keys
        Cache::forget('all_settings');
        Cache::forget('all_configs');

        // Clear individual theme/color cache keys (critical for Octane)
        $themeKeys = [
            'primary_color', 'secondary_color', 'text_primary_color',
            'text_secondary_color', 'box_background_color', 'app_background',
            'brand_background_image', 'table_background_color', 'dark_mode',
            'brand_background_type', 'box_background_color',
        ];
        foreach ($themeKeys as $key) {
            Cache::forget($key);
        }

        Cache::flush();

        $redirectUrl = url(config('admin.route.prefix') . '/settings');

        if ($request->has('current_tab')) {
            $redirectUrl .= '?tab=' . $request->current_tab;
            if ($request->has('inner_tab_type')) {
                $redirectUrl .= '&type=' . $request->inner_tab_type;
            }
             if ($request->has('inner_tab_type_hash')) {
                $redirectUrl .= '#' . $request->inner_tab_type_hash;
            }
            admin_toastr(__('Settings updated successfully!'), 'success');
            return redirect($redirectUrl);
        }

        if ($request->has('audio_room')) {
            admin_toastr(__('Settings updated successfully!'), 'success');
            $url = url('admin/default-app-screen-settings');
            return redirect()->to($url);
        }

        admin_toastr(__('Settings updated successfully!'), 'success');
        return redirect()->back();
        //        return redirect($redirectUrl);
    }

    public function settingGift(Request $request)
    {
        $sum = $request->app_wallet_lucky_gift
            + $request->owner_lucky_gift
            + $request->host_lucky_gift;

        if ($sum !== 100) {
            return back()->withErrors([
                'gift_percentage' => 'The total gift percentage must equal 100.'
            ])->withInput();
        }

        $data = $request->all();

        foreach ($data as $key => $value) {
            Setting::updateOrCreate(['key' => $key], ['value' => $value]);
            $cacheKey = "percentage_{$key}";
            Cache::put($cacheKey, $value);
        }


        return back();
    }



    public function updateAppConfig(Request $request)
    {
        if (!Admin::user()->can('*')) {
            Permission::check('edit-' . $this->permission_name);
        }
        $data = $request->except(['_token', 'current_tab', 'inner_tab_type']);


        if ($request->background_type === 'color') {
            $data['app_background'] = $request->background_color;
            $data['background_color'] = $request->background_color;
        } elseif ($request->background_type === 'image' && $request->hasFile('background_image')) {
            $data['app_background'] = Common::upload('images', $request->file('background_image'));
            $data['background_image'] = $data['app_background'];
        } elseif ($request->background_type === 'gradient') {
            $data['gradient_1'] = $request->gradient_1;
            $data['gradient_2'] = $request->gradient_2;
            $data['gradient_3'] = $request->gradient_3;
        }


         elseif ($request->background_body_theme === 'image' && $request->hasFile('background_body_theme_image')) {
            $data['background_body_theme_image'] = Common::upload('images', $request->file('background_body_theme_image'));
        }

        // Primary Color
        $data['app_primary_color'] = $request->app_primary_color;

        // Bottom Nav
        $data['bottom_nav_bottom_color'] = $request->reset != 1 ? $request->bottom_color : null;
        $data['bottom_nav_active_color'] = $request->reset != 1 ? $request->active_color : null;
        $data['bottom_nav_inactive_color'] = $request->reset != 1 ? $request->inactive_color : null;

        // Text & Button Colors
        $data['text_header_color'] = $request->text_header_color;
        $data['button_text_color'] = $request->button_text_color;


        unset($data['app_background_image'], $data['brand_background_image_reset']);
        if ($request->reset) {
            $timestamp = Carbon::now()->timestamp;
            settings()->set('color_setting_updated_at', $timestamp);
        }
        // Process and save settings
        foreach ($data as $key => $value) {

            if (Str::contains($key, ['color']) && (common::getSettingValue('app_primary_color') != $request->app_primary_color || common::getSettingValue('app_second_color') != $request->app_second_color || common::getSettingValue('app_white_color') != $request->app_white_color || common::getSettingValue('app_black_color') != $request->app_black_color || common::getSettingValue('app_grey_color') != $request->app_grey_color || common::getSettingValue('app_yellow_color') != $request->app_yellow_color)) {
                $cacheKey = 'colors_updated_at';
                settings()->set($cacheKey, true);
            } elseif ((common::getSettingValue('background_type') !== $request->background_type || $request->hasFile('app_background_image') || common::getSettingValue('background_color') !== $request->background_color || common::getSettingValue('gradient_2') !== $request->gradient_2 || common::getSettingValue('gradient_3') !== $request->gradient_3 || common::getSettingValue('gradient_1') !== $request->gradient_1)) {
                $cacheKey = 'ground_updated_at';
                settings()->set($cacheKey, true);
            } else {
                $cacheKey = $key . '_updated_at';
                settings()->set($cacheKey, true);
            }
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
            // Clear old cache first
            Cache::forget($key);
            Cache::put($key, $value);
        }

        // Clear all cache including rememberForever keys
        Cache::forget('all_settings');
        Cache::flush();

        admin_toastr('تم تحديث الإعدادات بنجاح!', 'success');

        $redirectUrl = url(config('admin.route.prefix') . '/settings');

        if ($request->has('current_tab')) {
            $redirectUrl .= '?tab=' . $request->current_tab;
            if ($request->has('inner_tab_type')) {
                $redirectUrl .= '&type=' . $request->inner_tab_type;
            }
        }

        return redirect($redirectUrl);
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
            $users = MonthlyDiamondReceive::where('monthly_diamond_received', '>=', $target->diamonds)->where('month', now()->month)->where('year', now()->year)->get();
            $hasActiveTargets = $users->count() > 0;
        }

        return response()->json(['hasActiveTargets' => $hasActiveTargets]);
    }
}
