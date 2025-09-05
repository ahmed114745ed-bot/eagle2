<?php

namespace App\Http\Controllers;

use App\Helpers\UserCommon;
use App\Models\Coin;
use App\Models\CoinLog;
use App\Models\Setting;
use App\Models\User;
use App\Services\StripeService;
use App\Traits\User\PaymentTrait;
use Database\Seeders\config;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeController extends Controller
{
    use PaymentTrait;

    private const SUCCESS_STATUSES = ['succeeded', 'paid'];

    public function __construct(public StripeService $stripeService) {}
    public function pay(Request $request)
    {
        $request->validate([
            'quantity'     => 'required|integer|min:1',
            'coin_id'      => 'required|numeric'
        ]);
    
        try {
            $stripe_test_secret_key = Setting::where('key', 'stripe_test_secret_key')->first();
            $apiKey = $stripe_test_secret_key?->value;
    
            $coin = Coin::findOrFail($request->coin_id);
            $trx  = rand(111111111111111111, 999999999999999999);
    
            // إنشاء الطلب في النظام
            $order = CoinLog::query()->create([
                'coin_id'        => $coin->id,
                'paid_usd'       => $coin->usd,
                'user_id'        => auth()->id(),
                'obtained_coins' => $coin->coin,
                'method'         => 'card',
                'trx'            => $trx,
                'status'         => 0
            ]);
    
            $paymentRequest = new \Illuminate\Http\Request([
                'product_name' => $coin->coin,
                'amount'       => $coin->usd,
                'quantity'     => $request->quantity,
                'order_id'     => $order->id,
            ]);
    
            $link = $this->stripeService->pay($apiKey, $paymentRequest);
    
            return response()->json([
                'message' => 'Link generated successfully',
                'link'    => $link
            ]);
    
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error generating payment link: ' . $e->getMessage(),
                'error'   => $e->getMessage()
            ], 500);
        }
    }


    public function handleWebhook(Request $request)
    {
        Log::info('Stripe Webhook received', [
            'payload' => $request->getContent(),
            'all'     => $request->all(),
        ]);

        $apiKey         = Setting::where('key', 'stripe_test_secret_key')->value('value');
        $endpointSecret = Setting::where('key', 'stripe_webhook_secret')->value('value');

        Stripe::setApiKey($apiKey);

        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);

            [$orderId, $trxId, $status] = $this->extractStripeEventData($event);

            if (!$trxId && !$orderId) {
                Log::warning("Ignored Stripe event: Missing IDs", [
                    'event' => $event->type,
                ]);
                return response('Ignored: no IDs', 200);
            }

            if (!in_array($status, self::SUCCESS_STATUSES)) {
                $this->markCoinLogAsFailed($orderId, $trxId, $status);
                return response('Ignored: not successful', 200);
            }
            

            $this->markCoinLogAsPaid($orderId, $trxId);

            return response('Webhook Handled', 200);

        } catch (SignatureVerificationException $e) {
            Log::error("Stripe Signature verification failed", [
                'error'     => $e->getMessage(),
                'payload'   => $payload,
                'sigHeader' => $sigHeader,
            ]);
            return response('Invalid Signature', 400);

        } catch (\Exception $e) {
            Log::error("Stripe Webhook error", [
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
                'payload' => $payload,
            ]);
            return response('Webhook Error: ' . $e->getMessage(), 500);
        }
    }

    private function extractStripeEventData(object $event): array
    {
        $orderId = null;
        $trxId   = null;
        $status  = null;

        switch ($event->type) {
            case 'checkout.session.completed':
                $session = $event->data->object;
                $orderId = $session->metadata->order_id ?? null;
                $trxId   = $session->payment_intent;
                $status  = $session->payment_status ?? null;
                break;

            case 'payment_intent.succeeded':
                $pi     = $event->data->object;
                $trxId  = $pi->id;
                $status = $pi->status;
                break;

            case 'charge.succeeded':
                $charge = $event->data->object;
                $trxId  = $charge->payment_intent ?? $charge->id;
                $status = $charge->status;
                break;

            case 'payment_intent.payment_failed':
            case 'payment_intent.canceled':
                $pi     = $event->data->object;
                $trxId  = $pi->id;
                $status = $pi->status;
                break;

            default:
                Log::info("Unhandled Stripe event type", ['event' => $event->type]);
        }

        return [$orderId, $trxId, $status];
    }

    private function markCoinLogAsPaid(?string $orderId, ?string $trxId)
    {
        $coinLog =  CoinLog::find($orderId);

        if (!$coinLog) {
            Log::warning("Stripe Webhook: No CoinLog found", [
                'orderId' => $orderId,
                'trxId'   => $trxId,
            ]);
            return;
        }

        if ($coinLog->status == 1) {
            return response()->json([
                'status' => 'failed',
                'reason' => 'Transaction already processed',
            ]);
        }

        $coinLog->update([
            'trx'     => $trxId,
            'status' => true,
        ]);
    }

    private function markCoinLogAsFailed(?string $orderId, ?string $trxId, string $status): void
    {
        $coinLog =  CoinLog::find($orderId);

        if ($coinLog) {
            $coinLog->update([
                'trx'    => $trxId,
                'status' => $status, 
            ]);
            Log::info("CoinLog {$coinLog->id} marked as {$status}");
        }
    }


    
    
    
    
    public function success(Request $request)
    {

        try {
            $orderId = $request->get('orderId');

            $coin = CoinLog::find($orderId);
            return response()->json([
                'status'  => true,
                'trx'     => $coin->trx,
                'message' => 'Transaction completed successfully.',
            ]);
        } catch (\Exception $e) {
            return response()->json(['status' => 'failed', 'message' => 'Payment failed.',], 500);
        }
    }

    public function cancel()
    {
        return response()->json(['status' => 'cancelled', 'message' => 'Payment cancelled.',]);
    }

}
