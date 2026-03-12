<?php

namespace App\Admin\Controllers;

use App\Admin\Controllers\MainController;
use App\Helpers\Common;
use App\Models\BrandImage;
use App\Models\Country;
use App\Models\GameProviderSetting;
use App\Models\Language;
use App\Models\PaymentCoin;
use App\Models\Setting;
use App\Models\Timezone;
use Cache;
use Encore\Admin\Layout\Content;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingController extends MainController
{
    protected $title = 'Settings';
    public $permission_name = 'settings';

    public function index(Content $content)
    {
        $settings = Setting::pluck('value', 'key')->toArray();
        $timezones = Timezone::all();
        $agora_app_id = Common::getConfig('app_id');
        $agora_app_certificate = Common::getConfig('agora_app_certificate');
        $zego_server_secret = Common::getConfig('zego_server_secret');
        $zego_app_id = Common::getConfig('zego_app_id');
        $tencent_server_secret = Common::getConfig('tencent_server_secret');
        $tencent_app_id = Common::getConfig('tencent_app_id');
        $app_sign = Common::getConfig('app_sign');
        $library = Common::getConfig('library');
        $soundLibrary = Common::getConfig('sound_library');
        $videoLibrary = Common::getConfig('video_library');
        $liveLibrary = Common::getConfig('live_library');
        $gamesLibrary = Common::getConfig('games_library');
        $brand_images = BrandImage::all();
        $paymentCoins = PaymentCoin::with('settings')->uniqueTypes()->orderByDesc('status')->get();
        $pusher_app_id = Common::getConf('pusher_app_id');
        $pusher_app_key = Common::getConf('pusher_app_key');
        $pusher_app_secret = Common::getConf('pusher_app_secret');
        $pusher_app_cluster = Common::getConf('pusher_app_cluster');
        $firebase_api_key = Common::getConf('firebase_api_key');
        $firebase_auth_domain = Common::getConf('firebase_auth_domain');
        $firebase_database_url = Common::getConf('firebase_database_url');
        $supabase_url = Common::getConf('supabase_url');
        $supabase_key = Common::getConf('supabase_key');
        $zego_filter_enabled = Common::getConf('zego_filter_enabled');
        $is_auto_preview = Common::getConf('is_auto_preview');
        $countries = Country::select(['id', 'name', 'e_name'])->get();
        $languages = Language::select(['id', 'name', 'code'])->get();
        $chargeTabType = request()->get('type', 'Experience');
        $zego_token = Common::getConf('zego_token');
        $zego_key = Common::getConf('zego_key');
        $appCoinRate = data_get($settings, 'app_coin_rate');
        $gameSettings = GameProviderSetting::all()->keyBy('provider_code');
        $bytesunSettings = $gameSettings->get('bytesun');
        $quantumNexusSettings = $gameSettings->get('quantum_nexus');
        $zeroGamesSettings = $gameSettings->get('zero_games');

        $supabase_service_role_key = Common::getConf('supabase_service_role_key');
        $userTransferRateEnabled = data_get($settings, 'user_transfer_rate_enabled');
        $userTransferCoinRate = data_get($settings, 'user_transfer_coin_rate');
        return parent::index($content
            ->header(__('Settings'))
            ->description('   ')
            ->body(view('admin.settings_new', compact([
                'zeroGamesSettings',
                'bytesunSettings',
                'quantumNexusSettings',
                'pusher_app_secret',
                'chargeTabType',
                'zego_token',
                'zego_key',
                'pusher_app_key',
                'pusher_app_id',
                'pusher_app_cluster',
                'settings',
                'languages',
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
                'liveLibrary',
                'gamesLibrary',
                'agora_app_certificate',
                'zego_filter_enabled',
                'is_auto_preview',
                'countries',
                'appCoinRate',
                'userTransferRateEnabled',
                'userTransferCoinRate'
            ]))));
    }

    public function save_image(Request $request)
    {
        $name = Common::upload('images', $request->image);
        BrandImage::create([
            'name' => $name
        ]);
        return Common::apiResponse(true, 'Success');
    }

    public function saveSettings(Request $request)
    {
        try {
            $data = $request->except(['_token', 'current_tab', 'inner_tab_type']);

            foreach ($data as $key => $value) {
                Setting::updateOrCreate(['key' => $key], ['value' => $value]);
                // Clear old cache first, then set new value
                Cache::forget($key);
                Cache::put($key, $value, now()->addYear());
            }

            // Clear all settings cache for Octane
            Cache::forget('all_settings');
            Cache::flush();

            $redirectUrl = url(config('admin.route.prefix') . '/settings');

            if ($request->has('current_tab')) {
                $redirectUrl .= '?tab=' . $request->current_tab;
                if ($request->has('inner_tab_type')) {
                    $redirectUrl .= '&type=' . $request->inner_tab_type;
                }
            }

            admin_success('تمت العملية', 'تم تحديث الإعدادات بنجاح!');
            return redirect($redirectUrl);
        } catch (Exception $exception) {
            return back()->with('error', $exception->getMessage());
        }
    }


    public function updateRoomCup(Request $request): JsonResponse
    {
        try {
            Setting::updateOrCreate(
                ['key' => 'room_cup'],
                ['value' => $request->value]
            );

            Cache::forever('room_cup', $request->value);
            \Artisan::call('cache:clear');

            return Common::apiResponse(true, 'created successfully');
        } catch (Exception $exception) {
            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }

    public function updateRoomBoom(Request $request): JsonResponse
    {
        try {
            Setting::updateOrCreate(
                ['key' => 'room_boom'],
                ['value' => $request->value]
            );

            Cache::forever('room_boom', $request->value);
            \Artisan::call('cache:clear');

            return Common::apiResponse(true, 'created successfully');
        } catch (Exception $exception) {
            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }

    public function updateRemainingDiamonds(Request $request): JsonResponse
    {
        try {
            Setting::updateOrCreate(
                ['key' => 'remaining_diamonds_action'],
                ['value' => $request->value]
            );

            Cache::forever('remaining_diamonds_action', $request->value);
            \Artisan::call('cache:clear');

            return Common::apiResponse(true, 'created successfully');
        } catch (Exception $exception) {
            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }

    public function updateHostLevel(Request $request): JsonResponse
    {
        try {
            Setting::updateOrCreate(
                ['key' => 'host_level_action'],
                ['value' => $request->value]
            );

            Cache::forever('host_level_action', $request->value);
            \Artisan::call('cache:clear');

            return Common::apiResponse(true, 'created successfully');
        } catch (Exception $exception) {
            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }


    public function updatePkLive(Request $request): JsonResponse
    {
        try {
            Setting::updateOrCreate(
                ['key' => 'pk_live_action'],
                ['value' => $request->value]
            );

            Cache::forever('pk_live_action', $request->value);
            \Artisan::call('cache:clear');

            return Common::apiResponse(true, 'created successfully');
        } catch (Exception $exception) {
            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }

    public function updateLuckyGifts(Request $request): JsonResponse
    {
        try {
            Setting::updateOrCreate(
                ['key' => 'lucky_gifts_action'],
                ['value' => $request->value]
            );

            Cache::forever('lucky_gifts_action', $request->value);
            \Artisan::call('cache:clear');

            return Common::apiResponse(true, 'created successfully');
        } catch (Exception $exception) {
            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }

    public function initCoinRates()
    {
        $appBaseRate = \App\Services\CoinRateService::getAppBaseRate();
        $superAdminCoins = Setting::where('key', 'super_admin_coins')->value('value') ?? $appBaseRate;
        $areaManagerCoins = Setting::where('key', 'area_manager_coins')->value('value') ?? Setting::where('key', 'zones_coins')->value('value') ?? $appBaseRate;
        $zones_coins =Setting::where('key', 'zones_coins')->value('value') ;
        // Super Admins
        $superAdmins = \App\Models\AdminUser::where('type', 'superadmin')->get();
        foreach ($superAdmins as $admin) {
            \App\Models\AdminCoinRate::updateOrCreate(
                ['admin_id' => $admin->id],
                ['rate' => $superAdminCoins]
            );
        }

        // Area Managers
        $areaManagers = \App\Models\AdminUser::where('type', 'area-manager')->get();
        foreach ($areaManagers as $manager) {
            \App\Models\AdminCoinRate::updateOrCreate(
                ['admin_id' => $manager->id],
                ['rate' => $areaManagerCoins]
            );
        }

        $userCoinsConfig = \App\Models\Setting::where('key', 'user_coins')->first();
        $userRate = (float) $userCoinsConfig->value ;

        \App\Models\Setting::updateOrCreate(
            ['key' => 'user_transfer_coin_rate'],
            ['value' => $userRate]
        );
        \App\Models\Setting::updateOrCreate(
            ['key' => 'user_transfer_rate_enabled'],
            ['value' => 1]
        );

        \App\Models\Setting::updateOrCreate(
            ['key' => 'app_coin_rate'],
            ['value' => $zones_coins]
        );
        \Illuminate\Support\Facades\Cache::forget('setting_user_transfer_coin_rate');
        \Illuminate\Support\Facades\Cache::forget('user_transfer_coin_rate');
        \Illuminate\Support\Facades\Cache::forget('setting_user_transfer_rate_enabled');
        \Illuminate\Support\Facades\Cache::forget('user_transfer_rate_enabled');

        return 'تمت تهيئة قيم المشرفين بنجاح (Init Admin Rates Done)';
    }

    public function initUserCoinRates()
    {
 
        return 'تمت تهيئة قيم المستخدمين بنجاح (Init User Rates Done)';
    }

    public function backfillCharges()
    {
        set_time_limit(300);
        $exitCode = \Artisan::call('charges:backfill', ['--chunk' => 500]);

        $output = \Artisan::output();

        if ($exitCode === 0) {
           return  'تمت العملية تم تعبئة بيانات الشحنات القديمة بنجاح!';
        } else {
            return 'خطأ حدث خطأ أثناء تعبئة البيانات. راجع السجلات.';
        }

    }
}
