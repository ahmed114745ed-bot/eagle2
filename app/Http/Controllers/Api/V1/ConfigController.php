<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ConfigCategory;
use App\Http\Requests\Api\ConfigValuesRequest;
use App\Models\Config;
use App\Helpers\Common;
use App\Models\Pack;
use Artisan;
use Illuminate\Http\Request;
use App\Services\ConfigService;
use Doctrine\DBAL\Schema\Index;
use App\Http\Controllers\Controller;
use App\Tik\Services\CountryService;
use App\Http\Resources\CountryResource;
use App\Http\Resources\Api\V1\ConfigResource;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config as LaravelConfig;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Auth\Permission;

class ConfigController extends Controller
{

    protected $configService;
    public $permission_name = 'agency-settings';
    public $permission_config_name = 'update_setting_button';

    public function __construct(ConfigService $configService)
    {
        $this->configService = $configService;
    }

    public function uploadBadges(Request $request)
    {
        if (!Admin::user()->can('*')) {
            Permission::check('edit-' . $this->permission_name);
        }

        foreach ($request->allFiles() as $input => $file) {

            if (is_array($file)) {
                foreach ($file as $singleFile) {
                    $value = Common::upload('images', $singleFile);
                    Config::updateOrCreate(['name' => $input], [
                        'value' => $value
                    ]);
                }
            } else {
                $value = Common::upload('images', $file);
                Config::updateOrCreate(['name' => $input], [
                    'value' => $value
                ]);
            }
        }
        settings()->set('badges_agency_update_at', time());
        return back();
    }

    public function getConfigValues(ConfigValuesRequest $request)
    {
        $configs = [];
        if (isset($request['keys'])) {
            $keys = $request['keys'];
            $keys = array_diff($keys, ['zego_server_secret', 'zego_app_id', 'app_sign']);
            
            $sorted = $keys;
            sort($sorted);
            $cacheKey = 'config_keys_' . md5(json_encode($sorted));
            $configs = Cache::remember($cacheKey, 300, function () use ($keys) {
                return Common::getConfFromKey($keys);
            });

            $configs = $configs->flatMap(function ($value) {
                return [
                    $value->name => $value->value,
                ];
            });
        }
        if ($request['enable-special'] == 1) {
            $wapel                 = Pack::query()
                ->where('type', 12)
                ->where('expire', '>=', time())
                ->where('user_id', \Auth::id())
                ->where('use_num', '>', 0)
                ->select(['id', 'use_num'])
                ->first();
            $configs['wapel_num']  =  @(int)$wapel->use_num ?? 0;
            $user                  = $request->user();
            $configs['user_coins'] =  @(int)$user->di ?? 0;
            $configs['user_coins_string'] =  @$user->coins_string ?? '0';
        }
        return Common::apiResponse(true, 'config returned success', $configs, 200);
    }



    public function index()
    {
        $data = $this->configService->getAllConfigs();
        return Common::apiResponse(1, '', $data);
    }

    public function updateConfig(Request $request)
    {
        Config::find($request->config_id)->update([
            "value" => $request->value,
        ]);
        return Common::apiResponse(1, 'updated successfully');
    }

    public function config(Request $request)
    {
        $configs = Config::where('is_hidden', 0)->whereNotNull("category")->get()->groupBy('category');
        $formattedConfigs = [];

        foreach ($configs as $category => $items) {
            $formattedConfigs[__($category)]['sub_category'] = ConfigCategory::getLinkedStringsByValue($category);
            $formattedConfigs[__($category)]['data'] = ConfigResource::collection($items);
        }


        return Common::apiResponse(1, '', $formattedConfigs);
    }

    public function updateConfigChatGroup(Request $request)
    {
        if (!Admin::user()->can('*')) {
            Permission::check('edit-' . $this->permission_config_name);
        }

        $config = Config::find($request->id);
        $config->value = $request->value;
        $config->save();
        return Redirect::back();
    }


    public function UpdateConfigsGroupChat(Request $request)
    {
        // if (!Admin::user()->can('*')) {
        //     Permission::check('edit-' . $this->permission_config_name);
        // }

        foreach ($request->except('_token') as $key => $value) {
            if (!is_null($value)) {
                Config::updateOrCreate(['name' => $key], ['value' => $value]);

                Cache::forget($key);
                Cache::forever($key, $value);
            }
        }

        // Clear all cache including rememberForever keys
        Cache::forget('all_configs');
        Cache::flush();
        Artisan::call('config:cache');

        admin_success('Saved Successfully');
        return Redirect::back();
    }

