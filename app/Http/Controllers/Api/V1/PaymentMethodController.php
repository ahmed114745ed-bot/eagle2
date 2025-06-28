<?php

namespace App\Http\Controllers\Api\V1;

use App\Helpers\Common;
use App\Http\Controllers\Controller;
use App\Models\CoinLog;
use App\Models\GameChargeHistory;
use App\Models\GameWallet;
use App\Models\PaymentMethodHistory;
use App\Traits\User\PaymentTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Log;
use Throwable;

class PaymentMethodController extends Controller
{
    use PaymentTrait;

    public function callback(Request $request)
    {
        $callbackData = $request->all();
        $fawryRefNumber = $callbackData['fawryRefNumber'];
        $merchantRefNumber = $callbackData['merchantRefNumber'];
        $orderStatus = $callbackData['orderStatus'];

        $order = PaymentMethodHistory::where('utd_code', $merchantRefNumber)->first();
        if ($orderStatus === 'PAID') {
            $order->status = 'paid';
            if ($order->type === 'game_type') {
                $this->updateDiForUser($order->amount);
                GameChargeHistory::create([
                    'value' => $order->amount,
                    'admin_id' => 0,
                ]);
            }
        } elseif ($orderStatus === 'CANCELLED') {
            $order->status = 'cancelled';
        } else {
            $order->status = 'Error';
        }
        $order->ref_code = $fawryRefNumber;

        $order->save();

        return true;
    }

    public function updateDiForUser($amount)
    {
        $balance = $amount * config('app.one_coins') * 2;
        $gameWallet = GameWallet::whereMonth('created_at', date('m'))->whereYear('created_at', date('Y'))->first();
        if ($gameWallet) {
            $gameWallet->balance += $balance;
            $gameWallet->save();
        } else {
            GameWallet::create([
                'balance' => $balance,
            ]);
        }
    }

    public function store(Request $request)
    {
        $trx = PaymentMethodHistory::create([
            'amount' => $request->amount,
            'type' => 'game_type',
            'utd_code' => $request->utd_code,
        ]);

        $trxId = $trx->id;

        return Common::apiResponse(1, 'created successfully', $trxId, 200);
    }

    public function utdCallback(Request $request)
    {
        $callbackData = $request->all();
        $fawryRefNumber = $callbackData['fawryRefNumber'];
        $merchantRefNumber = $callbackData['merchantRefNumber'];
        $orderStatus = $callbackData['orderStatus'];

        $paymentMethod = PaymentMethodHistory::where('utd_code', $merchantRefNumber)->first();
        $order = CoinLog::where('trx', $merchantRefNumber)->first();
        if ($orderStatus === 'PAID') {
            $this->webhookPayment($order->id);
            $order->pid = $fawryRefNumber;
            $paymentMethod->status = 'paid';
            $order->save();

            return response()->json(['status' => 'success', 'message' => 'Payment successful.']);
        }
        if ($orderStatus === 'UNPAID') {
            return response()->json(['status' => 'pending', 'message' => 'Payment is still unpaid.'], 202);
        }
        if ($orderStatus === 'CANCELLED') {
            $paymentMethod->status = 'cancelled';

            return response()->json(['status' => 'cancelled', 'message' => 'Payment was cancelled.']);
        }

        $paymentMethod->status = 'Error';
        $paymentMethod->save();
        $order->save();

        return response()->json(['status' => 'error', 'message' => 'Payment status is invalid or failed.'], 400);
    }

    public function success(Request $request): JsonResponse
    {
        try {
            $query = Arr::only($request->query(), [
                'statusCode',
                'statusDescription',
                'merchantRefNumber',
            ]);

            Log::info('this response '.json_encode($request->all()));

            // Check if merchantRefNumber exists
            if (empty($query['merchantRefNumber'])) {
                return response()->json([
                    'status' => false,
                    'trx' => null,
                    'message' => 'Missing merchant reference number.',
                ]);
            }

            $purchaseProduct = CoinLog::where('trx', $query['merchantRefNumber'])->first();
            Log::info('$purchaseProduct->status '.json_encode($purchaseProduct->status));

            if (! $purchaseProduct) {
                return response()->json([
                    'status' => false,
                    'trx' => $query['merchantRefNumber'],
                    'message' => 'Transaction not found.',
                ]);
            }

            Log::info('this response '.json_encode([
                'status' => $purchaseProduct->status === 1,
                'trx' => $purchaseProduct->trx,
                'message' => $query['statusDescription'] ?? '',
            ]));

            return response()->json([
                'status' => $purchaseProduct->status === 1,
                'trx' => $purchaseProduct->trx,
                'message' => $query['statusDescription'] ?? '',
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'status' => false,
                'trx' => null,
                'message' => 'Something went wrong: '.$e->getMessage(),
            ]);
        }
    }
}
