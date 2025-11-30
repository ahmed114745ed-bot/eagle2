<?php

namespace Modules\HostLevel\Http\Controllers\api;


use App\Helpers\Common;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\HostLevel\Http\Services\HostLevelService;
use Modules\HostLevel\Transformers\HostLevelResource;


class HostLevelController extends Controller
{

    public function __construct(private HostLevelService $hostLevelService) {}

    public function hostLevel()
    {

        $data = $this->hostLevelService->hostLevelIndex();
        $rule = $this->hostLevelService->roles();
        $field = "desc_" . app()->getLocale();
        $data = [
            'levels' => HostLevelResource::collection($data),
            'roles' => $rule != null ? $rule->$field : "",
        ];
        return Common::apiResponse(true, '', $data, 200, '', 'levels');
    }

    public function pick(Request $request)
    {
        $user = $request->user();
        $hostLevelId = $request->host_level_id;
        if (!$hostLevelId) {
            return Common::apiResponse(false, __('host level id is required'), null, 407);
        }
        try {

            $this->hostLevelService->pickHostLevel($user, $hostLevelId);
        } catch (\Exception $e) {
            return Common::apiResponse(false, $e->getMessage(), null, 407);
        }
        return Common::apiResponse(true, __('success process'));
    }
}
