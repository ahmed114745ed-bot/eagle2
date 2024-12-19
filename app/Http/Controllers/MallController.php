<?php

namespace App\Http\Controllers;

use App\Models\OVip;
use App\Models\Pack;
use App\Models\User;
use App\Helpers\Common;
use App\Models\UserVip;
use App\Services\VipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Facades\CustomNotification;

class MallController extends Controller
{
    public function __construct(private VipService $vipService) {}
    public function buyVip(Request $request){
        if (!$request->vip_id ) return Common::apiResponse (0,__('api_responses.missing_params'),null,422);

       return $this->vipService->buyVipWithActive($request);
    }
}
