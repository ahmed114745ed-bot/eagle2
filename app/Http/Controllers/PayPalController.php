<?php

namespace App\Http\Controllers;

use App\Models\CoinLog;
use App\Services\PayPalService;
use Http;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayPalController extends Controller
{
    public function checkout($logId)
    {
        $log = CoinLog::findOrFail($logId);

        return view('payments.paypal.checkout', [
            'logId'  => $log->id,
            'amount' => $log->paid_usd,
        ]);
    }

    public function create(Request $request): JsonResponse
    {
        $paypal = new PayPalService();
        [$orderId, $paymentLink] = $paypal->create($request->referenceId, $request->amount, null);

        $coinLog = CoinLog::whereId($request->referenceId)->first();
        $coinLog->update(['trx' => $orderId]);

        return response()->json([
            'id' => $orderId ?? null,
            'approval_url' => $paymentLink,
        ]);
    }

    public function capture($orderId): JsonResponse
    {
        $orderDetails = (new PayPalService())->capture($orderId);

        return response()->json([$orderDetails]);
    }

    public function transactions(): JsonResponse
    {
        $transactions = (new PayPalService())->transactions();

        return response()->json([$transactions]);
    }
}
