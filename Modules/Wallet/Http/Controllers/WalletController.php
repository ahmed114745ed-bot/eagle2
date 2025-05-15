<?php

namespace Modules\Wallet\Http\Controllers;

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
        return $this->walletService->makeTransaction($request->validated());
    }
}
