<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\Common;
use App\Http\Controllers\Controller;
use App\Services\VipService;
use Illuminate\Http\Request;

class MallController extends Controller
{

    protected $vipService;

    public function __construct(VipService $vipService)
    {
        $this->vipService = $vipService;
    }

    public function vipList()
    {
        $list = $this->vipService->getVipList();
        return Common::apiResponse(1, '', $list, 200);
    }

    public function buyVip(Request $request)
    {
        return $this->vipService->buyVip($request);
    }

    public function vip_use(Request $request)
    {
        return $this->vipService->useVip($request);
    }

    public function vip_send(Request $request)
    {
        return $this->vipService->sendVip($request);
    }
}
