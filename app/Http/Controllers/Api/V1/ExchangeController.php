<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\Common;
use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Tik\Services\ExchangeService;


class ExchangeController extends Controller
{
    public function __construct(private ExchangeService $exchangeService) {}


    public function exchangeList(Request $request)
    {
        $user = $request->user();
        if ($request->type == null) {
            return Common::apiResponse(0, 'missing param', null, 422);
        }
        $list = $this->exchangeService->index($request->type);
        /** @var User $user */
        return Common::apiResponse(1, $user->monthly_diamond_received, $list, 200);
    }

    public function exchangeSave(Request $request)
    {
        $user = $request->user();

        try {
            $this->exchangeService->create($user, $request->item_id);
            return Common::apiResponse(1, $user->total_diamond_received, ['diamond' => $user->total_diamond_received, 'coins' => $user->di], 200);
        } catch (\Exception $exception) {

            return Common::apiResponse(0, $exception->getMessage(), null, 400);
        }
    }

    public function exchangeLogs(Request $request)
    {
        $user = $request->user();
        $type = $request->type ?: 0;
        $data = $this->exchangeService->getExchangeLog($user->id, $type);
        return Common::apiResponse(1, 'ok', $data, 200);
    }
}
