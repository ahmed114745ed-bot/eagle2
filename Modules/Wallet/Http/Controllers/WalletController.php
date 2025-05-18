<?php

namespace Modules\Wallet\Http\Controllers;

use App\Helpers\Common;
use App\Http\Resources\TransactionResource;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Wallet\Http\Requests\MakeTransferRequest;
use Modules\Wallet\Services\WalletService;

class WalletController extends Controller
{
    public function __construct(private readonly WalletService $walletService)
    {
    }
    /**
     * @throws \Exception
     * @throws \Throwable
     */
    public function makeTransaction(MakeTransferRequest $request): JsonResponse
    {
        $result = $this->walletService->makeTransaction($request->validated());

        return Common::apiResponse(1, 'success', $result, 201);
    }

  

    public function getWalletTransactions(Request $request)
    {
        $result = $this->walletService->getWalletTransactions($request->all());
        return Common::apiResponse(1, 'success', TransactionResource::collection( $result), 201);

    }

 
    

}

