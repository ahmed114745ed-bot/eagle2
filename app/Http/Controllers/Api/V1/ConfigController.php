<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\ConfigCategory;
use App\Http\Requests\Api\ConfigValuesRequest;
use App\Models\Config;
use App\Helpers\Common;
use App\Models\Pack;
use Illuminate\Http\Request;
use App\Services\ConfigService;
use Doctrine\DBAL\Schema\Index;
use App\Http\Controllers\Controller;
use App\Tik\Services\CountryService;
use App\Http\Resources\CountryResource;
use App\Http\Resources\Api\V1\ConfigResource;
use Illuminate\Support\Facades\Redirect;

class ConfigController extends Controller
{

    protected $configService;

    public function __construct(ConfigService $configService)
    {
        $this->configService = $configService;
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
        $config = Config::find($request->id);
        $config->value = $request->value;
        $config->save();
        return Redirect::back();
    }

    public function updateConfigAgoraZego(Request $request)
    {
        $keys = array_keys($request->all());

        foreach ($keys as $key) {
            $config = Config::where('name', $key)->first();

            if ($config) {
                $config->value = $request->input($key);
            } else {
                $config = new Config();
                $config->name = $key;  // Set name first
                $config->value = $request->input($key);
            }
    
            $config->save();
        }
        return Redirect::back();
    }
}
