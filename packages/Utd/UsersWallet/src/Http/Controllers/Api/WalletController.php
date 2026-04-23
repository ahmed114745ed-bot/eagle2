<?php
namespace Utd\UsersWallet\Http\Controllers\Api;

use App\Helpers\Common;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;
use Utd\UsersWallet\Http\Resources\ProfitTypeResource;
use Utd\UsersWallet\Http\Resources\TransactionLogsResource;
use Utd\UsersWallet\Http\Resources\UserCoinLogResource;
use Utd\UsersWallet\Services\WalletService;
use Utd\UsersWallet\Transformers\WalletTemplateResource;

class WalletController extends Controller
{
    public function __construct(private readonly WalletService $walletService )
    {
    }

    public function diamondsStatistic(Request $request)
    {
        $user = $request->user();
        $data = $this->walletService->diamondsStatistic($user->id, $request->type, $request->startDate, $request->endDate, $request->perPage, $request->page);
        return Common::apiResponse(true, '', $data, 200, null, 'list');
    }

       public function history(Request $request)
    {
        $user = $request->user();
        $data = $this->walletService->history($user->id, $request->type, $request->start_date, $request->end_date, $request->page, $request->per_page);

        return Common::apiResponse(true, '', UserCoinLogResource::collection($data), 200);
    }

    public function getWalletTransactions(Request $request)
    {
        $result = $this->walletService->getWalletTransactions($request->all());
        return Common::apiResponse(1, 'success', TransactionLogsResource::collection( $result), 201);
    }

    public function getTemplate(Request $request): JsonResponse|AnonymousResourceCollection
    {
        $type = $request->query('type');

        if (!$type) {
            return response()->json([
                'message' => 'The "type" parameter is required.',
            ], 422);
        }

        $result = $this->walletService->getTemplate($type);

        return Common::apiResponse(1, 'success', WalletTemplateResource::collection( $result));
    }


   public function getProfitsByType(Request $request)
    {
        $data = $this->walletService->getProfitsByType($request->all());

        return Common::apiResponse(
            1,
            'success',
            ProfitTypeResource::collection($data)
        );
    }

    public function getLatestOperations(Request $request)
    {
        $data = $this->walletService->getLatestTransactions($request->all());

        return Common::apiResponse(
            1,
            'success',
            TransactionLogsResource::collection($data)
        );
    }


}

