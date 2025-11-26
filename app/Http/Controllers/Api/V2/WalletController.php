<?php

namespace App\Http\Controllers\Api\V2;

use App\Helpers\Common;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserCoinLogResource;;

use App\Tik\Services\WalletStatisticService;


class WalletController extends Controller
{
    public function __construct(private WalletStatisticService $walletStatisticService) {}

    public function diamondsStatistic(Request $request)
    {
        $user = $request->user();
        $data = $this->walletStatisticService->diamondsStatistic($user->id, $request->type, $request->startDate, $request->endDate, $request->perPage, $request->page);
        return Common::apiResponse(true, '', $data, 200, null, 'list');
    }

    public function history(Request $request)
    {
        $user = $request->user();
        $data = $this->walletStatisticService->history($user->id, $request->type, $request->start_date, $request->end_date, $request->page, $request->per_page);
        return Common::apiResponse(true, '', UserCoinLogResource::collection($data), 200);
    }
}
