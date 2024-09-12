<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\Common;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Tik\Services\CountryService;
use App\Http\Resources\CountryResource;
use Doctrine\DBAL\Schema\Index;
use App\Services\ConfigService;
use App\Models\Config;
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
            "desc" => $request->desc,
        ]);
        return Common::apiResponse(1, 'updated successfully');
    }

}
