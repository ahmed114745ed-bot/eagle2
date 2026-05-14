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
use Illuminate\Support\Facades\Config;

class SettingController extends MainController
{
    protected $title = 'Settings';
    public $permission_name = 'settings';

    public function index(Content $content)
    {
        // Batch load all settings + configs in 2 queries instead of 20+ individual calls
        $settings = Setting::pluck('value', 'key')->toArray();
        $configs = \App\Models\Config::pluck('value', 'name')->toArray();

        // Helper to read from pre-loaded configs
        $cfg = fn(string $key) => $configs[$key] ?? null;

        $timezones = Timezone::all();
        $agora_app_id = $cfg('app_id');
        $agora_app_certificate = $cfg('agora_app_certificate');
        $zego_server_secret = $cfg('zego_server_secret');
        $zego_app_id = $cfg('zego_app_id');
        $tencent_server_secret = $cfg('tencent_server_secret');
        $tencent_app_id = $cfg('tencent_app_id');
        $app_sign = $cfg('app_sign');
        $library = $cfg('library');
        $soundLibrary = $cfg('sound_library');
        $videoLibrary = $cfg('video_library');
        $liveLibrary = $cfg('live_library');
        $gamesLibrary = $cfg('games_library');
        $brand_images = BrandImage::all();
        $paymentCoins = PaymentCoin::with('settings')->uniqueTypes()->orderByDesc('status')->get();
        $pusher_app_id = $cfg('pusher_app_id');
        $pusher_app_key = $cfg('pusher_app_key');
        $pusher_app_secret = $cfg('pusher_app_secret');
        $pusher_app_cluster = $cfg('pusher_app_cluster');
        $firebase_api_key = $cfg('firebase_api_key');
        $firebase_auth_domain = $cfg('firebase_auth_domain');
        $firebase_database_url = $cfg('firebase_database_url');
        $supabase_url = $cfg('supabase_url');
        $supabase_key = $cfg('supabase_key');
        $zego_filter_enabled = $cfg('zego_filter_enabled');
        $is_auto_preview = $cfg('is_auto_preview');
        $countries = Country::select(['id', 'name', 'e_name'])->get();
        $languages = Language::select(['id', 'name', 'code'])->get();
        $chargeTabType = request()->get('type', 'Experience');
        $zego_token = $cfg('zego_token');
        $zego_key = $cfg('zego_key');
        $utd_stream_app_id = $cfg('utd_stream_app_id');
        $utd_stream_server_secret = $cfg('utd_stream_server_secret');
        $utd_stream_callback_secret = $cfg('utd_stream_callback_secret');
        $gameSettings = GameProviderSetting::all()->keyBy('provider_code');
        $bytesunSettings = $gameSettings->get('bytesun');
        $quantumNexusSettings = $gameSettings->get('quantum_nexus');
        $zeroGamesSettings = $gameSettings->get('zero_games');
        $utdGamesSettings = $gameSettings->get('utd_games');
        $isThemeEnabled = $settings['isThemeEnabled'] ?? 0;

        $supabase_service_role_key = $cfg('supabase_service_role_key');
        return parent::index($content
            ->header(__('Settings'))
            ->description('   ')
            ->body(view('admin.settings_new', compact([
                'isThemeEnabled',
                'zeroGamesSettings',
                'bytesunSettings',
                'quantumNexusSettings',
                'utdGamesSettings',
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
                'soundLibrary',
                'videoLibrary',
                'liveLibrary',
                'gamesLibrary',
                'zego_filter_enabled',
                'is_auto_preview',
                'countries',
                'utd_stream_app_id',
                'utd_stream_server_secret',
                'utd_stream_callback_secret'
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
                Cache::forget($key);
                Cache::put($key, $value, now()->addYear());
            }

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

            Cache::forget('room_cup');
            Cache::put('room_cup', $request->value, now()->addYear());

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

            Cache::forget('room_boom');
            Cache::put('room_boom', $request->value, now()->addYear());

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

            Cache::forget('remaining_diamonds_action');
            Cache::put('remaining_diamonds_action', $request->value, now()->addYear());

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

            Cache::forget('host_level_action');
            Cache::put('host_level_action', $request->value, now()->addYear());

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

            Cache::forget('pk_live_action');
            Cache::put('pk_live_action', $request->value, now()->addYear());

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

            Cache::forget('lucky_gifts_action');
            Cache::put('lucky_gifts_action', $request->value, now()->addYear());

            return Common::apiResponse(true, 'created successfully');
        } catch (Exception $exception) {
            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }


    public function updateIsThemeEnabled(Request $request): JsonResponse
    {
        try {
            Setting::updateOrCreate(
                ['key' => 'isThemeEnabled'],
                ['value' => $request->value]
            );

            Cache::forget('isThemeEnabled');
            Cache::put('isThemeEnabled', $request->value, now()->addYear());

            return Common::apiResponse(true, 'created successfully');
        } catch (Exception $exception) {
            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }


    public function updateRoomMode(Request $request): JsonResponse
    {
        try {
            Setting::updateOrCreate(
                ['key' => $request->field],
                ['value' => $request->value]
            );

            Cache::forget($request->field);
            Cache::put($request->field, $request->value, now()->addYear());

            return Common::apiResponse(true, 'created successfully');
        } catch (Exception $exception) {
            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }

    public function updateCharismaFormat(Request $request): JsonResponse
    {
        try {
            Setting::updateOrCreate(
                ['key' => 'charisma_format'],
                ['value' => $request->value]
            );

            Cache::forget('charisma_format');
            Cache::put('charisma_format', $request->value, now()->addYear());

            Config::set('charisma.format', (bool) $request->value);

            return Common::apiResponse(true, 'created successfully');
        } catch (Exception $exception) {
            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }


    public function updateCharismaBadge(Request $request): JsonResponse
    {
        try {
            Setting::updateOrCreate(
                ['key' => 'charisma_badge'],
                ['value' => $request->value]
            );

            Cache::forget('charisma_badge');
            Cache::put('charisma_badge', $request->value, now()->addYear());

            Config::set('charisma.badge', (bool) $request->value);

            return Common::apiResponse(true, 'created successfully');
        } catch (Exception $exception) {
            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }
}