    public function updateConfigAgoraZego(Request $request)
    {
        $excludeKeys = ['_token', 'redirect_to', 'current_tab', 'inner_tab_type'];
        $keys = array_diff(array_keys($request->all()), $excludeKeys);
        $updatedKeys = [];
        $hasPusherUpdate = false;

        foreach ($keys as $key) {
            $value = $request->input($key);

            // Log if live_library is being updated via this route (should NOT happen)
            if ($key === 'live_library') {
                Log::warning('Live Library Config - WRONG ROUTE! live_library being updated via updateConfigAgoraZego', [
                    'value' => $value,
                    'all_request' => $request->all(),
                    'url' => $request->fullUrl(),
                    'referer' => $request->header('referer'),
                    'is_ajax' => $request->ajax(),
                ]);
            }

            Config::updateOrCreate(
                ['name' => $key],
                ['value' => $value]
            );

            $updatedKeys[] = $key;
            Cache::forget($key);

            if (in_array($key, ['pusher_app_id', 'pusher_app_key', 'pusher_app_secret', 'pusher_app_cluster'])) {
                $hasPusherUpdate = true;
            }
        }

        Cache::forget('pusher_config');
        Cache::forget('all_configs');
        Cache::flush();

        // Re-cache all_configs immediately from DB so the redirected page has fresh data
        // This is critical for Octane: Common::getConfig() uses Cache::get('all_configs')
        // which does NOT auto-repopulate like rememberForever does.
        \App\Helpers\CacheHelper::cacheConfig();

        if (method_exists(Cache::store('octane'), 'flush')) {
            Cache::store('octane')->flush();
        }

        if ($hasPusherUpdate) {
            $pusherMapping = [
                'pusher_app_key' => 'broadcasting.connections.pusher.key',
                'pusher_app_secret' => 'broadcasting.connections.pusher.secret',
                'pusher_app_id' => 'broadcasting.connections.pusher.app_id',
                'pusher_app_cluster' => 'broadcasting.connections.pusher.options.cluster',
            ];

            foreach ($pusherMapping as $key => $configKey) {
                $value = $request->input($key);
                if ($value !== null) {
                    if ($key === 'pusher_app_cluster') {
                        LaravelConfig::set($configKey, $value ?? 'mt1');
                    } else {
                        LaravelConfig::set($configKey, $value);
                    }
                }
            }

            \App\Services\OctaneBroadcasterService::rebuildBroadcaster();
        }



        $redirectUrl = url('admin/settings');

        if ($request->has('current_tab')) {
            $redirectUrl .= '?tab=' . $request->current_tab;
            if ($request->has('inner_tab_type')) {
                $redirectUrl .= '&type=' . $request->inner_tab_type;
            }
        } elseif ($request->has('redirect_to')) {
            return Redirect::to($request->redirect_to);
        }

        return redirect($redirectUrl);
    }

  /*  public function updateConfigAgoraZego(Request $request)
    {
        $inputValue = $request->input('live_library');


        // Update directly via DB to avoid observer re-caching with stale data
        $exists = \DB::table('configs')->where('name', 'live_library')->exists();
        if ($exists) {
            \DB::table('configs')->where('name', 'live_library')->update([
                'value' => $inputValue,
                'updated_at' => now(),
            ]);
        } else {
            \DB::table('configs')->insert([
                'name' => 'live_library',
                'value' => $inputValue,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Confirm the update in DB
        $afterValue = \DB::table('configs')->where('name', 'live_library')->value('value');

        // Now clear ALL caches and re-cache with fresh data
        Cache::forget('live_library');
        Cache::forget('all_configs');

        try {
            Cache::flush();
        } catch (\Exception $e) {
            Log::error('Live Library - Cache flush failed', ['error' => $e->getMessage()]);
        }

        // Re-cache all_configs with fresh data from DB
        $freshConfigs = \DB::table('configs')->pluck('value', 'name')->toArray();
        Cache::forever('all_configs', $freshConfigs);


        try {
            if (method_exists(Cache::store('octane'), 'flush')) {
                Cache::store('octane')->flush();
            }
        } catch (\Exception $e) {
            Log::error('Live Library - Octane cache flush failed', ['error' => $e->getMessage()]);
        }

        if ($hasPusherUpdate) {
            $pusherMapping = [
                'pusher_app_key' => 'broadcasting.connections.pusher.key',
                'pusher_app_secret' => 'broadcasting.connections.pusher.secret',
                'pusher_app_id' => 'broadcasting.connections.pusher.app_id',
                'pusher_app_cluster' => 'broadcasting.connections.pusher.options.cluster',
            ];

            foreach ($pusherMapping as $key => $configKey) {
                $value = $request->input($key);
                if ($value !== null) {
                    if ($key === 'pusher_app_cluster') {
                        LaravelConfig::set($configKey, $value ?? 'mt1');
                    } else {
                        LaravelConfig::set($configKey, $value);
                    }
                }
            }

            \App\Services\OctaneBroadcasterService::rebuildBroadcaster();

        }

        $redirectUrl = url('admin/settings?tab=realTimeSetting');
        return redirect($redirectUrl);
    }*/
}
