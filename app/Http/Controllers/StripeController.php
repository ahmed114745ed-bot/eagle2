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
    
        $stripe_test_secret_key = Setting::where('key', 'stripe_test_secret_key')->first();
        $stripe_webhook_secret  = Setting::where('key', 'stripe_webhook_secret')->first();
    
        $apiKey         = $stripe_test_secret_key?->value;
        $endpointSecret = $stripe_webhook_secret?->value;
    
        Stripe::setApiKey($apiKey);
    
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
    
        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $endpointSecret);
            $orderId = null;
            $trxId   = null;
    
            switch ($event->type) {
                case 'checkout.session.completed':
                    $session = $event->data->object;
    
                    if (!empty($session->metadata->order_id)) {
                        $orderId = $session->metadata->order_id;
                        $trxId   = $session->payment_intent;
                        Log::info("checkout.session.completed: order_id={$orderId}, trx={$trxId}");
                    } else {
                        $trxId   = $session->id;
                    }
                    break;
    
                case 'payment_intent.succeeded':
                    $paymentIntent = $event->data->object;
                    $trxId   = $paymentIntent->payment_intent;
                    break;
    
                case 'charge.succeeded':
                case 'charge.updated':
                    $charge = $event->data->object;
                    $trxId   = $charge->payment_intent ?? $charge->id;
                    break;
    
                case 'checkout.session.expired':
                    $session = $event->data->object;
                    $trxId   = $session->id;
                    if ($trxId) {
                        Log::warning("Stripe session expired for trx {$trxId}.");
                    }
                    break;
    
                case 'payment_intent.failed':
                    $paymentIntent = $event->data->object;
                    $trxId   = $paymentIntent->id;
                    Log::error('Payment failed', ['paymentIntent' => $paymentIntent]);
                    break;
    
                default:
                    Log::info("Unhandled event type: {$event->type}");
                    break;
            }
    
            // لو orderId موجود من metadata نحدثه مباشرة
            if (!empty($orderId)) {
                $coinLog = \App\Models\CoinLog::find($orderId);
                if ($coinLog) {
                    $coinLog->trx = $trxId; 
                    $coinLog->save();
    
                    Log::info("Updated CoinLog {$orderId} with trx {$trxId}");
                    $this->webhookPayment($orderId);
                } else {
                    Log::warning("No CoinLog found with order_id {$orderId}");
                }
            }
            elseif (!empty($trxId)) {
                $coinLog = \App\Models\CoinLog::where('trx', $trxId)->first();
                if ($coinLog) {
                    $orderId = $coinLog->id;
                    Log::info("Found CoinLog for trx {$trxId}, order {$orderId}");
                    $this->webhookPayment($orderId);
                } else {
                    Log::warning("No CoinLog found for trx {$trxId}");
                }
            } else {
                Log::warning("No trxId or orderId extracted for event {$event->type}");
            }
    
            return response('Webhook Handled', 200);
    
        } catch (SignatureVerificationException $e) {
            Log::error("Stripe Signature verification failed", [
                'error' => $e->getMessage(),
                'payload' => $payload ?? null,
                'sigHeader' => $sigHeader ?? null,
            ]);
            return response('Invalid Signature', 400);
    
        } catch (\Exception $e) {
            Log::error("Stripe Webhook error", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $payload ?? null,
            ]);
            return response('Webhook Error: ' . $e->getMessage(), 500);
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
