<?php

namespace App\Http\Controllers\Api\V2;

use App\Models\Ware;
use App\Helpers\Common;
use Illuminate\Http\Request;
use App\Tik\Services\MallService;
use App\Http\Controllers\Controller;
use App\Http\Resources\WareResource;
use App\Http\Resources\BestWareSaleResource;
use Modules\Public\Http\Services\UserCounterServices;


class MallController extends Controller
{
    // wares

    public function __construct(private MallService $mallService) {}

    public function index(Request $request)
    {
        $user = $request->user();
        (new UserCounterServices)->UpgradeDateForType($user, "mall");
        if (!$request->type) {
            return Common::apiResponse(false, 'type is required', null, 422);
        }

        $wares = $this->mallService->getWares($user->id, $request->type);

        return Common::apiResponse(true, '', WareResource::collection($wares), 200);
    }

    public function buyWare(Request $request)
    {
        $user    = $request->user();
        $wareId = $request->ware_id;
        $quantity     = $request->qty ?: 1;
        return $this->mallService->buyWares($user, $wareId, $quantity);
    }

    public function sendWare(Request $request)
    {
        $user = $request->user();
        $toUserId = $request->to_id;


        $wareId = $request->ware_id;
        $quantity = $request->qty ?: 1;
        if (!$wareId || !$toUserId) return Common::apiResponse(0, 'missing params', null, 422);

        return $this->mallService->sendWare($user, $wareId, $toUserId, $quantity);
    }


    public function wareImage()
    {
        $wares = Ware::whereNotNull('img2')->get();
        foreach ($wares as $wares) {
            $ImageType =     pathinfo($wares->img2, PATHINFO_EXTENSION);
            $wares->image_type = $ImageType == 'alpha' ? 'mp4' : $ImageType;
            $wares->save();
        }
        return $wares;
    }

    public function bestWareSale()
    {
        $pestSaleProduct = $this->mallService->bestSaleWare();
        return Common::apiResponse(true, '', BestWareSaleResource::collection($pestSaleProduct), 200);
    }

    public function giftOVip(Request $request)
    {
        $ware = $this->mallService->giftOVip($request->level, $request->type);

        return response()->json([
            'image_url' => getImagePath($ware->show_img), 
        ]);
    }
}
