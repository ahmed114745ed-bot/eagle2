<?php

namespace App\Http\Controllers\Api\V1;

use Exception;
use App\Helpers\Common;

use App\Services\VipService;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Resources\Api\V1\VipResource;
use App\Http\Resources\Api\V1\OVipResource;
use Modules\Public\Http\Services\UserCounterServices;
use Modules\Public\Http\Services\UpgradeLevelServices;




class VipController extends Controller
{
    public function __construct(private VipService $vipService) {}

    public function index()
    {
        $type = request()->type ?? 2;
        $vips = $this->vipService->vipIndex($type);
        return Common::apiResponse(true, 'success', VipResource::collection($vips));
    }

    public function vipList()
    {
        $data = $this->vipService->vipList();

        \request()->vipPrivileges = $data['all_privileges'];

        return Common::apiResponse(1, '', OVipResource::collection($data['o_vips']), 200);
    }

    public function buyVip(Request $request)
    {
        if (!$request->vip_id) return Common::apiResponse(0, 'missing param', null, 422);

        try {
            [$user, $countWares, $buyer, $exp] = $this->vipService->buyVip($request);

            (new UpgradeLevelServices())->buyAristocracy($buyer, $exp);

            (new UserCounterServices)->eventUser($user, 'mybag', $countWares);
            return Common::apiResponse(1, 'done', null, 201);
        } catch (\Exception $exception) {
            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }

    public function vip_use(Request $request)
    {
        if (!$request->vip_id) {
            return Common::apiResponse(false, __("api_responses.missing_params"), null, 422);
        }
        try {
            $data = $this->vipService->userVip($request);
        } catch (\Exception $exception) {
            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
        return Common::apiResponse(1, 'success', $data);
    }

    public function vip_send(Request $request)
    {
        if (!$request->user_id || !$request->vip_id) {
            return Common::apiResponse(false, __("api_responses.missing_params"), null, 422);
        }

        try {
            $data = $this->vipService->sendVip($request);
        } catch (\Exception $exception) {
            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }

        return Common::apiResponse(1, 'success', $data);
    }


    public function createWareVip(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name'         => 'required|string|max:255',
            'name_en'         => 'required|string|max:255',
            'title' => 'nullable|string|max:255',
            'title_en' => 'nullable|string|max:255',
            'show_img' => 'required||mimes:jpeg,png,jpg,gif,svg|max:2048',
            'img2' => 'required||mimes:jpeg,png,jpg,gif,svg|max:2048',
            'img2_type' => 'nullable',
            'vipPrivilege_id' => 'required||integer|exists:vip_privileges,id',
            'ovip_id' => 'required|integer|exists:o_vips,id',

        ]);
        if ($validator->fails()) {
            return Common::apiResponse(0, __('api_responses.validation_error'), $validator->errors());
        }

        try {
            $this->vipService->createWareVip($request);
            return Common::apiResponse(1, 'created successfully');
        } catch (Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }
}
