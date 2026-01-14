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
            $configs = Common::getConfFromKey($keys);

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
        if (!Admin::user()->can('*')) {
            Permission::check('edit-' . $this->permission_config_name);
        }

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
        Cache::forget('pusher_config');

        $excludeKeys = ['_token', 'redirect_to', 'current_tab', 'inner_tab_type'];
        $keys = array_diff(array_keys($request->all()), $excludeKeys);

        foreach ($keys as $key) {
            $config = Config::where('name', $key)->first();
            $value = $request->input($key);

            if ($config) {
                $config->value = $value;
            } else {
                $config = new Config();
                $config->name = $key;
                $config->value = $request->input($key);
            }

            $config->save();
            Cache::forget($key);
            Cache::forever($key, $request->input($key));
        }

        // Clear pusher config cache for Octane
        Cache::forget('pusher_config');
        Cache::forget('all_configs');
        Cache::flush();
        Artisan::call('config:cache');

        $redirectUrl = url(config('admin.route.prefix') . '/settings');

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
}
