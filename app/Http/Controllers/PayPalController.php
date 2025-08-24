<?php

namespace App\Http\Controllers;

use App\Models\CoinLog;
use App\Services\PayPalService;
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
        $orderId = $paypal->create($request->referenceId, $request->amount, null);

        return response()->json(["id" => $orderId]);
    }

    public function capture($orderId): JsonResponse
    {
        return response()->json(["status" => "COMPLETED", "orderId" => $orderId]);
    }
}
