<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Gift;
use App\Helpers\Common;
use Illuminate\Http\Request;
use App\Tik\Services\GiftService;
use App\Http\Controllers\Controller;
use App\Http\Resources\GiftResource;

class GiftController extends Controller
{
    public function __construct(private GiftService $giftService) {}
    public function index(Request $request)
    {
        $gifts =$this->giftService->index($request);
        return Common::apiResponse(true, '', GiftResource::collection($gifts), 200);
    }
}