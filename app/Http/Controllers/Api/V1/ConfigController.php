<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Config;
use App\Helpers\Common;
use Illuminate\Http\Request;
use App\Services\ConfigService;
use Doctrine\DBAL\Schema\Index;
use App\Http\Controllers\Controller;
use App\Tik\Services\CountryService;
use App\Http\Resources\CountryResource;
use App\Http\Resources\Api\V1\ConfigResource;

class ConfigController extends Controller
{

    protected $configService;

    public function __construct(ConfigService $configService)
    {
        $this->configService = $configService;
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

    public function config( Request $request )
    {
        $configs = Config::all()->groupBy('category');
        $formattedConfigs = [];

        foreach ($configs as $category => $items) {
            $formattedConfigs[__($category)] = ConfigResource::collection($items);
        }

        return Common::apiResponse(1, '', $formattedConfigs);
    }

}
